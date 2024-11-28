<?php

namespace App\Backend\Component\Image;

use Intervention\Image\ImageManager;

class Image
{
    public static function resize(
        string $originalFile,
        string $mimeType,
        int $width,
        int $height,
        ?string $destinationFile = null
    ): string {
        if (!in_array($mimeType, ["image/jpeg", "image/jpg", "image/png", "image/gif"])) {
            throw new \InvalidArgumentException("Mime type {$mimeType} is not supported, only 'image/jpeg', 'image/jpg', 'image/png', 'image/gif' are allowed");
        }

        $image = ImageManager::imagick()->read($originalFile);
        $image->coverDown($width, $height);

        if (!$destinationFile) {
            $destinationFileName = self::generateThumbnailFileName($originalFile, $width, $height);
            $dirName = pathinfo($originalFile, PATHINFO_DIRNAME);
            $destinationFile = "{$dirName}/{$destinationFileName}";
        } else {
            $destinationFileName = pathinfo($destinationFile, PATHINFO_FILENAME);
        }

        $image->save($destinationFile);

        return $destinationFileName;
    }


    public static function generateThumbnailFileName(string $originalFile, int $width, int $height, ?string $extension = null): string
    {
        $fileName = pathinfo($originalFile, PATHINFO_FILENAME);
        $extension = $extension ?? pathinfo($originalFile, PATHINFO_EXTENSION);

        return "{$fileName}_{$width}x{$height}.{$extension}";
    }
}
