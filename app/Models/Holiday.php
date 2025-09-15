<?php
// app/Models/Holiday.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Holiday extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'frequency',
        'dates',
        'active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'dates' => 'array',
        'active' => 'boolean',
    ];

    // Relationships

    /**
     * Get flag placements for this holiday.
     */
    public function flagPlacements()
    {
        return $this->hasMany(FlagPlacement::class);
    }

    /**
     * Get routes for this holiday.
     */
    public function routes()
    {
        return $this->hasMany(Route::class);
    }

    // Scopes

    /**
     * Scope to get only active holidays.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope to get upcoming holidays.
     */
    public function scopeUpcoming($query, $year = null)
    {
        $year = $year ?: date('Y');
        $today = Carbon::now()->format('Y-m-d');
        
        return $query->active()->where(function ($q) use ($year, $today) {
            $q->whereJsonContains('dates', $year)
              ->orWhere(function ($subQ) use ($today) {
                  // For holidays with specific dates in current year
                  $subQ->whereRaw('JSON_EXTRACT(dates, "$[0]") >= ?', [$today]);
              });
        });
    }

    // Static methods

    /**
     * Get all holidays for a specific year.
     */
    public static function getHolidaysForYear($year)
    {
        return self::active()->ordered()->get()->filter(function ($holiday) use ($year) {
            return $holiday->isActiveInYear($year);
        });
    }

    /**
     * Get next upcoming holiday.
     */
    public static function getNextHoliday()
    {
        $holidays = self::upcoming()->get();
        $today = Carbon::now();
        
        return $holidays->sortBy(function ($holiday) use ($today) {
            $nextDate = $holiday->getNextDateAfter($today);
            return $nextDate ? $nextDate->timestamp : PHP_INT_MAX;
        })->first();
    }

    // Helper methods

    /**
     * Check if holiday is active in a specific year.
     */
    public function isActiveInYear($year)
    {
        if ($this->frequency === 'annual') {
            return true;
        }
        
        // For special holidays like Patriots Day (every 5 years)
        if ($this->frequency === 'special') {
            return in_array($year, $this->dates ?: []);
        }
        
        return false;
    }

    /**
     * Get the holiday date for a specific year.
     */
    public function getDateForYear($year)
    {
        if ($this->frequency === 'annual') {
            // Calculate date based on holiday rules
            return $this->calculateAnnualDate($year);
        }
        
        if ($this->frequency === 'special') {
            $yearData = collect($this->dates)->firstWhere('year', $year);
            return $yearData ? Carbon::parse($yearData['date']) : null;
        }
        
        return null;
    }

    /**
     * Get next occurrence of this holiday after a given date.
     */
    public function getNextDateAfter(Carbon $date)
    {
        $year = $date->year;
        
        // Check current year first
        $holidayDate = $this->getDateForYear($year);
        if ($holidayDate && $holidayDate->gt($date)) {
            return $holidayDate;
        }
        
        // Check next year
        $nextYearDate = $this->getDateForYear($year + 1);
        return $nextYearDate;
    }

    /**
     * Calculate annual holiday date for a given year.
     */
    private function calculateAnnualDate($year)
    {
        // This would contain logic for calculating holiday dates
        // For now, using static dates stored in the dates array
        $holidayDates = [
            'presidents-day' => Carbon::parse("third monday of february $year"),
            'memorial-day' => Carbon::parse("last monday of may $year"),
            'flag-day' => Carbon::createFromDate($year, 6, 14),
            'independence-day' => Carbon::createFromDate($year, 7, 4),
            'veterans-day' => Carbon::createFromDate($year, 11, 11),
        ];
        
        return $holidayDates[$this->slug] ?? null;
    }

    /**
     * Get placement date (usually a few days before the holiday).
     */
    public function getPlacementDateForYear($year)
    {
        $holidayDate = $this->getDateForYear($year);
        if (!$holidayDate) return null;
        
        // Place flags 2 days before the holiday
        return $holidayDate->copy()->subDays(2);
    }

    /**
     * Get removal date (usually a few days after the holiday).
     */
    public function getRemovalDateForYear($year)
    {
        $holidayDate = $this->getDateForYear($year);
        if (!$holidayDate) return null;
        
        // Remove flags 3 days after the holiday
        return $holidayDate->copy()->addDays(3);
    }

    /**
     * Get all placement dates for subscriptions.
     */
    public function getPlacementDatesForYear($year)
    {
        $holidayDate = $this->getDateForYear($year);
        if (!$holidayDate) return [];
        
        return [
            'holiday_date' => $holidayDate,
            'placement_date' => $this->getPlacementDateForYear($year),
            'removal_date' => $this->getRemovalDateForYear($year),
        ];
    }
}