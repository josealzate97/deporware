<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PlayerContact extends Model
{
    use HasFactory;

    public const ACTIVE = 1;
    public const INACTIVE = 0;

    protected $table = 'players_contacts';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'lastname',
        'email',
        'phone',
        'address',
        'city',
        'player',
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
        static::creating(function (self $contact): void {
            if (empty($contact->id)) {
                $contact->id = (string) Str::uuid();
            }
        });
    }

    public function player()
    {
        return $this->belongsTo(Player::class, 'player');
    }
}
