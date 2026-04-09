<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Player extends Model
{
    use BelongsToTenant, HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    public const ACTIVE = 1;
    public const INACTIVE = 0;

    public const NATIONALITY_COLOMBIA = 1;
    public const NATIONALITY_VENEZUELA = 2;
    public const NATIONALITY_ECUADOR = 3;
    public const NATIONALITY_SPAIN = 4;
    public const NATIONALITY_USA = 5;

    public const POSICION_ARQUERO = 1;
    public const POSICION_DEFENSA_CENTRAL = 2;

    public const POSICION_LATERAL_DERECHO = 3;
    public const POSICION_LATERAL_IZQUIERDO = 4;
    public const POSICION_MEDIOCAMPISTA_DEFENSIVO = 5;
    public const POSICION_MEDIOCAMPISTA_CENTRAL = 6;
    public const POSICION_MEDIOCAMPISTA_OFENSIVO = 7;
    public const POSICION_MEDIOCAMPISTA_DERECHO = 8;
    public const POSICION_MEDIOCAMPISTA_IZQUIERDO = 9;
    public const POSICION_EXTREMO_DERECHO = 10;
    public const POSICION_EXTREMO_IZQUIERDO = 11;
    public const POSICION_DELANTERO_CENTRO = 12;
    public const POSICION_SEGUNDA_PUNTA = 13;

    public const PIE_DERECHO = 1;
    public const PIE_IZQUIERDO = 2;
    public const PIE_AMBOS = 3;

    protected $fillable = [
        'id',
        'name',
        'lastname',
        'photo',
        'nit',
        'email',
        'phone',
        'birthdate',
        'nacionality',
        'position',
        'positions',
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
            'positions' => 'array',
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
            self::POSICION_ARQUERO => 'Arquero',
            self::POSICION_DEFENSA_CENTRAL => 'Defensa Central',
            self::POSICION_LATERAL_DERECHO => 'Lat. Derecho',
            self::POSICION_LATERAL_IZQUIERDO => 'Lat. Izquierdo',
            self::POSICION_MEDIOCAMPISTA_DEFENSIVO => 'Mediocampista Defensivo',
            self::POSICION_MEDIOCAMPISTA_CENTRAL => 'Mediocampista Central',
            self::POSICION_MEDIOCAMPISTA_OFENSIVO => 'Mediocampista Ofensivo',
            self::POSICION_MEDIOCAMPISTA_DERECHO => 'Mediocampista Derecho',
            self::POSICION_MEDIOCAMPISTA_IZQUIERDO => 'Mediocampista Izquierdo',
            self::POSICION_EXTREMO_DERECHO => 'Extremo Derecho',
            self::POSICION_EXTREMO_IZQUIERDO => 'Extremo Izquierdo',
            self::POSICION_DELANTERO_CENTRO => 'Delantero Centro',
            self::POSICION_SEGUNDA_PUNTA => 'Segunda Punta',
        ];
    }

    public static function normalizePositions(array|int|string|null $positions, ?int $fallback = null): array
    {
        $raw = is_array($positions) ? $positions : [$positions];

        if ($fallback !== null) {
            $raw[] = $fallback;
        }

        $allowed = array_keys(self::positionOptions());

        return collect($raw)
            ->map(fn ($value) => is_numeric($value) ? (int) $value : null)
            ->filter(fn ($value) => $value !== null && in_array($value, $allowed, true))
            ->unique()
            ->values()
            ->all();
    }

    public function getResolvedPositionsAttribute(): array
    {
        return self::normalizePositions($this->positions ?? [], $this->position);
    }

    public function getPrimaryPositionAttribute(): ?int
    {
        return $this->resolved_positions[0] ?? null;
    }

    public function getPositionLabelsAttribute(): array
    {
        $options = self::positionOptions();

        return collect($this->resolved_positions)
            ->map(fn (int $position) => $options[$position] ?? null)
            ->filter()
            ->values()
            ->all();
    }

    public function hasPosition(int $position): bool
    {
        return in_array($position, $this->resolved_positions, true);
    }

    public static function footOptions(): array
    {
        return [
            self::PIE_DERECHO => 'Derecha',
            self::PIE_IZQUIERDO => 'Izquierda',
            self::PIE_AMBOS => 'Ambas',
        ];
    }
}
