<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    protected $fillable = [
        'nickname',
        'class_id',
        'pin_hash',
        'total_stars',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(PlayerLevelProgress::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }
}
