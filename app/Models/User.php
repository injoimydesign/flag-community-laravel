<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'latitude',
        'longitude',
        'in_service_area',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'in_service_area' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Relationships

    /**
     * Get all subscriptions for this user.
     */
    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Subscription::class);
    }



    /**
     * Get the active subscription for this user.
     */
    public function activeSubscriptions()
    {
        return $this->hasMany(\App\Models\Subscription::class)->where('status', 'active');
    }

    /**
     * Get all notifications for this user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get routes assigned to this user.
     */
    public function assignedRoutes()
    {
        return $this->hasMany(Route::class, 'assigned_user_id');
    }

    // Helper methods

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Check if user has an active subscription.
     */
    public function hasActiveSubscription()
    {
        return $this->activeSubscription()->exists();
    }

    /**
     * Get coordinates as array for mapping.
     */
    public function getCoordinatesAttribute()
    {
        return [
            'lat' => (float) $this->latitude,
            'lng' => (float) $this->longitude
        ];
    }



      /**
       * Check if user is an admin.
       */
      public function isAdmin(): bool
      {
          return $this->role === 'admin';
      }

      /**
       * Check if user is a customer.
       */
      public function isCustomer(): bool
      {
          return $this->role === 'customer';
      }

    
      /**
       * Get the user's full address.
       */
      public function getFullAddressAttribute(): string
      {
          return "{$this->address}, {$this->city}, {$this->state} {$this->zip_code}";
      }

      /**
       * Scope to get only admin users.
       */
      public function scopeAdmins($query)
      {
          return $query->where('role', 'admin');
      }

      /**
       * Scope to get only customer users.
       */
      public function scopeCustomers($query)
      {
          return $query->where('role', 'customer');
      }

      /**
       * Check service area coverage for this user.
       */
      public function checkServiceAreaCoverage(): bool
      {
          // This would integrate with your ServiceArea model
          // For now, return true if latitude/longitude exist
          $this->in_service_area = !is_null($this->latitude) && !is_null($this->longitude);
          $this->save();

          return $this->in_service_area;
      }
}
