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
        $now      = Carbon::now();
        $password = Hash::make('12345678');

        $students = [];
        for ($i = 1; $i <= 10; $i++) {
            $students[] = [
                'id'          => $i,
                'name'        => 'Student ' . $i,
                'email'       => 'student' . $i . '@gmail.com',
                'password'    => $password,
                'is_verified' => true,
                'role'        => 'student',
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        $parents = [];
        for ($i = 1; $i <= 10; $i++) {
            $parents[] = [
                'id'          => 10 + $i,
                'name'        => 'Parent ' . $i,
                'email'       => 'parent' . $i . '@gmail.com',
                'password'    => $password,
                'is_verified' => true,
                'role'        => 'parent',
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        $teachers = [];
        for ($i = 1; $i <= 10; $i++) {
            $teachers[] = [
                'id'          => 20 + $i,
                'name'        => 'Teacher ' . $i,
                'email'       => 'teacher' . $i . '@gmail.com',
                'password'    => $password,
                'is_verified' => true,
                'role'        => 'teacher',
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        User::insert(array_merge($students, $parents, $teachers));
    }
}
