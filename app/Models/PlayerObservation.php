<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PlayerObservation extends Model
{
    use HasFactory;

    protected $table = 'player_observations';

    public const ACTIVE = 1;
    public const INACTIVE = 0;

    public const TYPE_TACTIC = 1;
    public const TYPE_PSYCHIQUE = 2;
    public const TYPE_PSYCOLOGICAL = 3;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'player',
        'type',
        'notes',
        'user',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'integer',
            'status' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $observation): void {
            if (empty($observation->id)) {
                $observation->id = (string) Str::uuid();
            }
        });
    }

    public function player()
    {
        return $this->belongsTo(Player::class, 'player');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user');
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_TACTIC => 'Táctica',
            self::TYPE_PSYCHIQUE => 'Física',
            self::TYPE_PSYCOLOGICAL => 'Psicológica',
        ];
    }
}
