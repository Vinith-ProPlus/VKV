<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $amenities = [
            ['name' => 'Swimming Pool', 'is_active' => 1],
            ['name' => 'Gymnasium', 'is_active' => 1],
            ['name' => 'Children Play Area', 'is_active' => 1],
        ];
        
        $amenityIds = [];
        foreach ($amenities as $amenity) {
            $a = \App\Models\Amenity::firstOrCreate(['name' => $amenity['name']], $amenity);
            $amenityIds[$a->name] = $a->id;
        }

        $projects = [
            [
                'name' => 'Green Meadows',
                'location' => 'North City',
                'latitude' => '12.9716',
                'longitude' => '77.5946',
                'is_active' => 1,
                'amenities' => [
                    ['name' => 'Swimming Pool', 'desc' => 'Olympic size pool for all residents.'],
                    ['name' => 'Gymnasium', 'desc' => 'Fully equipped modern gym.'],
                ]
            ],
            [
                'name' => 'Sunset Villas',
                'location' => 'West End',
                'latitude' => '13.0827',
                'longitude' => '80.2707',
                'is_active' => 1,
                'amenities' => [
                    ['name' => 'Children Play Area', 'desc' => 'Safe and fun play area for kids.'],
                    ['name' => 'Swimming Pool', 'desc' => 'Infinity pool with sunset view.'],
                ]
            ],
            [
                'name' => 'Lakeview Residency',
                'location' => 'East Lake',
                'latitude' => '11.0168',
                'longitude' => '76.9558',
                'is_active' => 1,
                'amenities' => [
                    ['name' => 'Gymnasium', 'desc' => '24/7 access gym for residents.'],
                    ['name' => 'Children Play Area', 'desc' => 'Large play area with modern equipment.'],
                ]
            ],
        ];

        foreach ($projects as $projectData) {
            $project = \App\Models\Project::firstOrCreate([
                'name' => $projectData['name'],
                'location' => $projectData['location'],
                'latitude' => $projectData['latitude'],
                'longitude' => $projectData['longitude'],
                'is_active' => $projectData['is_active'],
            ]);
            foreach ($projectData['amenities'] as $am) {
                if (isset($amenityIds[$am['name']])) {
                    \App\Models\ProjectAmenity::create([
                        'project_id' => $project->id,
                        'amenity_id' => $amenityIds[$am['name']],
                        'description' => $am['desc'],
                    ]);
                }
            }
        }
    }
        
}
