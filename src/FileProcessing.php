<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond;

use function base64_decode;
use function fclose;
use function fopen;
use function function_exists;
use function fwrite;
use function getimagesizefromstring;
use function imagealphablending;
use function imagecolorallocate;
use function imagecolorallocatealpha;
use function imagecopy;
use function imagecreatefromstring;
use function imagecreatetruecolor;
use function imagedestroy;
use function imagefilledrectangle;
use function imagejpeg;
use function imagepng;
use function imagesavealpha;
use function imagesx;
use function imagesy;
use function imagewebp;
use function is_numeric;
use function is_object;
use function is_string;
use function json_decode;
use function max;
use function min;
use function ob_get_clean;
use function ob_start;
use function pathinfo;
use function preg_replace;
use function round;
use function strtolower;

final class FileProcessing
{
    public static function save(array $files, string $path): void
    {
        self::processFiles($files, $path);
    }

    public static function saveWithReturningfile(
        array $files,
        string $path,
        string|null $newFileName = null,
        bool $withPath = true,
    ): string {
        $savedFiles = self::processFiles($files, $path, $newFileName, $withPath);

        return $savedFiles[0] ?? '';
    }

    public static function saveWithReturningFiles(array $files, string $path, bool $withPath = true): array
    {
        return self::processFiles($files, $path, withPath: $withPath);
    }

    /**
     * Saves files applying the crop rectangle stored in `metadata.crop.rect` on the server.
     *
     * Use this when the client uploads the original image (image transform disabled) and the actual
     * crop must be performed server-side, for example with large product photos. Requires the GD
     * extension; without it, the original bytes are stored unchanged.
     */
    public static function saveCropped(array $files, string $path): void
    {
        self::processFiles($files, $path, crop: true);
    }

    public static function saveCroppedWithReturningFile(
        array $files,
        string $path,
        string|null $newFileName = null,
        bool $withPath = true,
    ): string {
        $savedFiles = self::processFiles($files, $path, $newFileName, $withPath, true);

        return $savedFiles[0] ?? '';
    }

    public static function saveCroppedWithReturningFiles(array $files, string $path, bool $withPath = true): array
    {
        return self::processFiles($files, $path, withPath: $withPath, crop: true);
    }

    private static function processFiles(
        array $files,
        string $path,
        string|null $newFileName = null,
        bool $withPath = true,
        bool $crop = false
    ): array {
        $savedFiles = [];

        foreach ($files as $file) {
            if (is_string($file) && $file !== '') {
                $file = json_decode($file, false, flags: JSON_THROW_ON_ERROR);
            }

            if (is_object($file) && is_string($file->data) && is_string($file->name)) {
                $mimeType = self::getOutputMimeType($file);
                $data = base64_decode($file->data);

                if ($crop) {
                    $data = self::applyServerCrop($data, $file, $mimeType);
                }

                $filename = self::sanitizeFilename($file->name, $newFileName, $mimeType);

                $result = self::writeFile($path, $data, $filename);

                if ($result) {
                    $savedFiles[] = $withPath ? $path . $filename : $filename;
                }
            }
        }

        return $savedFiles;
    }

    /**
     * Applies the crop rectangle carried in `metadata.crop.rect` to the raw image bytes.
     *
     * Returns the original bytes unchanged when GD is unavailable, no rectangle is present, or the
     * source image cannot be decoded. Rotation/flip metadata is not applied (the editor never emits it).
     */
    private static function applyServerCrop(string $data, object $file, string|null $mimeType): string
    {
        if (!function_exists('imagecreatefromstring')) {
            return $data;
        }

        $rect = self::getCropRect($file);

        if ($rect === null) {
            return $data;
        }

        $cropped = self::cropImageData($data, $rect, $mimeType ?? self::detectMimeType($data));

        return $cropped ?? $data;
    }

    /**
     * @return array{x: int, y: int, width: int, height: int}|null
     */
    private static function getCropRect(object $file): array|null
    {
        if (
            !isset($file->metadata) ||
            !is_object($file->metadata) ||
            !isset($file->metadata->crop) ||
            !is_object($file->metadata->crop) ||
            !isset($file->metadata->crop->rect) ||
            !is_object($file->metadata->crop->rect)
        ) {
            return null;
        }

        $rect = $file->metadata->crop->rect;

        $width = isset($rect->width) && is_numeric($rect->width) ? (int) round((float) $rect->width) : 0;
        $height = isset($rect->height) && is_numeric($rect->height) ? (int) round((float) $rect->height) : 0;

        if ($width <= 0 || $height <= 0) {
            return null;
        }

        return [
            'x' => isset($rect->x) && is_numeric($rect->x) ? (int) round((float) $rect->x) : 0,
            'y' => isset($rect->y) && is_numeric($rect->y) ? (int) round((float) $rect->y) : 0,
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * @param array{x: int, y: int, width: int, height: int} $rect
     */
    private static function cropImageData(string $data, array $rect, string|null $mimeType): string|null
    {
        $source = @imagecreatefromstring($data);

        if ($source === false) {
            return null;
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $cropWidth = $rect['width'];
        $cropHeight = $rect['height'];
        $isJpeg = strtolower((string) $mimeType) === 'image/jpeg';

        $destination = imagecreatetruecolor($cropWidth, $cropHeight);

        if ($isJpeg) {
            $background = imagecolorallocate($destination, 255, 255, 255);
            imagefilledrectangle($destination, 0, 0, $cropWidth, $cropHeight, $background);
        } else {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 0, 0, 0, 127);
            imagefilledrectangle($destination, 0, 0, $cropWidth, $cropHeight, $transparent);
        }

        // Intersect the requested rectangle with the real image bounds so selections that extend past
        // the edges are padded (transparent or white) instead of producing a GD error.
        $startX = max(0, $rect['x']);
        $startY = max(0, $rect['y']);
        $endX = min($sourceWidth, $rect['x'] + $cropWidth);
        $endY = min($sourceHeight, $rect['y'] + $cropHeight);
        $copyWidth = $endX - $startX;
        $copyHeight = $endY - $startY;

        if ($copyWidth > 0 && $copyHeight > 0) {
            imagecopy(
                $destination,
                $source,
                $startX - $rect['x'],
                $startY - $rect['y'],
                $startX,
                $startY,
                $copyWidth,
                $copyHeight,
            );
        }

        $output = self::encodeImage($destination, $mimeType);

        imagedestroy($source);
        imagedestroy($destination);

        return $output;
    }

    private static function encodeImage(\GdImage $image, string|null $mimeType): string|null
    {
        ob_start();

        $encoded = match (strtolower((string) $mimeType)) {
            'image/jpeg' => imagejpeg($image, null, 92),
            'image/webp' => function_exists('imagewebp') && imagewebp($image, null, 92),
            default => imagepng($image),
        };

        $output = ob_get_clean();

        return $encoded && is_string($output) && $output !== '' ? $output : null;
    }

    private static function detectMimeType(string $data): string|null
    {
        $info = @getimagesizefromstring($data);

        return is_array($info) ? $info['mime'] : null;
    }

    private static function getOutputMimeType(object $file): string|null
    {
        if (
            isset($file->metadata) &&
            is_object($file->metadata) &&
            isset($file->metadata->output) &&
            is_object($file->metadata->output) &&
            isset($file->metadata->output->type) &&
            is_string($file->metadata->output->type)
        ) {
            return $file->metadata->output->type;
        }

        return null;
    }

    private static function sanitizeFilename(string $filename, $newFileName, string|null $outputMimeType = null): string
    {
        $info = pathinfo($filename);

        $name = ($newFileName !== null)
            ? self::sanitizeFilenamePart($newFileName)
            : self::sanitizeFilenamePart($info['filename']);

        $extension = self::extensionFromMimeType($outputMimeType)
            ?? (isset($info['extension']) ? self::sanitizeFilenamePart($info['extension']) : '');

        return ($name !== '' ? $name : '_') . '.' . $extension;
    }

    private static function extensionFromMimeType(string|null $mimeType): string|null
    {
        if ($mimeType === null) {
            return null;
        }

        return match (strtolower($mimeType)) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null,
        };
    }

    private static function sanitizeFilenamePart(string $str): string
    {
        return preg_replace("/[^a-zA-Z0-9\s]/", '', $str);
    }

    private static function writeFile(string $path, string $data, string $filename): bool
    {
        $handle = fopen($path . DIRECTORY_SEPARATOR . $filename, 'wb');
        $result = fwrite($handle, $data);

        fclose($handle);

        return $result !== false;
    }
}
