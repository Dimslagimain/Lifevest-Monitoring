<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Aircraft;

class ActivityLog extends Model
{
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
