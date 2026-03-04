<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;

class UserVenue extends Pivot
{
    protected $table = 'user_venue';

    public const ACTIVE = 1;
    public const INACTIVE = 0;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user',
        'venue',
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
        static::creating(function (self $pivot): void {
            if (empty($pivot->id)) {
                $pivot->id = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user');
    }

    public function venue()
    {
        return $this->belongsTo(SportsVenue::class, 'venue');
    }
}
