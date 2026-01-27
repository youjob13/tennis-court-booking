<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'court_id',
        'user_id',
        'start_datetime',
        'duration_hours',
        'total_price',
        'status',
        'payment_reference',
        'lock_expires_at',
        'unlocked_after',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_datetime' => 'datetime',
            'lock_expires_at' => 'datetime',
            'unlocked_after' => 'datetime',
            'total_price' => 'decimal:2',
            'duration_hours' => 'integer',
            'status' => 'string',
        ];
    }

    /**
     * Get the court that owns the booking.
     */
    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    /**
     * Get the user that owns the booking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if booking is locked.
     */
    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    /**
     * Check if booking is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if lock has expired.
     */
    public function hasExpiredLock(): bool
    {
        return $this->isLocked()
            && $this->lock_expires_at
            && $this->lock_expires_at->isPast();
    }

    /**
     * Scope a query to only include confirmed bookings.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope a query to only include locked bookings.
     */
    public function scopeLocked($query)
    {
        return $query->where('status', 'locked');
    }

    /**
     * Scope a query to only include expired locks.
     */
    public function scopeExpiredLocks($query)
    {
        return $query->where('status', 'locked')
            ->where('lock_expires_at', '<', now());
    }
}
