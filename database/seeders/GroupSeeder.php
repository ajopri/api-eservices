<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('groups')->insert([
            ['rcc' => 'MCA', 'name' => 'NIPPON'],
            ['rcc' => 'MCA', 'name' => '3M GROUP'],
        ]);
    }
}
