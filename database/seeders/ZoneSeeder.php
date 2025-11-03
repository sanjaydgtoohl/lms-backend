<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Zone; // Zone model ko import karein
use Illuminate\Support\Str;

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $zones = [
            'North Zone',
            'South Zone',
            'East Zone',
            'West Zone',
            'Central Zone',
        ];

        foreach ($zones as $zoneName) {
            // updateOrCreate() check karega ki zone pehle se hai ya nahi
            // Agar nahi hai, toh create karega
            Zone::updateOrCreate(
                [
                    'slug' => Str::slug($zoneName) // 'slug' ke basis par check karega
                ],
                [
                    'name' => $zoneName,
                    'status' => '1', // Default '1' = active (aapki migration ke hisaab se)
                ]
            );
        }
    }
}