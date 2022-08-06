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
            ['name' => 'Classics'],
            ['name' => 'Action'],
            ['name' => 'Horror'],
            ['name' => 'Crime'],
            ['name' => 'True Crime'],
            ['name' => 'Fantasy'],
            ['name' => 'Historical Fiction'],
            ['name' => 'Adventure'],
            ['name' => 'Graphic Novel'],
            ['name' => 'Comic Book'],
            ['name' => 'Humor'],
            ['name' => 'Mystery'],
            ['name' => 'Romance'],
            ['name' => 'Poetry'],
            ['name' => 'Cookbooks'],
            ['name' => 'Science Fiction'],
            ['name' => 'Other']
        ];
        \App\Models\Category::insert($categories);
    }
}
