<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Order_statusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $status = [
            ['status' => 'Processing'],
            ['status' => 'Delivery in progress'],
            ['status' => 'Delivered'],
            ['status' => 'Canceled']
        ];
        \App\Models\OrderStatus::insert($status);
    }
}
