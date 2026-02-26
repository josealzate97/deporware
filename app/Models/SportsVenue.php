<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SportsVenue extends Model
{
    use HasFactory;

    protected $table = 'sports_venues';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'address',
        'city',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $venue): void {
            if (empty($venue->id)) {
                $venue->id = (string) Str::uuid();
            }
        });
    }

    // Entrenamientos realizados en esta sede
    public function trainings()
    {
        return $this->hasMany(Training::class, 'venue');
    }

    // Partidos jugados en esta sede
    public function matches()
    {
        return $this->hasMany(MatchModel::class, 'venue');
    }

    // Usuarios (entrenadores, coordinadores, etc.) que trabajan en esta sede
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_venue', 'venue', 'user')
                    ->using(UserVenue::class)
                    ->withPivot('id', 'status')
                    ->withTimestamps();
    }

    // Equipos que operan en esta sede
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_venue', 'venue', 'team')
                    ->using(TeamVenue::class)
                    ->withPivot('id', 'status')
                    ->withTimestamps();
    }
}
