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

    public const MOTHER = 1;
    public const FATHER = 2;
    public const SIBLING = 3;
    public const UNCLE_AUNT = 4;
    public const COUSIN = 5;

    protected $table = 'players_contacts';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'lastname',
        'relationship',
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
            'relationship' => 'integer',
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

    public static function relationshipOptions(): array
    {
        return [
            self::MOTHER => 'Madre',
            self::FATHER => 'Padre',
            self::SIBLING => 'Hermano/a',
            self::UNCLE_AUNT => 'Tío/a',
            self::COUSIN => 'Primo/a',
        ];
    }
}
