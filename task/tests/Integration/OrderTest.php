<?php
use Faker\Factory as Faker;
use Illuminate\Http\Response;

class OrderTest extends TestCase
{
    
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testCreateOrderWithValidData()
    {
        echo "\n >>>>> INTEGRATION TESTING STARTS  HERE. <<<<< \n";
        echo "\n STARTING TESTING FOR CREATING ORDERS...";
        echo "\n -- Test for valid parameter -- \n";
        $param = [
                'origin' => ["28.4595", "77.0266"], // Gurgaon
                'destination' => ["28.7041", "77.1025"], //Delhi
            ];
        $this->_orderCheck($param, Response::HTTP_OK); 
    }

    public function testCreateOrderWithInvalidData()
    {
        $this->faker = Faker::create();
        echo "\n -- Test for empty parameter -- \n";
        $this->_orderCheck([], Response::HTTP_BAD_REQUEST); 
        
        echo "\n -- Test for invalid request parameter --\n";
        $param = [
                'origgin' => [$this->faker->latitude, $this->faker->longitude], 
                'ddestination' => [$this->faker->latitude, $this->faker->longitude]
            ];
        $this->_orderCheck($param, Response::HTTP_UNPROCESSABLE_ENTITY);
        
        echo "\n -- Test for invalid values -- \n";
        $param = [
                'origin' => [$this->faker->latitude, "invalid"], 
                'destination' => ["invalid", $this->faker->longitude]
            ];
        $this->_orderCheck($param, Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    public function testUpdateOrderWithValidData()
    {
        echo "\n STARTING TESTING FOR UPDATING ORDERS...";
        echo "\n -- Test for valid data --";
        $param = [
                'origin' => ["28.4595", "77.0266"], // Gurgaon
                'destination' => ["28.7041", "77.1025"], //Delhi
            ];
        $response = $this->call('POST', '/orders', $param);
        $data = $response->getData();
        $order_id = $data->id;

        $param = ['status' => 'TAKEN'];
        $this->_orderCheck($param,Response::HTTP_OK,$order_id); //success
    }

    public function testUpdateOrderWithInvalidData()
    {
        $param = [
                'origin' => ["28.4595", "77.0266"], // Gurgaon
                'destination' => ["28.7041", "77.1025"], //Delhi
            ];
        $response = $this->call('POST', '/orders', $param);
        $data = $response->getData();
        $order_id = $data->id;
        echo "\n -- Test for empty parameter -- \n";
        $this->_orderCheck([], Response::HTTP_BAD_REQUEST,$order_id); 
        
        echo "\n -- Test for invalid parameter -- \n";
        $this->_orderCheck(['statuss' => 'TAKEN'], Response::HTTP_UNPROCESSABLE_ENTITY,$order_id);
        
        echo "\n -- Test for invalid value -- \n";
        $this->_orderCheck(['status' => 'TAKEEN'], Response::HTTP_UNPROCESSABLE_ENTITY,$order_id);
        
        echo "\n -- Test for invalid order id -- \n";
        $this->_orderCheck(['status' => 'TAKEN'], Response::HTTP_UNPROCESSABLE_ENTITY,"AK47");
        
        //marking order ID as TAKEN
        $this->call('PATCH', '/orders/' . $order_id, ['status' => 'TAKEN']);
        
        echo "\n -- Test for already taken order id -- \n";
        $this->_orderCheck(['status' => 'TAKEN'], 409,$order_id);
    }


    public function testOrderListWithValidData()
    {
        echo "\n STARTING TESTING FOR LISTING ORDERS...";
        echo "\n -- Test for getting valid list  -- \n";
        $this->_orderCheck([], Response::HTTP_OK,NULL,'page=1&limit=3');
        
    }

    public function testOrderListWithInvalidData()
    {
        echo "\n -- Test for getting list with invalid parameter -- \n";
        $this->_orderCheck([], Response::HTTP_BAD_REQUEST,NULL,'pagee=1&limit=5');
        
        echo "\n -- Test for getting list with invalid values -- \n";
        $this->_orderCheck([], Response::HTTP_UNPROCESSABLE_ENTITY,NULL,'page=NOTHING&limit=5');
        
        echo "\n -- Test for getting list with negative values -- \n";
        $this->_orderCheck([], Response::HTTP_UNPROCESSABLE_ENTITY,NULL,'page=-1&limit=-5');
        
        echo "\n -- Test for getting list with 0 limit -- \n";
        $this->_orderCheck([], Response::HTTP_BAD_REQUEST,NULL,'page=1&limit=0');
        
        echo "\n -- Test for getting list with empty parameter -- \n";
        $this->_orderCheck([], Response::HTTP_BAD_REQUEST,NULL,'');
        
        echo "\n >>>>> INTEGRATION TESTING ENDS  HERE. <<<<< \n\n";
    }
    
    /**
 * Test Order Create/Insert API Request
 *
 * @param array|json|object|NULL, $params, Request Body
 * @param integer $status, Desired status code
 * @param integer|NULL $id, Desired order id
 * @param string|NULL $query_string, Desired query parameter
 */
    protected function _orderCheck($params, $status,$id= NULL,$query_string= NULL)
    {
        if(!empty($id)){
            $response = $this->call('PATCH', '/orders/' . $id, $params);
        }elseif($query_string !== NULL){
            $response = $this->call('GET', "/orders?$query_string", []);
        }else{
            $response = $this->call('POST', '/orders', $params);
        }
        
        $data = (array) $response->getData();
        $this->assertEquals($status, $this->response->status());
        switch ($status) {
            case Response::HTTP_OK:
                if(!empty($id)){
                    $this->assertArrayHasKey('status', $data);
                    $this->assertContains($value = 'SUCCESS', $data, "response doesn't contains SUCCESS as value");
                }elseif($query_string !== NULL){
                    foreach ($data as $param) {
                        $this->assertArrayHasKey('id', (array) $param);
                        $this->assertArrayHasKey('distance', (array) $param);
                        $this->assertArrayHasKey('status', (array) $param);
                    }
                    $this->assertCount(3,$data, "Returned 3 elememts successfully.");
                }else{
                    $this->assertArrayHasKey('id', $data);
                    $this->assertArrayHasKey('distance', $data);
                    $this->assertArrayHasKey('status', $data);
                }
                
                break;
            case Response::HTTP_BAD_REQUEST:
                $this->assertArrayHasKey('error', $data);
                break;
            case Response::HTTP_UNPROCESSABLE_ENTITY:
                $this->assertArrayHasKey('error', $data);
                break;
            case Response::HTTP_NOT_FOUND:
                $this->assertArrayHasKey('error', $data);
                if(!empty($id)){
                    $this->assertContains($value = 'ORDER_ID_NOT_FOUND', $data);
                }
                break;
            case Response::HTTP_INTERNAL_SERVER_ERROR:
                $this->assertArrayHasKey('error', $data, 'INTERNAL_SERVER_ERROR');
                break;
            default:
                # code...
                break;
        }
    }
    
}
