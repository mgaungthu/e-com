<?php

namespace App\Services\Chat;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ChatImageService
{
    public function store(
        UploadedFile $file,
        int $conversationId,
    ): array {
        /*
        |--------------------------------------------------------------------------
        | Detect Safe Extension
        |--------------------------------------------------------------------------
        |
        | Do not trust the original client filename extension.
        |
        */

        $extension = $this->extensionForMimeType(
            $file->getMimeType()
        );

        if (!$extension) {
            throw new RuntimeException(
                'Unsupported chat image type.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Image Dimensions
        |--------------------------------------------------------------------------
        */

        $realPath = $file->getRealPath();

        if (!$realPath) {
            throw new RuntimeException(
                'Unable to read uploaded image.'
            );
        }

        $imageInfo = @getimagesize(
            $realPath
        );

        if ($imageInfo === false) {
            throw new RuntimeException(
                'Uploaded file is not a valid image.'
            );
        }

        [$width, $height] = $imageInfo;

        /*
        |--------------------------------------------------------------------------
        | Filename
        |--------------------------------------------------------------------------
        */

        $filename =
            Str::uuid()->toString()
            . '.'
            . $extension;

        $directory =
            "chat/{$conversationId}";

        /*
        |--------------------------------------------------------------------------
        | Store
        |--------------------------------------------------------------------------
        */

        $path = $file->storeAs(
            $directory,
            $filename,
            'public',
        );

        if (!$path) {
            throw new RuntimeException(
                'Unable to store chat image.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Metadata
        |--------------------------------------------------------------------------
        */

        return [
            'image_path' =>
                $path,

            'image_width' =>
                $width,

            'image_height' =>
                $height,

            'image_size' =>
                $file->getSize(),

            'image_mime_type' =>
                $file->getMimeType(),
        ];
    }

    public function delete(
        ?string $path
    ): void {
        if (
            !$path ||
            trim($path) === ''
        ) {
            return;
        }

        Storage::disk('public')
            ->delete($path);
    }

    private function extensionForMimeType(
        ?string $mimeType
    ): ?string {
        return match ($mimeType) {
            'image/jpeg' =>
                'jpg',

            'image/png' =>
                'png',

            'image/webp' =>
                'webp',

            default =>
                null,
        };
    }
}