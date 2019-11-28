<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Order as Order;
use App\Http\Controllers\OrdersController;
use App\Repositories\OrderRepository;
use Illuminate\Http\Request as Request;
use Illuminate\Http\JsonResponse;
use Faker\Factory as Faker;
use Illuminate\Http\Response;

class OrdersControllerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    
    public function setUp():void
    {
        parent::setUp();
        $this->orderRepositoryMock = \Mockery::mock(OrderRepository::class);
        $this->orderMock = \Mockery::mock(Order::class);
        $this->orderControllerMock = $this->app->instance(
            OrdersController::class,
            new OrdersController($this->orderRepositoryMock)
        );
    }
    
    public function tearDown(): void
    {
        \Mockery::close();
    }
    
    //create order test cases 
    
    public function testCreateOrder()
    {
        $param = [
                'origin' => ["28.4595", "77.0266"], // Gurgaon
                'destination' => ["28.7041", "77.1025"], //Delhi
            ];
        
        $order = $this->createOrder($param);
        $param = $this->getRequestData($param);
        $this->orderRepositoryMock
            ->shouldReceive('create')
            ->withAnyArgs()
            ->once()
            ->andReturn($order);
        $response = $this->orderControllerMock->create($param);
        $data = json_decode($response->getContent(), true);
        
        echo "\n >>>>> UNIT TESTING STARTS  HERE. <<<<< \n\n";
        echo "\n STARTING TESTING FOR CREATING ORDERS...";
        echo "\n -- Test for valid parameter -- \n";
        
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('distance', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);
    }
    
    public function testCreateForEmptyParameter()
    {
        $param = [];
        $param = $this->getRequestData($param);

        $this->app->instance('Order', $this->orderRepositoryMock);
        $response = $this->orderControllerMock->create($param);
        $data = json_decode($response->getContent(), true);
        
        echo "\n -- Test for empty parameter -- \n";
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertInternalType('array', $data);
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);

    }
    
    public function testCreateForInvalidLatitude()
    {
        $param = [
                'origin' => ["abc", "77.0266"], 
                'destination' => ["28.7041", "77.1025"], //Delhi
            ];
        $param = $this->getRequestData($param);

        $this->app->instance('Order', $this->orderRepositoryMock);
        $response = $this->orderControllerMock->create($param);
        $data = json_decode($response->getContent(), true);
        
        echo "\n -- Test for invalid latitude -- \n";
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertInternalType('array', $data);
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);

    }
    
    public function testCreateForInvalidLongitude()
    {
        $param = [
                'origin' => ["28.4595", "77.0266"],
                'destination' => ["28.7041", "cde"],
            ];
        $param = $this->getRequestData($param);

        $this->app->instance('Order', $this->orderRepositoryMock);
        $response = $this->orderControllerMock->create($param);
        $data = json_decode($response->getContent(), true);
        
        echo "\n -- Test for invalid longitude -- \n";
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertInternalType('array', $data);
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);

    }
    
    public function testCreateForEmptyLatLong()
    {
        $param = [
                'origin' => [], 
                'destination' => [], 
            ];
        $param = $this->getRequestData($param);

        $this->app->instance('Order', $this->orderRepositoryMock);
        $response = $this->orderControllerMock->create($param);
        $data = json_decode($response->getContent(), true);
        
        echo "\n -- Test for empty latitude and longitude -- \n";
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertInternalType('array', $data);
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);

    }
    
    public function testCreateForInvalidParams()
    {
        $param = [
                'origgin' => ["abc", "77.0266"],
                'destination' => ["28.7041", "cde"],
            ];
        $param = $this->getRequestData($param);

        $this->app->instance('Order', $this->orderRepositoryMock);
        $response = $this->orderControllerMock->create($param);
        $data = json_decode($response->getContent(), true);
        
        echo "\n -- Test for invalid parameter -- \n";
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertInternalType('array', $data);
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);
    }
    
    //order updation test cases
    
    public function testUpdateOrderWithValidaData()
    {
        $param = ['status' => 'TAKEN'];
        $param = $this->getRequestData($param);
        $id = rand(1,100);
        $order = new Order;
        $order->id = $id;
        $order->status = Order::UNASSIGNED;
        $this->orderRepositoryMock->shouldReceive('fetch')->with($id)->andReturn($order);
        $this->orderRepositoryMock->shouldReceive('update')->with($id)->andReturn(1);
        $response = $this->orderControllerMock->update($param, $id);
        $data = json_decode($response->getContent(), true);
        
        echo "\n STARTING TESTING FOR UPDATING ORDERS...\n";
        echo "\n -- Test for valid data -- \n";
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertSame('SUCCESS', $data['status']);
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);

    }
    
    public function testUpdateOrderWithInvalidParams()
    {
        $param = ['states' => 'TAKEN'];
        $param = $this->getRequestData($param);
        $id = rand(1,100);
        $response = $this->orderControllerMock->update($param, $id);
        $data = json_decode($response->getContent(), true);
        echo "\n -- Test for invalid parameter -- \n";
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertInternalType('array', $data);
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);
    }
    
    public function testUpdateOrderWithInvalidValue()
    {
        $param = ['status' => 'INVALID'];
        $param = $this->getRequestData($param);
        $id = rand(1,100);
        $response = $this->orderControllerMock->update($param, $id);
        $data = json_decode($response->getContent(), true);
        
        echo "\n -- Test for invalid value -- \n";
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertInternalType('array', $data);
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);
    }
    
        public function testUpdateOrderWithEmptyData()
    {
        $param = [];
        $param = $this->getRequestData($param);
        $id = rand(1,100);
        $response = $this->orderControllerMock->update($param, $id);
        $data = json_decode($response->getContent(), true);
        
        echo "\n -- Test for empty parameter -- \n";
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertInternalType('array', $data);
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);
    }

    public function testUpdateOrderWithInvalidOrderId()
    {
        $param = ['status' => 'TAKEN'];
        $param = $this->getRequestData($param);
        $id = "abc";
        $response = $this->orderControllerMock->update($param, $id);
        $data = json_decode($response->getContent(), true);
        
        echo "\n -- Test for invalid order id -- \n";
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertInternalType('array', $data);
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);
    }
    
    /**
     * TestCase for update order error with different scenarios.
     * 
     * @dataProvider updateOrderProvider
     */
    public function testUpdateOrderError($fetchOrderResponse, $responseCode, $updateOrderResponse,$test_message)
    {
        $param = ['status' => 'TAKEN'];
        $param = $this->getRequestData($param);
        $id = rand(1,100);
        $order = new Order;
        $order->id = $id;
        $order->status = Order::UNASSIGNED;
        $this->orderRepositoryMock->shouldReceive('fetch')->with($id)->andReturn($fetchOrderResponse);
        $this->orderRepositoryMock->shouldReceive('update')->with($id)->andReturn($updateOrderResponse);
        $response = $this->orderControllerMock->update($param, $id);
        $data = json_decode($response->getContent(), true);
        
        echo "\n -- Test for ". $test_message ." data -- \n";
        $this->assertEquals($responseCode, $response->getStatusCode());
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);
    }
    
    /**
     * Data provider for update order error testcase.
     * 
     */
    public function updateOrderProvider()
    {
        $order = new Order();
        $order->status = Order::TAKEN;
        
        $unassigned_order = new Order();
        $unassigned_order->status = Order::UNASSIGNED;
        return [
            [null, Response::HTTP_NOT_FOUND, 1,"order not found"],
             [$order, Response::HTTP_CONFLICT, 1,"order already taken"],
            [$unassigned_order, Response::HTTP_CONFLICT, 0,"concurrent requests to take a same order"],
        ];
    }

    //order list test cases
    
    public function testOrderListWithInvalidParameter()
    {
        $page = 1;
        $limit = 10;
        $query = "pagee=$page&llimit=$limit";
        $this->app->instance('Order', $this->orderRepositoryMock);
        $response = $this->call('GET', '/orders?' . $query);
        echo "\n STARTING TESTING FOR LISTING ORDERS...\n";
        echo "\n -- Test for getting list with invalid parameter -- \n";
        $data = (array) $response->getData();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertInternalType('array', $data);
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);

    }
    
    public function testOrderListWithInvalidValues()
    {
        $page = "abc";
        $limit = -1;
        $query = "page=$page&limit=$limit";
        $this->app->instance('Order', $this->orderRepositoryMock);
        $response = $this->call('GET', '/orders?' . $query);
        $data = (array) $response->getData();
        echo "\n -- Test for getting list with invalid values  -- \n";
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->response->status());
        $this->assertArrayHasKey('error', $data);
        $this->assertInternalType('array', $data);
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);
    }
    
    public function testOrderListWithEmptyParams()
    {
        $query = "";
        $this->app->instance('Order', $this->orderRepositoryMock);
        $response = $this->call('GET', '/orders?' . $query);
        $data = (array) $response->getData();
        echo "\n -- Test for getting list with empty parameters  -- \n";
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->response->status());
        $this->assertArrayHasKey('error', $data);
        $this->assertInternalType('array', $data);
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);
    }
    
    public function testOrderListWithValidData ()
    {
        $page = 1;
        $limit = 3;
        $params = ['page' => $page, 'limit' => $limit];
        $params = $this->getRequestData($params);
        $orders = $this->getOrders($limit);
        $this->orderRepositoryMock
            ->shouldReceive('index')
            ->with($page, $limit)
            ->once()
            ->andReturn($orders);
        $response = $this->orderControllerMock->index($params);
        $data = json_decode($response->getContent(), true);

        echo "\n -- Test for getting list with valid parameters  -- \n";
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('id', (array) $data[0]);
        $this->assertArrayHasKey('distance', (array) $data[0]);
        $this->assertArrayHasKey('status', (array) $data[0]);
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);
        echo "\n >>>>> UNIT TESTING ENDS  HERE. <<<<< \n\n";
    }
    
    /**
     * @param int, $limit, Order Limit
     *
     * @return Order
     */
    protected function getOrders(int $limit)
    {
        $orders = [];
        $faker = Faker::create();
        for ($i = 0; $i <= $limit; $i++) {
            $param = [
                'origin' => [$faker->latitude, $faker->longitude], 
                'destination' => [$faker->latitude, $faker->longitude]
            ];
            $orders[] = $this->createOrder($param);
        }
        return $orders;
    }

    /**
     * @param array, $requestBbody, Request body
     *
     * @return Request
     */
    protected function getRequestData(array $requestData)
    {
        $request = new Request();
        $request->replace($requestData);
        return $request;
    }
    
    
    protected function createOrder($params)
    {
        $id = rand(1,100);
        $origint_lat = (float)array_get($params,'origin.0','');
        $origin_long = (float)array_get($params,'origin.1','');
        $destination_lat = (float)array_get($params,'destination.0','');
        $destination_long = (float)array_get($params,'destination.1','');
        $distance = $this->_getDistanceWithoutApi($origint_lat, $origin_long, $destination_lat, $destination_long);
        $this->app->instance('Order', $this->orderMock);
        $this->orderMock
            ->shouldReceive('getDistance')
            ->withAnyArgs()
            ->andReturn($distance);
        $order = new Order();
        $order->id = $id;
        $order->origin_lat = $origint_lat;
        $order->origin_long = $origin_long;
        $order->destination_lat = $destination_lat;
        $order->destination_long = $destination_long;
        $order->distance = $distance;
        $order->status = Order::UNASSIGNED;
        $order->created_at = date('Y-m-d H:i:s');
        $order->updated_at = date('Y-m-d H:i:s');
        return $order;
    }
    
    protected function _getDistanceWithoutApi($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo)
    {
      $earthRadius = 6371000;
      // convert from degrees to radians
      $latFrom = deg2rad($latitudeFrom);
      $lonFrom = deg2rad($longitudeFrom);
      $latTo = deg2rad($latitudeTo);
      $lonTo = deg2rad($longitudeTo);

      $latDelta = $latTo - $latFrom;
      $lonDelta = $lonTo - $lonFrom;

      $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
      return $angle * $earthRadius;
    }
}