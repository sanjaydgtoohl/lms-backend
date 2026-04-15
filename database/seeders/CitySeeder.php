<?php

namespace Database\Seeders;

class CitySeeder extends GeoLocationTableSeeder
{
    protected function table(): string
    {
        return 'cities';
    }
}
