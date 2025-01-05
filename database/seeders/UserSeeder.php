<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $password = Hash::make(config('seeder.default_password', '12345678')); // Use config or env for default password

        // Reusable function to create user data
        $createUsers = function ($role, $startId, $count) use ($password, $now) {
            $users = [];
            for ($i = 1; $i <= $count; $i++) {
                $users[] = [
                    'name'        => ucfirst($role) . ' ' . $i,
                    'email'       => $role . $i . '@gmail.com',
                    'password'    => $password,
                    'is_verified' => true,
                    'role'        => $role,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
            return $users;
        };

        // Generate users
        $students = $createUsers('student', 1, 10);
        $parents = $createUsers('parent', 11, 10);
        $teachers = $createUsers('teacher', 21, 10);
        $admin = [
            [
                'name'        => 'Admin',
                'email'       => 'admin@gmail.com',
                'password'    => $password,
                'is_verified' => true,
                'role'        => 'admin',
                'created_at'  => $now,
                'updated_at'  => $now,
            ]
        ];

        // Insert all users into the database
        User::insert(array_merge($students, $parents, $teachers, $admin));
    }
}
