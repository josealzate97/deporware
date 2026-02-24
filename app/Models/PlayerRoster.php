<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PlayerRoster extends Model
{
    use HasFactory;

    protected $table = 'player_roster';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'player',
        'team',
        'position',
        'dorsal',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'dorsal' => 'integer',
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

    public function player()
    {
        return $this->belongsTo(Player::class, 'player');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team');
    }
}
