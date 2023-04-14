<?php


namespace App\Helpers;



use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class FileHelper
{
    /* Upload file function */
    public static function uploadFile($file, $path, $wmax = null, $hmax = null): array
    {
        try {

            $pathDisc = Storage::disk('do')->put('public/images/' . $path, $file,'public');

            $filePath = substr(strrchr($pathDisc, "/"), 1);

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $path.'/'.$filePath];
        } catch (\Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    /* Download file function */
    public static function downloadFile($path, $name){
        $path = Storage::disk('public')->path($path);
        return response()->download($path, $name);
    }

    /* Delete file function */
    public static function deleteFile($path){
        return Storage::disk('public')->delete('images/' . $path);
    }

    /* Обрезка картинки под стандарты системы */
    public static function resize($target, $dest, $wmax, $hmax, $ext){
        list($w_orig, $h_orig) = getimagesize($target);
        $ratio = $w_orig / $h_orig;

        if (($wmax / $hmax) > $ratio){
            $wmax = $hmax * $ratio;
        } else {
            $hmax = $wmax / $ratio;
        }
        $img = "";
        switch ($ext){
            case ("gif"):
                $img = imagecreatefromgif($target);
                break;
            case ("png"):
                $img = imagecreatefrompng($target);
                break;
            default:
                $img = imagecreatefromjpeg($target);
        }

        $newImg = imagecreatetruecolor($wmax, $hmax);
        if ($ext == "png"){
            imagesavealpha($newImg, true);
            $transPng = imagecolorallocatealpha($newImg, 0,0,0,127);
            imagefill($newImg, 0,0, $transPng);
        }
        imagecopyresampled($newImg, $img, 0,0,0,0, $wmax, $hmax, $w_orig, $h_orig);
        switch ($ext){
            case("gif"):
                imagegif($newImg, $dest);
                break;
            case("png"):
                imagepng($newImg, $dest);
                break;
            default:
                imagejpeg($newImg,$dest);
        }
        imagedestroy($newImg);
    }

}
