<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {

        DB::table('users')->insert([
            [
                'id' => (string) Str::uuid(),
                'name' => 'Super',
                'email' => 'root@deporware.com',
                'username' => 'superadmin',
                'password' => Hash::make('root@123'),
                'phone' => "100-000-000",
                'role' => User::ROLE_ROOT,
                'hired_date' => now(),
                'status' => User::ACTIVE,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Gerente Deportivo',
                'email' => 'gerente@deporware.com',
                'username' => 'gerente',
                'password' => Hash::make('gerente@123'),
                'phone' => "200-000-000",
                'role' => User::ROLE_ADMIN,
                'hired_date' => now(),
                'status' => User::ACTIVE,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Entrenador',
                'email' => 'staff@deporware.com',
                'username' => 'staff',
                'password' => Hash::make('staff@123'),
                'phone' => "300-000-000",
                'role' => User::ROLE_STAFF,
                'hired_date' => now(),
                'status' => User::ACTIVE,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
