<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stations = [
            ['name' => 'Maadi Station', 'code' => 'MAA'],
            ['name' => 'Nasr City Station', 'code' => 'NASR'],
            ['name' => 'Dokki Station', 'code' => 'DOK'],
            ['name' => 'Zamalek Station', 'code' => 'ZAM'],
            ['name' => 'New Cairo Station', 'code' => 'NCAI'],
        ];

        foreach ($stations as $station) {
            Station::firstOrCreate(
                ['code' => $station['code']],
                ['name' => $station['name']]
            );
        }
    }
}
