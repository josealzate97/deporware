<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Team extends Model
{
    use HasFactory;

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
