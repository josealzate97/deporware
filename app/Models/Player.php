<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Player extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    public const ACTIVE = 1;
    public const INACTIVE = 0;

    protected $fillable = [
        'id',
        'name',
        'lastname',
        'email',
        'phone',
        'birthdate',
        'nacionality',
        'position',
        'dorsal',
        'foot',
        'weight',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'nacionality' => 'integer',
            'position' => 'integer',
            'dorsal' => 'integer',
            'foot' => 'integer',
            'weight' => 'integer',
            'status' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $player): void {
            if (empty($player->id)) {
                $player->id = (string) Str::uuid();
            }
        });
    }

    public function contacts()
    {
        return $this->hasMany(PlayerContact::class, 'player');
    }

    public function observations()
    {
        return $this->hasMany(PlayerObservation::class, 'player');
    }

    public function rosters()
    {
        return $this->hasMany(PlayerRoster::class, 'player');
    }

    public function trainingAttendance()
    {
        return $this->hasMany(TrainingAttendance::class, 'player');
    }
}
