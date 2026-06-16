<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Cement & Concrete',
            'Steel & TMT Bars',
            'Electrical Materials',
        ];

        foreach ($categories as $name) {
            ProductCategory::firstOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }

        $this->command?->info('Product categories seeded: ' . count($categories));
    }
}
