<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RivalTeam extends Model
{
    use HasFactory;

    protected $table = 'rival_teams';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $rival): void {
            if (empty($rival->id)) {
                $rival->id = (string) Str::uuid();
            }
        });
    }

    public function matches()
    {
        return $this->hasMany(MatchModel::class, 'rival');
    }
}
