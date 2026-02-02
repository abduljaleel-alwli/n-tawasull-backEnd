<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'المسطح',
            'المسحوب',
            'المواسير',
            'التيوبات',
            'الزوايا',
            'الجسور',
            'الزنك',
            'الصاج',
            'قطاعات متنوعة',
        ];

        foreach ($categories as $index => $name) {
            DB::table('categories')->insert([
                'name' => $name,
                'slug' => Str::slug($name, '-'),
                'is_active' => true,
                'display_order' => $index + 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
