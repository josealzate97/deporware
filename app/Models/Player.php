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
    public const NATIONALITY_PERU = 4;
    public const NATIONALITY_BRAZIL = 5;
    public const NATIONALITY_ARGENTINA = 6;
    public const NATIONALITY_CHILE = 7;
    public const NATIONALITY_URUGUAY = 8;
    public const NATIONALITY_PARAGUAY = 9;
    public const NATIONALITY_BOLIVIA = 10;
    public const NATIONALITY_MEXICO = 11;
    public const NATIONALITY_SPAIN = 12;
    public const NATIONALITY_USA = 13;

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

    public static function nationalityOptions(): array
    {
        return [
            self::NATIONALITY_COLOMBIA => 'Colombia',
            self::NATIONALITY_VENEZUELA => 'Venezuela',
            self::NATIONALITY_ECUADOR => 'Ecuador',
            self::NATIONALITY_PERU => 'Perú',
            self::NATIONALITY_BRAZIL => 'Brasil',
            self::NATIONALITY_ARGENTINA => 'Argentina',
            self::NATIONALITY_CHILE => 'Chile',
            self::NATIONALITY_URUGUAY => 'Uruguay',
            self::NATIONALITY_PARAGUAY => 'Paraguay',
            self::NATIONALITY_BOLIVIA => 'Bolivia',
            self::NATIONALITY_MEXICO => 'México',
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
