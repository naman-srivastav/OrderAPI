<?php

namespace App\Http\Controllers;
use App\Models\Order as Order;
use Illuminate\Http\Request as Request;
use Illuminate\Http\Response; 
use App\Repositories\OrderRepository as OrderRepository;
use Exception;
use App\Http\Validations\OrderValidation;

class OrdersController extends Controller
{
    protected $orderRepository;
    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }
    
    /**
    * @OA\Info(
    *   title="Order's API", 
    *   description="Order's API",
    *   version="0.1"
    * )
    */
    /**
    * @OA\Get(path="/orders?page=:page&limit=:limit",
    *   tags={"Order's List"},
    *   summary="Return list of orders.",
    *   description="Having a list of orders along with there id, status and disatnce",
    *   operationId="orderListing",
    *   parameters={},
    *   @OA\Parameter(
    *       name="page",
    *       in="query",
    *       description="Valid page and must start with 1",
    *       required=true,
    *       @OA\Schema(
    *           type="integer",
    *           format="int64"
    *          )
    *       ),
    *   @OA\Parameter(
    *       name="limit",
    *       in="query",
    *       description="Valid limit of order",
    *       required=true,
    *       @OA\Schema(
    *           type="integer",
    *           format="int64"
    *          )
    *       ),
    *   @OA\Response(
    *     response=200,
    *     description="successful operation",
    *     @OA\Schema(ref="#/components/schemas/Order")
    *   ),
    *   @OA\Response(response=400, description="BAD_REQUEST"),
    *   @OA\Response(response=422, description="INVALID_PARAMETERS")
    *
    * )
    */
    
    public function index(Request $request){
        try {
            $validation_response = OrderValidation::validateIndexRequest($request);
            if(!empty(array_get($validation_response,'error',''))){
                 return response()->json(['error' => $validation_response['error']],array_get($validation_response,'code',Response::HTTP_INTERNAL_SERVER_ERROR));
            }
            $order = $this->orderRepository->index($request->get('page'), $request->get('limit'));
            return response()->json($order,Response::HTTP_OK);
        }  catch (Exception $ex){
            return response()->json(['error' => $ex->getMessage()],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
    * @OA\Post(path="/orders",
    *   tags={"Create Order"},
    *   summary="Create new order",
    *   description="Create a new order with a valid origin and destination latitude and longitude",
    *   operationId="createOrder",
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                 @OA\Property(
    *                     property="origin",
    *                     type="array",
    *                      @OA\Items(type="string"),
    *                 ),
    *                 @OA\Property(
    *                     property="destination",
    *                     type="array",
    *                      @OA\Items(type="string"),
    *                 ),
    *                 example={"origin": {"28.4595", "77.0266"}, "destination": {"28.7041", "77.1025"}}
    *             )
    *         )
    *     ),
    *
    *   @OA\Response(
    *     response=200,
    *     description="successful operation",
    *     @OA\Schema(ref="#/components/schemas/Order")
    *   ),
    *   @OA\Response(response=400, description="Bad Request"),
    *   @OA\Response(response=404, description="Not Found"),
    *   @OA\Response(response=422, description="Invalid Parameters")
    *
    * )
    */
    
    public function create(Request $request){
        try {
            $validation_response = OrderValidation::validateCreateRequest($request);
            if(!empty(array_get($validation_response,'error',''))){
                 return response()->json(['error' => $validation_response['error']],array_get($validation_response,'code',Response::HTTP_INTERNAL_SERVER_ERROR));
            }

            $data = Order::getDistance($request->get('origin'),$request->get('destination'));
            if(!empty(array_get($data,'error',''))){
                return response()->json(['error' => $data['error']] , Response::HTTP_NOT_FOUND);
            }
            $order = $this->orderRepository->create($data);
            return response()->json(["id" => $order->id,"distance" => $order->distance,"status" => $order->status],Response::HTTP_OK);
        }  catch (Exception $ex){
            return response()->json(['error' => $ex->getMessage()],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
    * @OA\PATCH(path="/orders/{id}",
    *   tags={"Update Order"},
    *   summary="Update order",
    *   description="Update order status",
    *   operationId="updateOrder",
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="Valid order id with UNASSIGNED status",
    *         required=true,
    *         @OA\Schema(
    *         type="integer",
    *          format="int64"
    *       ),
    *         style="form"
    *     ),
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                 @OA\Property(
    *                     property="status",
    *                     type="string",
    *                 ),
    *                 example={"status": "TAKEN"}
    *             )
    *         )
    *     ),
    *
    *   @OA\Response(
    *     response=200,
    *     description="Order has been assigned successfully",
    *     @OA\Schema(ref="#/components/schemas/Order")
    *   ),
    *   @OA\Response(response=400, description="BAD_REQUEST"),
    *   @OA\Response(response=404, description="ORDER_NOT_FOUND"),
    *   @OA\Response(response=405, description="METHOD_NOT_ALLOWED"),
    *   @OA\Response(response=409, description="ORDER_ALREADY_TAKEN"),
    *   @OA\Response(response=422, description="INVALID_PARAMETERS"),  
    *
    * )
    */
    
    public function update(Request $request,$id){
        try {
            $validation_response = OrderValidation::validateUpdateRequest($request,$id);
            if(!empty(array_get($validation_response,'error',''))){
                 return response()->json(['error' => $validation_response['error']],array_get($validation_response,'code',Response::HTTP_INTERNAL_SERVER_ERROR));
            }
            $order = $this->orderRepository->fetch($id);
            if(empty($order)){
                return response()->json(['error' => "ORDER_ID_NOT_FOUND"] ,Response::HTTP_NOT_FOUND);
            }
            else if($order->status == Order::TAKEN){
                return response()->json(['error' => "ORDER_ALREADY_TAKEN"],Response::HTTP_CONFLICT);
            }else{
                $response = $this->orderRepository->update($id);
                if(!$response){
                    return response()->json(['error' => "ORDER_ALREADY_TAKEN"],Response::HTTP_CONFLICT);
                }
                return response()->json(['status'  => "SUCCESS"],Response::HTTP_OK);
            }
        }  catch (Exception $ex){
            return response()->json(['error' => $ex->getMessage()],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
