<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\CountryTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Throwable;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $filePath = storage_path('app/public/locations/countries.sql');

            if (file_exists($filePath)) {
                DB::table('countries')->delete();
                DB::table('country_translations')->delete();
                DB::unprepared(file_get_contents($filePath));
            }
        } catch (Throwable $e) {
            dd($e);
        }
    }
}
