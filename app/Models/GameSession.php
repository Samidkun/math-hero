<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameSession extends Model
{
    protected $fillable = [
        'player_id',
        'level_id',
        'status',
        'started_at',
        'finished_at',
        'score_pct',
        'total_time_ms',
        'abandon_reason',
        'question_seeds',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'question_seeds' => 'array',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
