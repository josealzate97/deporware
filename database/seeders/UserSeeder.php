<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $rolePrimary = Role::query()->where('code', '001')->value('id');
        $roleSecondary = Role::query()->where('code', '002')->value('id');
        $roleTertiary = Role::query()->where('code', '003')->value('id');

        $fallbackRole = Role::query()->orderBy('code')->value('id');
        $rolePrimary = $rolePrimary ?: $fallbackRole;
        $roleSecondary = $roleSecondary ?: $fallbackRole;
        $roleTertiary = $roleTertiary ?: $fallbackRole;

        DB::table('users')->insert([
            [
                'id' => (string) Str::uuid(),
                'name' => 'Super',
                'email' => 'superadmin@deporware.com',
                'username' => 'superadmin',
                'password' => Hash::make('superadmin@123'),
                'phone' => "100-000-000",
                'role' => $rolePrimary,
                'specialty' => null,
                'hired_date' => now(),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Admin',
                'email' => 'admin@deporware.com',
                'username' => 'admin',
                'password' => Hash::make('admin@123'),
                'phone' => "200-000-000",
                'role' => $roleSecondary,
                'specialty' => null,
                'hired_date' => now(),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Cajero',
                'email' => 'caja@deporware.com',
                'username' => 'caja',
                'password' => Hash::make('caja@123'),
                'phone' => "300-000-000",
                'role' => $roleTertiary,
                'specialty' => null,
                'hired_date' => now(),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
