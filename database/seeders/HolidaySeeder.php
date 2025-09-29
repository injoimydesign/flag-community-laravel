<?php
// database/seeders/HolidaySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;
use Carbon\Carbon;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            [
                'name' => 'New Year\'s Day',
                'description' => 'The first day of the year',
                'date' => '2025-01-01',
                'recurring' => true,
                'placement_days_before' => 1,
                'removal_days_after' => 2,
                'active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Martin Luther King Jr. Day',
                'description' => 'Federal holiday honoring civil rights leader Martin Luther King Jr.',
                'date' => '2025-01-20',
                'recurring' => true,
                'placement_days_before' => 1,
                'removal_days_after' => 1,
                'active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Presidents Day',
                'description' => 'Federal holiday honoring all U.S. presidents',
                'date' => '2025-02-17',
                'recurring' => true,
                'placement_days_before' => 1,
                'removal_days_after' => 1,
                'active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Memorial Day',
                'description' => 'Holiday honoring those who died while serving in the U.S. military',
                'date' => '2025-05-26',
                'recurring' => true,
                'placement_days_before' => 2,
                'removal_days_after' => 2,
                'active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Flag Day',
                'description' => 'Commemorates the adoption of the flag of the United States',
                'date' => '2025-06-14',
                'recurring' => true,
                'placement_days_before' => 1,
                'removal_days_after' => 1,
                'active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Independence Day',
                'description' => 'Celebrates the Declaration of Independence and the birth of the United States',
                'date' => '2025-07-04',
                'recurring' => true,
                'placement_days_before' => 2,
                'removal_days_after' => 2,
                'active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Labor Day',
                'description' => 'Federal holiday celebrating the American labor movement',
                'date' => '2025-09-01',
                'recurring' => true,
                'placement_days_before' => 1,
                'removal_days_after' => 1,
                'active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Patriots Day',
                'description' => 'Commemorating September 11, 2001 attacks',
                'date' => '2025-09-11',
                'recurring' => true,
                'placement_days_before' => 1,
                'removal_days_after' => 1,
                'active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Columbus Day',
                'description' => 'Federal holiday commemorating Christopher Columbus\'s arrival in the Americas',
                'date' => '2025-10-13',
                'recurring' => true,
                'placement_days_before' => 1,
                'removal_days_after' => 1,
                'active' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'Veterans Day',
                'description' => 'Federal holiday honoring military veterans',
                'date' => '2025-11-11',
                'recurring' => true,
                'placement_days_before' => 2,
                'removal_days_after' => 2,
                'active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Thanksgiving Day',
                'description' => 'National holiday of thanksgiving and harvest',
                'date' => '2025-11-27',
                'recurring' => true,
                'placement_days_before' => 1,
                'removal_days_after' => 2,
                'active' => true,
                'sort_order' => 11,
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                ['name' => $holiday['name']],
                $holiday
            );
        }

        $this->command->info('Holidays seeded successfully!');
    }
}
