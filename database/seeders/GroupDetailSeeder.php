<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('group_details')->insert([
            ['group_id' => 1, 'card_code' => 'CINIP02', 'card_name' => 'NIPPON PAINT (S) CO PTE LTD (SGD)', 'currency' => 'SGD', 'cust_bu' => 'ST'],
            ['group_id' => 1, 'card_code' => 'CINIP03', 'card_name' => 'NIPPON PAINT (VIETNAM) CO.,LTD', 'currency' => 'USD', 'cust_bu' => 'IS'],
        ]);
    }
}
