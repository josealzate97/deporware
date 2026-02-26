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

    const ACTIVE = 1;
    const INACTIVE = 0;

    const ROLE_ROOT = 1;
    const ROLE_ADMIN = 2;
    const ROLE_STAFF = 3;
    const ROLE_COORDINATOR = 4;
    const ROLE_PLAYER = 5;

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
            'hired_date' => 'datetime',
            'status' => 'integer',
        ];
    }

    public function getAuthIdentifierName() {
        return 'id';
    }

}
