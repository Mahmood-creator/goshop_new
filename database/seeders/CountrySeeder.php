<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\CountryTranslation;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countryJson = file_get_contents(storage_path('app/public/export/countries.json'));

        $countryFiles = json_decode($countryJson);

        foreach ($countryFiles as $countryFile) {

            $country = Country::create([
                'id' => $countryFile->id,
                'name' => $countryFile->name
            ]);

            $countries = collect($countryFile->translations)->toArray();

            foreach ($countries as $key => $value){
                CountryTranslation::create([
                    'country_id' => $country->id,
                    'locale' => $key,
                    'title' => $value
                ]);
            }

        }
    }
}
