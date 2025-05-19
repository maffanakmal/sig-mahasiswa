<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'user_uuid' => (string) Str::uuid(),
                'nama_user' => 'Admin BAAKPSI',
                'username' => 'baakpsi_admin',
                'password' => Hash::make('password123'), // Ganti sesuai kebutuhan
                'role' => 'BAAKPSI',
            ],
            [
                'user_uuid' => (string) Str::uuid(),
                'nama_user' => 'Warek 3 User',
                'username' => 'warek3_user',
                'password' => Hash::make('password123'),
                'role' => 'Warek 3',
            ],
            [
                'user_uuid' => (string) Str::uuid(),
                'nama_user' => 'PMB User',
                'username' => 'pmb_user',
                'password' => Hash::make('password123'),
                'role' => 'PMB',
            ],
        ]);
    }
}
