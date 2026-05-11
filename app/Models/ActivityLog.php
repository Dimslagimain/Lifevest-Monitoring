<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use Prunable;

    /**
     * Get the prunable model query.
     */
    public function prunable()
    {
        // Keep only the last 30 days of logs for production performance
        return static::where('created_at', '<=', now()->subDays(30));
    }

    protected $fillable = [
        'user_id',
        'registration',
        'action',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    /**
     * Get the user who performed the activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the aircraft related to this log
     */
    public function aircraft(): BelongsTo
    {
        return $this->belongsTo(Aircraft::class, 'registration', 'registration');
    }
}
