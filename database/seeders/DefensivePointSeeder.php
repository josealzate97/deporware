<?php

namespace Database\Seeders;

use App\Models\DefensivePoint;
use Illuminate\Database\Seeder;

class DefensivePointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $points = [
            'Anticipacion',
            'Ayudas permanentes',
            'Balon parado',
            'Coberturas',
            'Despeje',
            'Entrada',
            'Interceptacion',
            'Juego aereo',
            'Marcaje',
            'Permutas',
            'Presion',
            'Repliegue',
            'Temporizaciones',
            'Vigilancias',
        ];

        foreach ($points as $point) {
            DefensivePoint::updateOrCreate([
                'name' => $point,
            ], [
                'status' => DefensivePoint::ACTIVE,
            ]);
        }
    }
}
