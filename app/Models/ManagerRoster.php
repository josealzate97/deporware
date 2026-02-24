<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ManagerRoster extends Model
{
    use HasFactory;

    protected $table = 'manager_roster';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user',
        'team',
        'role',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $roster): void {
            if (empty($roster->id)) {
                $roster->id = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role');
    }
}
