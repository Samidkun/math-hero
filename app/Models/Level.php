<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    protected $fillable = [
        'grade',
        'level_number',
        'topic_code',
        'title',
    ];

    public function progress(): HasMany
    {
        return $this->hasMany(PlayerLevelProgress::class);
    }
}
