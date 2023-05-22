<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Throwable;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $filePath = storage_path('app/public/locations/regions.sql');
            if (file_exists($filePath)) {
                Region::truncate();
                DB::unprepared(file_get_contents($filePath));
            }
        } catch (Throwable $e) {
        }
    }
}
