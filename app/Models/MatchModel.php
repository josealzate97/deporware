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

    public const STATUS_SCHEDULED = 1;
    public const STATUS_COMPLETED = 2;
    public const STATUS_CANCELLED = 3;

    public const RESULT_WIN = 1;
    public const RESULT_LOSS = 2;
    public const RESULT_DRAW = 3;

    public const SIDE_HOME = 1;
    public const SIDE_AWAY = 2;



    protected $fillable = [
        'id',
        'match_date',
        'match_round',
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
        'team_picture',
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

    public function teamRating()
    {
        return $this->hasOne(MatchTeamRating::class, 'match');
    }

    // Sede donde se juega el partido (si aplica)
    public function venue()
    {
        return $this->belongsTo(SportsVenue::class, 'venue');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_SCHEDULED => 'AGENDADO',
            self::STATUS_COMPLETED => 'COMPLETADO',
            self::STATUS_CANCELLED => 'CANCELADO',
        ];
    }

    public static function resultOptions(): array
    {
        return [
            self::RESULT_WIN => 'GANADO',
            self::RESULT_LOSS => 'PERDIDO',
            self::RESULT_DRAW => 'EMPATE',
        ];
    }

    public static function sideOptions(): array
    {
        return [
            self::SIDE_HOME => 'LOCAL',
            self::SIDE_AWAY => 'VISITANTE',
        ];
    }

    public static function formationOptions(): array {
        return [
            '4-4-2' => '4-4-2',
            '4-3-3' => '4-3-3',
            '3-5-2' => '3-5-2',
            '4-2-3-1' => '4-2-3-1',
            '5-3-2' => '5-3-2',
            '4-1-4-1' => '4-1-4-1',
            '5-4-1' => '5-4-1',
            '3-4-3' => '3-4-3',
            '4-5-1' => '4-5-1',
            '4-4-1-1' => '4-4-1-1',
            '4-3-2-1' => '4-3-2-1',
            '3-4-2-1' => '3-4-2-1',
            '3-6-1' => '3-6-1',
            '5-2-3' => '5-2-3',
            '4-2-2-2' => '4-2-2-2',
            '4-3-1-2' => '4-3-1-2',
            '3-3-3-1' => '3-3-3-1',
            '3-4-1-2' => '3-4-1-2',
            '4-1-3-2' => '4-1-3-2',
            '4-2-1-3' => '4-2-1-3',
            '5-3-1-1' => '5-3-1-1',
            '3-5-1-1' => '3-5-1-1',
            '5-2-1-2' => '5-2-1-2',
            '4-4-0' => '4-4-0',
            '4-2-4' => '4-2-4',
            '4-6-0' => '4-6-0',
            '2-3-5' => '2-3-5',
            '3-2-5' => '3-2-5',
            '2-4-4' => '2-4-4',
            '3-3-4' => '3-3-4',
        ];
    }
}
