<?php

namespace Database\Seeders;

class CountrySeeder extends GeoLocationTableSeeder
{
    protected function table(): string
    {
        return 'countries';
    }
}
