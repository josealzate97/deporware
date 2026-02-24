<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MatchFeedback extends Model
{
    use HasFactory;

    protected $table = 'matches_feedback';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'match',
        'match_formation',
        'attack_strengths',
        'attack_weaknesses',
        'defense_strengths',
        'defense_weaknesses',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'attack_strengths' => 'integer',
            'attack_weaknesses' => 'integer',
            'defense_strengths' => 'integer',
            'defense_weaknesses' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $feedback): void {
            if (empty($feedback->id)) {
                $feedback->id = (string) Str::uuid();
            }
        });
    }

    public function match()
    {
        return $this->belongsTo(MatchModel::class, 'match');
    }
}
