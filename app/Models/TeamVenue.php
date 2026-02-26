<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;

class TeamVenue extends Pivot
{
    protected $table = 'team_venue';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'team',
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

    public function team()
    {
        return $this->belongsTo(Team::class, 'team');
    }

    public function venue()
    {
        return $this->belongsTo(SportsVenue::class, 'venue');
    }
}
