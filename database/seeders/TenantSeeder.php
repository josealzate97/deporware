<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Orquestador principal del seeding por tenant.
 *
 * Crea el tenant y delega en cada seeder pasándole el objeto Tenant.
 * Todos los seeders dependientes reciben $tenant como argumento en run().
 *
 * Uso en fresh install:  php artisan db:seed  (vía DatabaseSeeder)
 * Uso para nuevo tenant: php artisan db:seed --class=TenantSeeder
 */
class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'mi_escuela_001'],
            [
                'id'     => (string) Str::uuid(),
                'number' => 1,
                'name'   => 'Mi Escuela',
                'slug'   => 'mi_escuela_001',
                'status' => Tenant::ACTIVE,
            ]
        );

        $this->command->info("Tenant: {$tenant->name} ({$tenant->id})");

        // Registrar el tenant en el container para que el trait BelongsToTenant
        // lo inyecte automáticamente al crear registros desde los seeders
        app()->instance('current_tenant', $tenant);

        $this->call([
            ConfigurationSeeder::class,
            SportsVenueSeeder::class,
            UserSeeder::class,
            AttackPointSeeder::class,
            DefensivePointSeeder::class,
        ]);
    }
}
