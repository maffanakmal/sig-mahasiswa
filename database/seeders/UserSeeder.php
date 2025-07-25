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
                'nama_lengkap' => 'Admin SISTEM',
                'username' => 'sistem-admin',
                'email' => 'usnigis@email.com',
                'password' => Hash::make('password123'),
                'role' => 'BAAKPSI',
            ],
            [
                'user_uuid' => (string) Str::uuid(),
                'nama_lengkap' => 'Admin BAAKPSI',
                'username' => 'baakpsi-admin',
                'email' => 'baakpsi@email.com',
                'password' => Hash::make('password123'),
                'role' => 'BAAKPSI',
            ],
            [
                'user_uuid' => (string) Str::uuid(),
                'nama_lengkap' => 'Warek 3 User',
                'username' => 'warek3-user',
                'email' => 'warek3@email.com',
                'password' => Hash::make('password123'),
                'role' => 'Warek 3',
            ],
            [
                'user_uuid' => (string) Str::uuid(),
                'nama_lengkap' => 'PMB User',
                'username' => 'pmb-user',
                'email' => 'pmb@email.com',
                'password' => Hash::make('password123'),
                'role' => 'PMB',
            ],
        ]);
    }
}
