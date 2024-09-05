<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Image;

class ImageService
{
    /**
     * @param $image
     * @param $path
     * @param $name
     * @return string
     */
    public function resizeAndSave($image, $path, $name): string
    {
        $resizedImage = Image::make($image)->fit(70, 70, function ($constraint) {
            $constraint->upsize();
        })->encode('jpg', 70);

        $filePath = $path . $name;
        Storage::put($filePath, (string) $resizedImage);

        return $filePath;
    }
}
