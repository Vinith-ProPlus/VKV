<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Tax;
use App\Models\UnitOfMeasurement;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $gst = Tax::firstOrCreate(
            ['name' => 'GST 18%'],
            ['percentage' => 18, 'is_active' => true]
        );

        $uoms = [
            'Bag' => 'BAG',
            'Kilogram' => 'KG',
            'Meter' => 'MTR',
        ];

        $uomIds = [];
        foreach ($uoms as $name => $code) {
            $uomIds[$code] = UnitOfMeasurement::firstOrCreate(
                ['code' => $code],
                ['name' => $name, 'is_active' => true]
            )->id;
        }

        $products = [
            [
                'name' => 'OPC 53 Grade Cement',
                'code' => 'PRD-CEM-001',
                'category' => 'Cement & Concrete',
                'uom_code' => 'BAG',
            ],
            [
                'name' => 'TMT Steel Bar 12mm',
                'code' => 'PRD-STL-001',
                'category' => 'Steel & TMT Bars',
                'uom_code' => 'KG',
            ],
            [
                'name' => 'Copper Wire 2.5 sq mm',
                'code' => 'PRD-ELC-001',
                'category' => 'Electrical Materials',
                'uom_code' => 'MTR',
            ],
        ];

        foreach ($products as $item) {
            $category = ProductCategory::where('name', $item['category'])->first();

            if (!$category) {
                $this->command?->warn("Category not found: {$item['category']}. Run ProductCategorySeeder first.");

                continue;
            }

            Product::firstOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'category_id' => $category->id,
                    'tax_id' => $gst->id,
                    'uom_id' => $uomIds[$item['uom_code']],
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info('Products seeded: ' . count($products));
    }
}
