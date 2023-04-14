<?php

namespace App\Services\GalleryService;

use App\Helpers\ResponseError;
use App\Models\Gallery;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileStorageService extends \App\Services\CoreService
{

    protected function getModelClass()
    {
        return Gallery::class;
    }

    public function getStorageFiles($type, $length = null, $start = null): \Illuminate\Support\Collection
    {
        $path = Storage::disk('public')->path('images/'. $type);
        $collection = File::allFiles($path);

        return collect($collection)->map(function ($q) {
            $title = Str::of($q)->after('images/');
            $gallery = Gallery::where('path', $title)->first();
            return [
                'file' => $title,
                'isset' => (bool) $gallery,
                'type' => $gallery->type ?? null,
                'path' => '/storage/images/' . $title,
            ];
        })->skip($start)->take($length);
    }

    public function deleteFileFromStorage($file): array
    {
        $path = 'images/' . $file;
        $file = Storage::disk('public')->exists($path);
        if($file){
            Storage::disk('public')->delete($path);
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => []];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }
}
