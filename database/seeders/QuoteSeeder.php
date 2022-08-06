<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $quotes = [
            [
                'quote' => 'every thing has beauty, but not everyone sees it'
            ],
            [
                'quote' => 'i wonder how long i can comfort myself by saying: "this is enough for now" '
            ],
            [
                'quote' => 'البحر مالح والناس مصالح'
            ],
            [
                'quote' => 'غدار يازمن'
            ],
            [
                'quote' => 'رضاكي يا امي'
            ],

        ];
        \App\Models\Quote::insert($quotes);
    }
}
