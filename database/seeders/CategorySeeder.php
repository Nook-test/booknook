<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['name' => 'classics'],
            ['name' => 'Action'],
            ['name' => 'Horror'],
            ['name' => 'Crime'],
            ['name' => ' true crime'],
            ['name' => 'fantasy'],
            ['name' => 'humor'],
            ['name' => 'romance']
        ];
        \App\Models\Category::insert($categories);
    }
}
