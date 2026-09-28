<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerLevelProgress extends Model
{
    protected $table = 'player_level_progress';

    protected $fillable = [
        'player_id',
        'level_id',
        'best_score_pct',
        'best_time_ms',
        'attempts',
        'is_unlocked',
    ];

    protected $casts = [
        'is_unlocked' => 'boolean',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }
}
