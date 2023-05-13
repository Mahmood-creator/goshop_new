<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cityJson = file_get_contents(storage_path('app/public/export/cities.json'));

        $cityFiles = json_decode($cityJson);

        foreach ($cityFiles as $cityFile) {
            City::create([
                'id' => $cityFile->id,
                'name' => $cityFile->name,
                'region_id' => $cityFile->state_id
            ]);
        }
    }
}
