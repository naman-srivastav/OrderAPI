<?php

use Illuminate\Database\Seeder;
use App\Models\Order;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        for($i=0;$i<5;$i++) {
            $order = new Order;
            $order->origin_lat = $faker->latitude;
            $order->origin_long = $faker->latitude;
            $order->destination_lat = $faker->latitude;
            $order->destination_long = $faker->latitude;
            $order->status = ($i%2 == 0) ? Order::UNASSIGNED : Order::TAKEN;
            $order->distance =rand(100000, 1000000); 
            $order->save();
        }
    }
}
