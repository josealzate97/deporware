<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ConfigurationSeeder::class,
            SportsVenueSeeder::class,
            UserSeeder::class,
            AttackPointSeeder::class,
            DefensivePointSeeder::class,
        ]);
    }
}
