<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable {
    
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public $incrementing = false;
    public $timestamps = true;
    protected $keyType = 'string';

    public const ACTIVE = 1;
    public const INACTIVE = 0;

    public const ROLE_ROOT = 1;
    public const ROLE_SPORT_MANAGER = 2;
    public const ROLE_COACH = 3;
    public const ROLE_COORDINATOR = 4;

    protected $fillable = [
        'id',
        'name',
        'email',
        'phone',
        'username',
        'role',
        'hired_date',
        'password',
        'status',
    ];

    protected static function booted(): void {
        static::creating(function (self $user): void {
            if (empty($user->id)) {
                $user->id = (string) Str::uuid();
            }
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'password' => 'hashed',
            'hired_date' => 'date',
            'status' => 'integer',
        ];
    }

    public static function roleOptions(): array {
        return [
            self::ROLE_ROOT => 'Super Admin',
            self::ROLE_SPORT_MANAGER => 'Gerente Deportivo',
            self::ROLE_COORDINATOR => 'Coordinador',
            self::ROLE_COACH => 'Entrenador',
        ];
    }

    public function getRoleLabelAttribute(): string {
        return static::roleOptions()[$this->role] ?? 'Sin rol';
    }

    public function getAuthIdentifierName() {
        return 'id';
    }

    /**
     * Ítems del menú lateral visibles para este usuario según su rol.
     * Cada ítem: ['label', 'route', 'icon', 'url']
     */
    public function menuItems(): array
    {
        $catalog = [
            'dashboard'  => ['label' => 'Dashboard',      'route' => 'home',             'icon' => 'fa-dashboard',             'url' => 'home'],
            'users'      => ['label' => 'Personal',        'route' => 'users.index',      'icon' => 'fa-user',                  'url' => 'users'],
            'venues'     => ['label' => 'Sedes',           'route' => 'venues.index',     'icon' => 'fa-building-circle-check', 'url' => 'venues'],
            'teams'      => ['label' => 'Plantillas',      'route' => 'teams.index',      'icon' => 'fa-shield',                'url' => 'teams'],
            'players'    => ['label' => 'Jugadores',       'route' => 'players.index',    'icon' => 'fa-people-group',          'url' => 'players'],
            'matches'    => ['label' => 'Partidos',        'route' => 'matches.index',    'icon' => 'fa-futbol',                'url' => 'matches'],
            'trainings'  => ['label' => 'Entrenamientos',  'route' => 'trainings.index',  'icon' => 'fa-dumbbell',              'url' => 'trainings'],
        ];

        $visible = match ((int) $this->role) {
            self::ROLE_ROOT          => array_keys($catalog),
            self::ROLE_SPORT_MANAGER => ['dashboard', 'users', 'venues', 'teams', 'players', 'trainings', 'matches'],
            self::ROLE_COORDINATOR   => ['dashboard', 'teams', 'players', 'matches', 'trainings'],
            self::ROLE_COACH         => ['dashboard', 'teams', 'players', 'matches', 'trainings'],
            default                  => ['dashboard'],
        };

        return array_values(array_intersect_key($catalog, array_flip($visible)));
    }

    // Sedes donde trabaja este usuario
    public function venues()
    {
        return $this->belongsToMany(SportsVenue::class, 'user_venue', 'user', 'venue')
        ->using(UserVenue::class)
        ->withPivot('id', 'status')
        ->withTimestamps();
    }

}
