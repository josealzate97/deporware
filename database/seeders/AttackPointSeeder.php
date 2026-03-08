<?php

namespace Database\Seeders;

use App\Models\AttackPoint;
use Illuminate\Database\Seeder;

class AttackPointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $points = [
            'Apoyos',
            'Ataque organizado',
            'Balon parado',
            'Cambio de orientacion',
            'Cambio de ritmo',
            'Conduccion',
            'Control del juego',
            'Contraataques',
            'Control orientado',
            'Desdoblamientos',
            'Desmarques',
            'Efectividad de cara a gol',
            'Espacios libres',
            'Finalizacion',
            'Golpeo (corto y largo)',
            'Paredes',
            'Pase',
            'Perfiles',
            'Progresion de juego',
            'Regate',
            'Ritmo de juego',
            'Tiro',
            'Velocidad de juego',
        ];

        foreach ($points as $point) {
            AttackPoint::updateOrCreate([
                'name' => $point,
            ], [
                'status' => AttackPoint::ACTIVE,
            ]);
        }
    }
}
