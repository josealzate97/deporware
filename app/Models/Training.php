<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Training extends Model
{
    use HasFactory;

    public const ACTIVE = 1;
    public const INACTIVE = 0;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
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

        static::created(function (self $training): void {

            $root = storage_path('app/public');

            if (!is_dir($root) || !is_writable($root)) {

                Log::error('Storage path is not writable for trainings.', [
                    'path' => $root,
                ]);

                return;

            }

            if (empty($training->team)) {
                return;
            }

            $disk = Storage::disk('public');
            $basePath = "teams/{$training->team}/trainings/{$training->id}";

            if (!$disk->exists($basePath) && !$disk->makeDirectory($basePath)) {

                Log::error('Failed to create training folder.', [
                    'training' => $training->id,
                    'team' => $training->team,
                    'path' => $basePath,
                ]);

                return;

            }

            foreach (['reports', 'photos'] as $folder) {

                $fullPath = "{$basePath}/{$folder}";

                if (!$disk->exists($fullPath) && !$disk->makeDirectory($fullPath)) {

                    Log::error('Failed to create training subfolder.', [
                        'path' => $fullPath,
                    ]);

                }

            }
            
        });
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

    public static function statusOptions(): array
    {
        return [
            self::ACTIVE => 'Activo',
            self::INACTIVE => 'Inactivo',
        ];
    }
}
