<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Team extends Model
{
    use BelongsToTenant, HasFactory;

    public const TYPE_COMPETITIVE = 1;
    public const TYPE_FORMATIVE = 2;

    public const ACTIVE = 1;
    public const INACTIVE = 0;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'year',
        'type',
        'season',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public static function typeOptions(): array
    {
        return [
            'competitive' => self::TYPE_COMPETITIVE,
            'formative' => self::TYPE_FORMATIVE,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $team): void {
            if (empty($team->id)) {
                $team->id = (string) Str::uuid();
            }
        });
    }

    public function playerRosters()
    {
        return $this->hasMany(PlayerRoster::class, 'team');
    }

    public function managerRosters()
    {
        return $this->hasMany(ManagerRoster::class, 'team');
    }

    public function trainings()
    {
        return $this->hasMany(Training::class, 'team');
    }

    public function matches()
    {
        return $this->hasMany(MatchModel::class, 'team');
    }

    // Sedes donde opera este equipo
    public function venues()
    {
        return $this->belongsToMany(SportsVenue::class, 'team_venue', 'team', 'venue')
        ->using(TeamVenue::class)
        ->withPivot('id', 'status')
        ->withTimestamps();
    }
}
