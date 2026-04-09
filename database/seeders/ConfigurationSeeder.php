<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Configuration;

class ConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = app()->bound('current_tenant') ? app('current_tenant')->id : null;

        DB::table('configurations')->insert([
            'id'         => (string) Str::uuid(),
            'tenant_id'  => $tenantId,
            'name'       => 'Academia Deportiva Futbol Center',
            'legal_name' => 'Academia Deportiva Futbol Center',
            'legal_id'   => '000.000.000-0',
            'country'    => Configuration::COUNTRY_CO,
            'city'       => 'Armenia',
            'address'    => 'Calle 00 #00-00, Barrio Ejemplo',
            'phone'      => '000-000-000',
            'email'      => 'contacto@example.com',
            'website'    => 'https://www.example.com',
            'logo'       => null,
            'currency'   => Configuration::CURRENCY_COP,
            'timezone'   => Configuration::TIMEZONE_BOGOTA,
            'locale'     => Configuration::LOCALE_ES_CO,
            'sport'      => Configuration::SPORT_FOOTBALL,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
