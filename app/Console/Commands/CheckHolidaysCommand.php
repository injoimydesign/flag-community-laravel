<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Holiday;

/**
 * Create this file at: app/Console/Commands/CheckHolidaysCommand.php
 *
 * Run with: php artisan holidays:check
 */
class CheckHolidaysCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'holidays:check';

    /**
     * The console command description.
     */
    protected $description = 'Check for holidays with null dates and other data issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking holidays for data issues...');
        $this->newLine();

        $allHolidays = Holiday::all();
        $issuesFound = false;

        // Check for null dates
        $nullDates = Holiday::whereNull('date')->get();
        if ($nullDates->count() > 0) {
            $issuesFound = true;
            $this->error("Found {$nullDates->count()} holiday(s) with NULL dates:");

            $rows = [];
            foreach ($nullDates as $holiday) {
                $rows[] = [
                    $holiday->id,
                    $holiday->name,
                    $holiday->active ? 'Yes' : 'No',
                    $holiday->recurring ? 'Yes' : 'No',
                ];
            }

            $this->table(
                ['ID', 'Name', 'Active', 'Recurring'],
                $rows
            );
            $this->newLine();
        }

        // Check for null placement_days_before
        $nullPlacementDays = Holiday::whereNull('placement_days_before')->get();
        if ($nullPlacementDays->count() > 0) {
            $issuesFound = true;
            $this->warn("Found {$nullPlacementDays->count()} holiday(s) with NULL placement_days_before:");

            $rows = [];
            foreach ($nullPlacementDays as $holiday) {
                $rows[] = [
                    $holiday->id,
                    $holiday->name,
                    $holiday->date ? $holiday->date->format('Y-m-d') : 'NULL',
                ];
            }

            $this->table(
                ['ID', 'Name', 'Date'],
                $rows
            );
            $this->newLine();
        }

        // Check for null removal_days_after
        $nullRemovalDays = Holiday::whereNull('removal_days_after')->get();
        if ($nullRemovalDays->count() > 0) {
            $issuesFound = true;
            $this->warn("Found {$nullRemovalDays->count()} holiday(s) with NULL removal_days_after:");

            $rows = [];
            foreach ($nullRemovalDays as $holiday) {
                $rows[] = [
                    $holiday->id,
                    $holiday->name,
                    $holiday->date ? $holiday->date->format('Y-m-d') : 'NULL',
                ];
            }

            $this->table(
                ['ID', 'Name', 'Date'],
                $rows
            );
            $this->newLine();
        }

        // Summary
        if (!$issuesFound) {
            $this->info('✓ All holidays look good! No issues found.');
        } else {
            $this->newLine();
            $this->warn('Issues found! Please fix these holidays in the database.');
            $this->info('You can fix them by:');
            $this->line('1. Running the holiday seeder: php artisan db:seed --class=HolidaySeeder');
            $this->line('2. Manually updating in the admin panel');
            $this->line('3. Running SQL updates directly');
        }

        $this->newLine();
        $this->info("Total holidays in database: {$allHolidays->count()}");

        return 0;
    }
}
