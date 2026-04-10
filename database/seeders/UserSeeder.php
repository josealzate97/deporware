<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\SportsVenue;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = app()->bound('current_tenant') ? app('current_tenant')->id : null;

        $users = [
            [
                'id'         => (string) Str::uuid(),
                'tenant_id'  => null,          // ROOT no pertenece a ninguna escuela
                'name'       => 'Super',
                'email'      => 'root@deporware.com',
                'username'   => 'superadmin',
                'password'   => Hash::make('root@123'),
                'phone'      => "100-000-000",
                'role'       => User::ROLE_ROOT,
                'hired_date' => now(),
                'status'     => User::ACTIVE,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => (string) Str::uuid(),
                'tenant_id'  => $tenantId,     // Pertenece a la escuela de ejemplo
                'name'       => 'Gerente Deportivo',
                'email'      => 'gerente@deporware.com',
                'username'   => 'gerente',
                'password'   => Hash::make('gerente@123'),
                'phone'      => "200-000-000",
                'role'       => User::ROLE_SPORT_MANAGER,
                'hired_date' => now(),
                'status'     => User::ACTIVE,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => (string) Str::uuid(),
                'tenant_id'  => $tenantId,     // Pertenece a la escuela de ejemplo
                'name'       => 'Entrenador',
                'email'      => 'staff@deporware.com',
                'username'   => 'staff',
                'password'   => Hash::make('staff@123'),
                'phone'      => "300-000-000",
                'role'       => User::ROLE_COACH,
                'hired_date' => now(),
                'status'     => User::ACTIVE,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);

        $mainVenue = SportsVenue::where('name', 'Sede Principal')->first();

        if ($mainVenue) {
            $pivotRows = [];

            foreach ($users as $user) {
                if (in_array($user['role'], [User::ROLE_ROOT, User::ROLE_SPORT_MANAGER], true)) {
                    continue;
                }

                $pivotRows[] = [
                    'id' => (string) Str::uuid(),
                    'user' => $user['id'],
                    'venue' => $mainVenue->id,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($pivotRows)) {
                DB::table('user_venue')->insert($pivotRows);
            }
        }
    }
}
