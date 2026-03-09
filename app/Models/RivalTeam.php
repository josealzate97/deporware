<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RivalTeam extends Model
{
    use HasFactory;

    public const ACTIVE = 1;
    public const INACTIVE = 0;

    protected $table = 'rival_teams';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
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
        static::creating(function (self $rival): void {
            if (empty($rival->id)) {
                $rival->id = (string) Str::uuid();
            }

            if ($rival->status === null) {
                $rival->status = self::ACTIVE;
            }
        });
    }

    public function matches()
    {
        return $this->hasMany(MatchModel::class, 'rival');
    }
}
