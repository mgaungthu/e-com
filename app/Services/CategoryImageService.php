<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Format;
use Intervention\Image\Laravel\Facades\Image;
use RuntimeException;

class CategoryImageService
{
    private const WIDTH = 800;

    private const HEIGHT = 800;

    private const QUALITY = 82;

    public function store(UploadedFile $file): string
    {
        $filename = Str::uuid().'.webp';

        $path = 'categories/'.$filename;

        $image = Image::decode($file);

        $image->cover(
            self::WIDTH,
            self::HEIGHT,
        );

        $encoded = $image->encodeUsingFormat(
            Format::WEBP,
            quality: self::QUALITY,
        );

        $stored = Storage::disk('public')->put(
            $path,
            (string) $encoded,
        );

        if (! $stored) {
            throw new RuntimeException(
                'Failed to store the category image.'
            );
        }

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