<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'game_session_id',
        'question_index',
        'question_seed',
        'topic_code',
        'chosen',
        'is_correct',
        'misconception_label',
        'time_taken_ms',
        'answered_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'answered_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'game_session_id');
    }
}
