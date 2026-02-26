<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MatchModel extends Model
{
    use HasFactory;

    protected $table = 'matches';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'match_date',
        'team',
        'rival',
        'venue',
        'location',
        'match_status',
        'match_result',
        'side',
        'final_score',
        'match_notes',
        'match_file',
    ];

    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
            'match_status' => 'integer',
            'match_result' => 'integer',
            'side' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $match): void {
            if (empty($match->id)) {
                $match->id = (string) Str::uuid();
            }
        });
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team');
    }

    public function rival()
    {
        return $this->belongsTo(RivalTeam::class, 'rival');
    }

    public function feedback()
    {
        return $this->hasOne(MatchFeedback::class, 'match');
    }

    // Sede donde se juega el partido (si aplica)
    public function venue()
    {
        return $this->belongsTo(SportsVenue::class, 'venue');
    }
}
