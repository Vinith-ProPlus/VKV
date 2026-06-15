<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            'Swimming Pool',
            'Gymnasium',
            'Children Play Area',
            'Club House',
            'Landscaped Garden',
            'Indoor Games Room',
            'Outdoor Sports Court',
            'Jogging Track',
            'CCTV Surveillance',
            '24x7 Security',
            'Gated Community',
            'Visitor Parking',
            'Covered Car Parking',
            'Power Backup',
            'Lift',
            'Rainwater Harvesting',
            'Sewage Treatment Plant',
            'Water Treatment Plant',
            'Intercom Facility',
            'Fire Safety System',
            'Community Hall',
            'Library',
            'Yoga / Meditation Room',
            'Senior Citizen Sit-out',
            'Convenience Store',
            'ATM',
            'Wi-Fi Enabled Common Areas',
            'EV Charging Station',
            'Pet Park',
            'Amphitheatre',
        ];

        foreach ($amenities as $name) {
            Amenity::firstOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }

        $this->command?->info('Amenities seeded: ' . count($amenities));
    }
}
