<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TrainingAttendance extends Model
{
    use HasFactory;

    protected $table = 'training_attendance';

    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'training',
        'player',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $attendance): void {
            if (empty($attendance->id)) {
                $attendance->id = (string) Str::uuid();
            }
        });
    }

    public function training()
    {
        return $this->belongsTo(Training::class, 'training');
    }

    public function player()
    {
        return $this->belongsTo(Player::class, 'player');
    }
}
