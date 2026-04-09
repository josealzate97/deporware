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
        });

        // Al crear un tenant, generar su estructura de carpetas en storage
        static::created(function (self $tenant): void {
            TenantStorage::scaffold($tenant);
        });
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
