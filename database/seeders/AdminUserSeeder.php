<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Achmad Joko Priyono',
            'email' => 'achmadjp7@gmail.com',
            'activated_at' => now(),
            'role_id' => 1,
            'group_id' => 0,
            'active' => 1,
            'default' => 1,
            'contact_code' => $this->generateUniqueCode(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function generateUniqueCode()
    {
        do {
            $code = random_int(100, 999);
        } while (User::where("contact_code", $code)->first());

        return $code;
    }
}
