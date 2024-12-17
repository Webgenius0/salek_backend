<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now      = Carbon::now();
        $password = Hash::make('12345678');

        User::insert([
            [
                'id'          => 1,
                'name'        => 'Jhon Doe',
                'email'       => 'student@gmail.com',
                'password'    => $password,
                'is_verified' => true,
                'role'        => 'student',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 2,
                'name'        => 'Sheldon Cotrel',
                'email'       => 'parent@gmail.com',
                'password'    => $password,
                'is_verified' => true,
                'role'        => 'parent',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 3,
                'name'        => 'Ramiz Raza',
                'email'       => 'teacher@gmail.com',
                'password'    => $password,
                'is_verified' => true,
                'role'        => 'teacher',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 4,
                'name'        => 'Admin',
                'email'       => 'admin@gmail.com',
                'password'    => $password,
                'is_verified' => true,
                'role'        => 'admin',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);
    }
}
