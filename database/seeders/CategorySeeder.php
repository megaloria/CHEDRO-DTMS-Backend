<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $category = [
            [
                'description' => 'Urgent',
                'is_assignable' => true
            ],
            [
                'description' => 'Ordinary',
                'is_assignable' => true
            ],
            [
                'description' => 'Confidential',
                'is_assignable' => false
            ],
        ];

        $category = Category::insert($category);

    }
}
