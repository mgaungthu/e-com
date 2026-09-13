<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Format;
use Intervention\Image\Laravel\Facades\Image;

class HomeBannerImageService
{
    /*
    |--------------------------------------------------------------------------
    | Banner Dimensions
    |--------------------------------------------------------------------------
    |
    | Mobile carousel target ratio:
    |
    | 1200 / 660 ≈ 1.82
    |
    | This closely matches the current React Native hero banner.
    |
    */

    private const WIDTH = 1200;

    private const HEIGHT = 660;

    private const QUALITY = 85;

    /*
    |--------------------------------------------------------------------------
    | Store Banner
    |--------------------------------------------------------------------------
    */

    public function store(
        UploadedFile $file
    ): string {
        $filename = Str::uuid().'.webp';

        $path = 'home-banners/'.$filename;

        /*
        |--------------------------------------------------------------------------
        | Decode
        |--------------------------------------------------------------------------
        */

        $image = Image::decode(
            $file
        );

        /*
        |--------------------------------------------------------------------------
        | Resize + Center Crop
        |--------------------------------------------------------------------------
        |
        | The complete uploaded image represents the banner.
        |
        | Therefore every stored banner should use the same aspect ratio so the
        | mobile app can render it consistently without different slide heights.
        |
        | No white padding is added.
        |
        */

        $image->cover(
            self::WIDTH,
            self::HEIGHT,
        );

        /*
        |--------------------------------------------------------------------------
        | Encode WebP
        |--------------------------------------------------------------------------
        */

        $encoded = $image->encodeUsingFormat(
            Format::WEBP,
            quality: self::QUALITY,
        );

        /*
        |--------------------------------------------------------------------------
        | Store
        |--------------------------------------------------------------------------
        */

        Storage::disk('public')->put(
            $path,
            (string) $encoded,
        );

        return $path;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        ?string $path
    ): void {
        if (! $path) {
            return;
        }

        Storage::disk('public')->delete(
            $path
        );
    }
}