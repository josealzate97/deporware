<?php

namespace App\Models;

use App\Support\TenantStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tenant extends Model
{
    public const ACTIVE   = 1;
    public const INACTIVE = 0;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'id',
        'number',
        'name',
        'slug',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status'     => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $tenant): void {
            if (empty($tenant->id)) {
                $tenant->id = (string) Str::uuid();
            }

            // Asignar número correlativo único
            if (empty($tenant->number)) {
                $tenant->number = ((int) static::max('number')) + 1;
            }

            // Auto-generar slug si no viene explícito
            if (empty($tenant->slug)) {
                $tenant->slug = static::generateSlug($tenant->name, $tenant->number);
            }
        });

        // Al crear un tenant, generar su estructura de carpetas en storage
        static::created(function (self $tenant): void {
            TenantStorage::scaffold($tenant);
        });
    }

    /**
     * Genera el slug único de un tenant.
     * Formato: slugify(name) + '_' + número con cero-padding (3 dígitos)
     * Ej: "Drogueria Luz" + 2 → "drogueria_luz_002"
     */
    public static function generateSlug(string $name, int $number): string
    {
        $base = Str::slug($name, '_');
        return $base . '_' . str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    public function configurations()
    {
        return $this->hasMany(Configuration::class, 'tenant_id');
    }

    public function teams()
    {
        return $this->hasMany(Team::class, 'tenant_id');
    }

    public function players()
    {
        return $this->hasMany(Player::class, 'tenant_id');
    }

    public function venues()
    {
        return $this->hasMany(SportsVenue::class, 'tenant_id');
    }

    public function trainings()
    {
        return $this->hasMany(Training::class, 'tenant_id');
    }

    public function matches()
    {
        return $this->hasMany(MatchModel::class, 'tenant_id');
    }

    public function rivalTeams()
    {
        return $this->hasMany(RivalTeam::class, 'tenant_id');
    }

    public function attackPoints()
    {
        return $this->hasMany(AttackPoint::class, 'tenant_id');
    }

    public function defensivePoints()
    {
        return $this->hasMany(DefensivePoint::class, 'tenant_id');
    }
}
