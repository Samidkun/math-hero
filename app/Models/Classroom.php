<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'code',
        'name',
        'grade',
        'teacher_name',
    ];

    public function players(): HasMany
    {
        return $this->hasMany(Player::class, 'class_id');
    }
}
