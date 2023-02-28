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
                'description' => 'Urgent'
            ],
            [
                'description' => 'Ordinary'
            ],
            [
                'description' => 'Confidential'
            ],
        ];

        $category = Category::insert($category);
    }
}
