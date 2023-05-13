<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stateJson = file_get_contents(storage_path('app/public/export/states.json'));

        $stateFiles = json_decode($stateJson);

        foreach ($stateFiles as $stateFile) {
            Region::create([
                'id' => $stateFile->id,
                'name' => $stateFile->name,
                'country_id' => $stateFile->country_id
            ]);
        }
    }
}
