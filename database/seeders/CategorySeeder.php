<?php

namespace Database\Seeders;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
    */
    public function run(): void
    {
        $now = Carbon::now();

        Category::insert([
            [
                'id'         => 1,
                'name'       => 'Category 1',
                'slug'       => 'category-1',
                'created_by' => 21,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 2,
                'name'       => 'Category 2',
                'slug'       => 'category-2',
                'created_by' => 21,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 3,
                'name'       => 'Category 3',
                'slug'       => 'category-3',
                'created_by' => 21,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
