<?php

namespace Database\Seeders;

use App\Models\Subscription;
use App\Models\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Throwable;

class TranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $filePath = resource_path('lang/translations.sql');
            if (file_exists($filePath)) {
                Translation::truncate();
//                DB::unprepared(file_get_contents($filePath));
            }
        } catch (Throwable $e) {
        }
    }
}
