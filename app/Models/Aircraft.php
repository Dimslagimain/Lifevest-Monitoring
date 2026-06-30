<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aircraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration',
        'airline_id',
        'type',
        'icon',
        'layout',
        'status',
        'pn_adult',
        'pn_crew',
        'pn_infant',
    ];

    /**
     * Get the airline that owns this aircraft.
     */
    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        // 1. Auto-Assign Icon before creation
        static::creating(function ($aircraft) {
            $type = strtoupper($aircraft->type);
            $layout = strtoupper($aircraft->layout);

            // Logic Icon
            if (str_contains($layout, 'CARGO') || str_contains($type, 'F') && ! str_contains($type, '737')) {
                $aircraft->icon = '📦'; // Cargo
            } elseif (str_starts_with($type, 'A')) {
                $aircraft->icon = '🛩️'; // Airbus (Start with A...)
            } elseif (str_contains($type, '777')) {
                $aircraft->icon = '🛫'; // Boeing 777
            } else {
                $aircraft->icon = '✈️'; // Default (Boeing 737 etc)
            }
        });

        // 2. Cascade Delete Seats
        static::deleting(function ($aircraft) {
            Seat::where('registration', $aircraft->registration)->delete();
        });
    }
}
