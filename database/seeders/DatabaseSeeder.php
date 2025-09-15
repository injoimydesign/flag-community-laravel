<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ServiceArea;
use App\Models\FlagType;
use App\Models\FlagSize;
use App\Models\FlagProduct;
use App\Models\Holiday;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@flagsacrosscommunity.com',
            'password' => Hash::make('password123'),
            'phone' => '555-0100',
            'address' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'TX',
            'zip_code' => '12345',
            'latitude' => 30.6280,
            'longitude' => -96.3344,
            'in_service_area' => true,
            'role' => 'admin',
        ]);

        // Create sample service area (Bryan-College Station, TX)
        $serviceArea = ServiceArea::create([
            'name' => 'Bryan-College Station Area',
            'center_latitude' => 30.6280,
            'center_longitude' => -96.3344,
            'radius_miles' => 25,
            'zip_codes' => [
                '77801', '77802', '77803', '77807', '77808', // Bryan zip codes
                '77840', '77841', '77842', '77843', '77845', // College Station zip codes
                '77868', '77881', '77845', '77834', // Surrounding areas
            ],
            'active' => true,
        ]);

        // Create flag types
        $flagTypes = [
            [
                'name' => 'United States Flag',
                'slug' => 'us-flag',
                'description' => 'The official flag of the United States of America',
                'category' => 'us',
                'sort_order' => 1,
            ],
            [
                'name' => 'Army Flag',
                'slug' => 'army-flag',
                'description' => 'United States Army flag',
                'category' => 'military',
                'sort_order' => 2,
            ],
            [
                'name' => 'Navy Flag',
                'slug' => 'navy-flag',
                'description' => 'United States Navy flag',
                'category' => 'military',
                'sort_order' => 3,
            ],
            [
                'name' => 'Air Force Flag',
                'slug' => 'air-force-flag',
                'description' => 'United States Air Force flag',
                'category' => 'military',
                'sort_order' => 4,
            ],
            [
                'name' => 'Marine Corps Flag',
                'slug' => 'marine-corps-flag',
                'description' => 'United States Marine Corps flag',
                'category' => 'military',
                'sort_order' => 5,
            ],
            [
                'name' => 'Coast Guard Flag',
                'slug' => 'coast-guard-flag',
                'description' => 'United States Coast Guard flag',
                'category' => 'military',
                'sort_order' => 6,
            ],
            [
                'name' => 'Space Force Flag',
                'slug' => 'space-force-flag',
                'description' => 'United States Space Force flag',
                'category' => 'military',
                'sort_order' => 7,
            ],
        ];

        foreach ($flagTypes as $flagType) {
            FlagType::create($flagType);
        }

        // Create flag sizes
        $flagSizes = [
            [
                'name' => "3'x5'",
                'dimensions' => "3'x5'",
                'sort_order' => 1,
            ],
            [
                'name' => "4'x6'",
                'dimensions' => "4'x6'",
                'sort_order' => 2,
            ],
            [
                'name' => "5'x8'",
                'dimensions' => "5'x8'",
                'sort_order' => 3,
            ],
        ];

        foreach ($flagSizes as $flagSize) {
            FlagSize::create($flagSize);
        }

        // Create flag products (combinations of types and sizes with pricing)
        $flagTypeIds = FlagType::pluck('id');
        $flagSizeIds = FlagSize::pluck('id');

        // Pricing structure
        $pricing = [
            1 => [ // 3'x5'
                'us' => ['onetime' => 25.00, 'annual' => 89.00],
                'military' => ['onetime' => 30.00, 'annual' => 109.00],
            ],
            2 => [ // 4'x6'
                'us' => ['onetime' => 35.00, 'annual' => 129.00],
                'military' => ['onetime' => 40.00, 'annual' => 149.00],
            ],
            3 => [ // 5'x8'
                'us' => ['onetime' => 45.00, 'annual' => 169.00],
                'military' => ['onetime' => 50.00, 'annual' => 189.00],
            ],
        ];

        foreach ($flagTypeIds as $flagTypeId) {
            $flagType = FlagType::find($flagTypeId);
            
            foreach ($flagSizeIds as $flagSizeId) {
                $category = $flagType->category;
                $prices = $pricing[$flagSizeId][$category];
                
                FlagProduct::create([
                    'flag_type_id' => $flagTypeId,
                    'flag_size_id' => $flagSizeId,
                    'one_time_price' => $prices['onetime'],
                    'annual_subscription_price' => $prices['annual'],
                    'inventory_count' => 50, // Starting inventory
                    'min_inventory_alert' => 5,
                    'active' => true,
                ]);
            }
        }

        // Create holidays
        $holidays = [
            [
                'name' => "Presidents' Day",
                'slug' => 'presidents-day',
                'description' => 'Federal holiday honoring all U.S. presidents, observed on the third Monday in February',
                'frequency' => 'annual',
                'dates' => [], // Will be calculated dynamically
                'sort_order' => 1,
            ],
            [
                'name' => 'Memorial Day',
                'slug' => 'memorial-day',
                'description' => 'Federal holiday honoring military personnel who died in service, observed on the last Monday in May',
                'frequency' => 'annual',
                'dates' => [],
                'sort_order' => 2,
            ],
            [
                'name' => 'Flag Day',
                'slug' => 'flag-day',
                'description' => 'Commemorates the adoption of the United States flag, observed on June 14',
                'frequency' => 'annual',
                'dates' => [],
                'sort_order' => 3,
            ],
            [
                'name' => 'Independence Day',
                'slug' => 'independence-day',
                'description' => 'Commemorates the Declaration of Independence, observed on July 4',
                'frequency' => 'annual',
                'dates' => [],
                'sort_order' => 4,
            ],
            [
                'name' => 'Veterans Day',
                'slug' => 'veterans-day',
                'description' => 'Federal holiday honoring military veterans, observed on November 11',
                'frequency' => 'annual',
                'dates' => [],
                'sort_order' => 5,
            ],
            [
                'name' => 'Patriots Day',
                'slug' => 'patriots-day',
                'description' => 'Remembrance of September 11, 2001 attacks, observed every 5th anniversary',
                'frequency' => 'special',
                'dates' => [2026, 2031, 2036, 2041, 2046], // Every 5 years starting 2026
                'sort_order' => 6,
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::create($holiday);
        }

        // Create sample customer
        $customer = User::create([
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'customer@example.com',
            'password' => Hash::make('password123'),
            'phone' => '555-0200',
            'address' => '456 Oak Street',
            'city' => 'Bryan',
            'state' => 'TX',
            'zip_code' => '77801',
            'latitude' => 30.6744,
            'longitude' => -96.3698,
            'in_service_area' => true,
            'role' => 'customer',
        ]);

        echo "Database seeded successfully!\n";
        echo "Admin login: admin@flagsacrosscommunity.com / password123\n";
        echo "Customer login: customer@example.com / password123\n";
    }
}