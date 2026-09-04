<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Format;
use Intervention\Image\Laravel\Facades\Image;

class ProductImageService
{
    private const WIDTH = 1200;

    private const HEIGHT = 900;

    private const QUALITY = 82;

    public function store(UploadedFile $file): string
    {
        $filename = Str::uuid().'.webp';

        $path = 'products/'.$filename;

        /*
        |--------------------------------------------------------------------------
        | Decode uploaded image
        |--------------------------------------------------------------------------
        */

        $image = Image::decode($file);

        /*
        |--------------------------------------------------------------------------
        | Resize + Center Crop
        |--------------------------------------------------------------------------
        |
        | Final output will always be:
        |
        | 1200 x 900
        |
        | The original aspect ratio is preserved.
        | Any overflowing area is cropped from the edges.
        | No white padding is added.
        |
        */

        $image->cover(
            self::WIDTH,
            self::HEIGHT,
        );

        /*
        |--------------------------------------------------------------------------
        | Encode to WebP
        |--------------------------------------------------------------------------
        */

        $encoded = $image->encodeUsingFormat(
            Format::WEBP,
            quality: self::QUALITY,
        );

        /*
        |--------------------------------------------------------------------------
        | Store processed image
        |--------------------------------------------------------------------------
        */

        Storage::disk('public')->put(
            $path,
            (string) $encoded,
        );

        return $path;
    }

    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}