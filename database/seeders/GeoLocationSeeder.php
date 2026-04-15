<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Backward-compatible wrapper that imports all geography tables.
 * New code should prefer the table-specific seeders.
 */
class GeoLocationSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RegionSeeder::class,
            SubregionSeeder::class,
            CountrySeeder::class,
            StateSeeder::class,
            CitySeeder::class,
        ]);
    }
}
