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

    // Sedes donde trabaja este usuario
    public function venues()
    {
        return $this->belongsToMany(SportsVenue::class, 'user_venue', 'user', 'venue')
                    ->using(UserVenue::class)
                    ->withPivot('id', 'status')
                    ->withTimestamps();
    }

}
