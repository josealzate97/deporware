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

    public static function tacticObjectivesOptions(): array
    {

        return [
            1 => 'Anticipacion',
            2 => 'Apoyos',
            3 => 'Ataque',
            4 => 'Ayuda Permanente',
            5 => 'Cambio de orientacion',
            6 => 'Cambio de ritmo',
            7 => 'Coberturas',
            8 => 'Control del juego',
            9 => 'Contraataque',
            10 => 'Desdoblamientos',
            11 => 'Desmarques',
            12 => 'Espacios Libres',
            13 => 'Marcaje',
            14 => 'Paredes',
            15 => 'Permutas',
            16 => 'Presion',
            17 => 'Progresion del juego',
            18 => 'Repliegue',
            19 => 'Ritmo de juego',
            20 => 'Temporizaciones',
            21 => 'Velocidad de juego',
            22 => 'Vigilancias',

        ];
    }

    public static function psychoObjectivesOptions(): array
    {

        return [
            1 => 'Cohesion',
            2 => 'Compañerismo',
            3 => 'Comunicacion',
            4 => 'Concentracion',
            5 => 'Confianza',
            6 => 'Deportividad',
            7 => 'Disciplina',
            8 => 'Gestion del estres',
            9 => 'Gestion emocional',
            10 => 'Respeto',
            11 => 'Tolerancia a la frustracion',
        ];
    }

    public static function tecnichObjectivesOptions(): array
    {

        return [
            1 => 'Conduccion',
            2 => 'Control / Control Orientado',
            3 => 'Despeje',
            4 => 'Entrada',
            5 => 'Golpeo',
            6 => 'Interceptacion',
            7 => 'Juego Aereo',
            8 => 'Pases',
            9 => 'Perfiles',
            10 => 'Regate',
            11 => 'Tiros',
        ];
    }

    public static function fisicObjectivesOptions(): array
    {

        return [
            1 => 'Coodinación',
            2 => 'Fuerza',
            3 => 'Resistencia',
            4 => 'Velocidad',
        ];
    }

    public static function momentOptions(): array
    {
        return [
            1 => 'Ataque',
            2 => 'Defensa',
            3 => 'Transicion Defensa-Ataque',
            4 => 'Transicion Ataque-Defensa',
        ];
    }
}

