<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Apartment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'owner_id',
        'governorate',
        'governorate_ar',
        'city',
        'city_ar',
        'address',
        'address_ar',
        'nightly_price',
        'monthly_price',
        'bedrooms',
        'bathrooms',
        'living_rooms',
        'size',
        'description',
        'description_ar',
        'photos',
        'amenities',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'amenities' => 'array',
            'nightly_price' => 'decimal:2',
            'monthly_price' => 'decimal:2',
            'size' => 'decimal:2',
        ];
    }

    /**
     * Get the owner of the apartment.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the bookings for this apartment.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the ratings for this apartment.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get the favorites for this apartment.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Calculate average rating for this apartment.
     */
    public function getAverageRatingAttribute(): float
    {
        return $this->ratings()->avg('rating') ?? 0.0;
    }

    /**
     * Check if apartment is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
