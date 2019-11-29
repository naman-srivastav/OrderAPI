<?php

namespace App\Repositories;

use App\Models\Order;
use Exception;

class OrderRepository
{
    /**
     * Function to place new order.
     *
     * @param array $input
     * @return object of order
     */
    public function create($input)
    {
        try {
            return Order::create($input);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Function to take order.
     *
     * @param integer $id
     * @return bool
     */
    public function update($id)
    {
        try {
            return Order::where(['id' => $id, 'status' => Order::UNASSIGNED])->update(['status' => Order::TAKEN]);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Function to list orders.
     *
     * @param integer $page
     * @param integer $limit
     * @return array list of orders
     */
    public function index($page, $limit)
    {
        try {
            $ordersList = Order::select('id', 'distance', 'status')->paginate($limit);
            $ordersList->appends(['limit' => $limit, 'page' => $page]);
            return $ordersList->items();
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Function to fetch order.
     *
     * @param integer $id
     * @return object of order
     */
    public function fetch($id)
    {
        try {
            return Order::where('id', $id)->first();
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
