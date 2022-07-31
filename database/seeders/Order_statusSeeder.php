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
            ['status' => 'delivery in progress'],
            ['status' => 'delivered'],
            ['status' => 'canceled']
        ];
        \App\Models\OrderStatus::insert($status);
    }
}
