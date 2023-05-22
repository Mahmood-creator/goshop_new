<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Throwable;

class CitySeederSecond extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $filePath2 = storage_path('app/public/locations/cities2.sql');
            if (file_exists($filePath2)) {
                DB::unprepared(file_get_contents($filePath2));
            }
        } catch (Throwable $e) {
            dd($e);
        }
    }
}
