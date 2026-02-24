<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'year',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'status' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $category): void {
            if (empty($category->id)) {
                $category->id = (string) Str::uuid();
            }
        });
    }

    public function teams()
    {
        return $this->hasMany(Team::class, 'category');
    }

    public function trainings()
    {
        return $this->hasMany(Training::class, 'category');
    }
}
