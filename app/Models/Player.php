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

    public const NATIONALITY_COLOMBIA = 1;
    public const NATIONALITY_VENEZUELA = 2;
    public const NATIONALITY_ECUADOR = 3;
    public const NATIONALITY_SPAIN = 4;
    public const NATIONALITY_USA = 5;

    public const POSITION_GOALKEEPER = 1;
    public const POSITION_DEFENDER = 2;
    public const POSITION_MIDFIELDER = 3;
    public const POSITION_FORWARD = 4;

    public const FOOT_RIGHT = 1;
    public const FOOT_LEFT = 2;
    public const FOOT_BOTH = 3;

    protected $fillable = [
        'id',
        'name',
        'lastname',
        'nit',
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

    public function activeRoster()
    {
        return $this->hasOne(PlayerRoster::class, 'player')
            ->where('status', PlayerRoster::ACTIVE)
            ->latestOfMany('created_at');
    }

    public function latestRoster()
    {
        return $this->hasOne(PlayerRoster::class, 'player')
            ->latestOfMany('created_at');
    }

    public function trainingAttendance()
    {
        return $this->hasMany(TrainingAttendance::class, 'player');
    }

    public static function nationalityOptions(): array
    {
        return [
            self::NATIONALITY_COLOMBIA => 'Colombia',
            self::NATIONALITY_VENEZUELA => 'Venezuela',
            self::NATIONALITY_ECUADOR => 'Ecuador',
            self::NATIONALITY_SPAIN => 'España',
            self::NATIONALITY_USA => 'Estados Unidos',
        ];
    }

    public static function positionOptions(): array
    {
        return [
            self::POSITION_GOALKEEPER => 'Arquero',
            self::POSITION_DEFENDER => 'Defensa',
            self::POSITION_MIDFIELDER => 'Mediocampo',
            self::POSITION_FORWARD => 'Delantero',
        ];
    }

    public static function footOptions(): array
    {
        return [
            self::FOOT_RIGHT => 'Derecha',
            self::FOOT_LEFT => 'Izquierda',
            self::FOOT_BOTH => 'Ambas',
        ];
    }
}
