<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AttackPoint extends Model
{
    use BelongsToTenant, HasFactory;

    public const ACTIVE = 1;
    public const INACTIVE = 0;

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
        static::creating(function (self $attackPoint): void {
            if (empty($attackPoint->id)) {
                $attackPoint->id = (string) Str::uuid();
            }

            if ($attackPoint->status === null) {
                $attackPoint->status = self::ACTIVE;
            }
        });
    }
}
