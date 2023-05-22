<?php

namespace Database\Seeders;

use Throwable;
use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        try {
            $filePath1 = storage_path('app/public/locations/cities.sql');
            if (file_exists($filePath1)) {
                City::truncate();
                DB::unprepared(file_get_contents($filePath1));
            }
        } catch (Throwable $e) {
            dd($e);
        }
    }
}
