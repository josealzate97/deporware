<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Entrenador Principal', 'code' => '001'],
            ['name' => 'Entrenador/a Asistente', 'code' => '002'],
            ['name' => 'Preparador Fisico', 'code' => '003'],
            ['name' => 'Fisioterapeuta', 'code' => '004'],
            ['name' => 'Entrenador de Porteros', 'code' => '005'],
            ['name' => 'Nutricionista', 'code' => '006'],
            ['name' => 'Analista', 'code' => '007'],
            ['name' => 'Delegado', 'code' => '008'],
        ];

        foreach ($roles as $data) {
            $role = Role::query()->firstOrCreate(
                ['code' => $data['code']],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $data['name'],
                    'status' => true,
                ]
            );

            if ($role->name !== $data['name'] || $role->status !== true) {
                $role->update([
                    'name' => $data['name'],
                    'status' => true,
                ]);
            }
        }
    }
}
