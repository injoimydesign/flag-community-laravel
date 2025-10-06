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
        'date',
        'recurring',
        'placement_days_before',
        'removal_days_after',
        'active',
        'sort_order',
        'frequency',
        'dates',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date' => 'date',
        'recurring' => 'boolean',
        'active' => 'boolean',
        'dates' => 'array',
        'placement_days_before' => 'integer',
        'removal_days_after' => 'integer',
        'sort_order' => 'integer',
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
     * Alias for flagPlacements() - for backward compatibility.
     * FIXED: Added this method to match controller usage
     */
    public function placements()
    {
        return $this->flagPlacements();
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

        return $query->active()->where('date', '>=', $today);
    }

    // Static methods

    /**
     * Get all holidays for a specific year.
     */
    public static function getHolidaysForYear($year)
    {
        return self::active()
            ->ordered()
            ->whereYear('date', $year)
            ->get();
    }

    /**
     * Get next upcoming holiday.
     */
    public static function getNextHoliday()
    {
        return self::active()
            ->where('date', '>=', Carbon::now())
            ->orderBy('date')
            ->first();
    }

    // Helper methods

    /**
     * Check if holiday is active in a specific year.
     */
    public function isActiveInYear($year)
    {
      // CRITICAL FIX: Check if date is null
      if (!$this->date) {
          return false;
      }

      if (!$this->recurring) {
          return $this->date->year == $year;
      }

      // Recurring holidays are active every year
      return true;
    }

    /**
     * Get the next occurrence date after a given date.
     */
    public function getNextDateAfter(Carbon $date)
    {

      // CRITICAL FIX: Check if date is null
      if (!$this->date) {
          return null;
      }

        if (!$this->recurring) {
            return $this->date->gt($date) ? $this->date : null;
        }

        // For recurring holidays, get next occurrence
        $holidayDate = $this->date->copy()->year($date->year);

        if ($holidayDate->gt($date)) {
            return $holidayDate;
        }

        // Return next year's occurrence
        return $holidayDate->addYear();
    }

    /**
     * Get placement and removal dates for a specific year.
     */
    public function getPlacementDatesForYear($year)
    {
      // CRITICAL FIX: Check if date is null
      if (!$this->date) {
          throw new \Exception("Holiday {$this->id} ({$this->name}) has no date set");
      }

        $holidayDate = $this->date->copy()->year($year);

        return [
            'holiday_date' => $holidayDate,
            'placement_date' => $holidayDate->copy()->subDays($this->placement_days_before),
            'removal_date' => $holidayDate->copy()->addDays($this->removal_days_after),
        ];
    }

    /**
     * Get formatted name with date.
     */
    public function getFullNameAttribute()
    {
      // CRITICAL FIX: Check if date is null
      if (!$this->date) {
          return $this->name . ' (No date set)';
      }

        return $this->name . ' (' . $this->date->format('M j') . ')';
    }

    /**
     * Check if holiday is upcoming.
     */
    public function isUpcoming()
    {
      // CRITICAL FIX: Check if date is null
      if (!$this->date) {
          return false;
      }

        return $this->date->isFuture();
    }

    /**
     * Check if holiday is past.
     */
    public function isPast()
    {
      // CRITICAL FIX: Check if date is null
      if (!$this->date) {
          return false;
      }

        return $this->date->isPast();
    }

    /**
     * Get days until holiday.
     */
    public function getDaysUntilAttribute()
    {

      // CRITICAL FIX: Check if date is null
      if (!$this->date) {
          return false;
      }

        return Carbon::now()->diffInDays($this->date, false);
    }

    /**
     * Get placement count for this holiday.
     */
    public function getPlacementCount()
    {
        return $this->flagPlacements()->count();
    }

    /**
     * Get scheduled placement count.
     */
    public function getScheduledPlacementCount()
    {
        return $this->flagPlacements()->where('status', 'scheduled')->count();
    }

    /**
     * Get completed placement count.
     */
    public function getCompletedPlacementCount()
    {
        return $this->flagPlacements()->where('status', 'placed')->count();
    }
}
