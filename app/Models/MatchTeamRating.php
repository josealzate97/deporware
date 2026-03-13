<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MatchTeamRating extends Model
{
    use HasFactory;

    protected $table = 'match_team_ratings';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'match',
        'referee_rating',
        'coach_rating',
        'teammates_rating',
        'opponents_rating',
        'fans_rating',
    ];

    protected function casts(): array
    {
        return [
            'referee_rating' => 'integer',
            'coach_rating' => 'integer',
            'teammates_rating' => 'integer',
            'opponents_rating' => 'integer',
            'fans_rating' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $rating): void {
            if (empty($rating->id)) {
                $rating->id = (string) Str::uuid();
            }
        });
    }

    public function match()
    {
        return $this->belongsTo(MatchModel::class, 'match');
    }
}
