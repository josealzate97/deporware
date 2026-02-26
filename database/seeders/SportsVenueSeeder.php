<?php

namespace Database\Seeders;

use App\Models\Configuration;
use App\Models\SportsVenue;
use Illuminate\Database\Seeder;

class SportsVenueSeeder extends Seeder
{
    public function run(): void
    {
        $config = Configuration::first();
        $address = $config?->address ?: 'Sin dirección';

        SportsVenue::create([
            'name' => 'Sede Principal',
            'address' => $address,
            'city' => 'Armenia',
            'status' => true,
        ]);
    }
}
