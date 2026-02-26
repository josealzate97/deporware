<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Training extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'category',
        'team',
        'venue',
        'location',
        'duration',
        'notes',
        'principal_obj',
        'tactic_obj',
        'fisic_obj',
        'tecnic_obj',
        'pyscho_obj',
        'moment',
        'document',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'duration' => 'integer',
            'principal_obj' => 'integer',
            'tactic_obj' => 'integer',
            'fisic_obj' => 'integer',
            'tecnic_obj' => 'integer',
            'pyscho_obj' => 'integer',
            'moment' => 'integer',
            'status' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $training): void {
            if (empty($training->id)) {
                $training->id = (string) Str::uuid();
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team');
    }

    public function attendance()
    {
        return $this->hasMany(TrainingAttendance::class, 'training');
    }

    // Sede donde se realiza el entrenamiento (si aplica)
    public function venue()
    {
        return $this->belongsTo(SportsVenue::class, 'venue');
    }
}
