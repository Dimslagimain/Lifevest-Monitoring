<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    protected $fillable = [
        'registration',
        'seat_id',
        'row',
        'col',
        'class_type',
        'expiry_date',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    /**
     * Get the status based on expiry date
     */
    public function getStatusAttribute(): string
    {
        if (! $this->expiry_date) {
            return 'no-data';
        }

        $daysRemaining = $this->days_remaining;

        if ($daysRemaining < 0) {
            return 'expired';
        } elseif ($daysRemaining < 90) {
            return 'critical';
        } elseif ($daysRemaining < 180) {
            return 'warning';
        }

        return 'safe';
    }

    /**
     * Get days remaining until expiry
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (! $this->expiry_date) {
            return null;
        }

        $today = now()->startOfDay();
        $expiry = \Illuminate\Support\Carbon::parse($this->expiry_date)->startOfDay();
        $diff = $today->diff($expiry);

        return (int) ($diff->invert ? -$diff->days : $diff->days);
    }
}
