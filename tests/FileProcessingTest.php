<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Tests;

use JsonException;
use PHPForge\Support\DirectoryCleaner;
use Yii2\Extensions\FilePond\FileProcessing;

use function json_encode;

final class FileProcessingTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        DirectoryCleaner::clean(__DIR__ . '/Support/runtime/');
    }

    /**
     * @throws JsonException
     */
    public function testSave(): void
    {
        FileProcessing::save(
            [
                0 => json_encode(
                    [
                        'id' => 'opqgdavos',
                        'name' => 'test.txt',
                        'type' => 'text/plain',
                        'size' => 7,
                        'metadata' => [],
                        'data' => 'VGVzdE1lCg==',
                    ],
                    JSON_THROW_ON_ERROR,
                ),
            ],
            __DIR__ . '/Support/runtime/',
        );

        $this->assertFileExists(__DIR__ . '/Support/runtime/test.txt');
    }

    /**
     * @throws JsonException
     */
    public function testSaveWithEmptyData(): void
    {
        FileProcessing::save(
            [
                0 => json_encode([], JSON_THROW_ON_ERROR),
            ],
            __DIR__ . '/Support/runtime',
        );

        $this->assertFileDoesNotExist(__DIR__ . '/Support/runtime/test.txt');
    }

    /**
     * @throws JsonException
     */
    public function testSaveWithReturningFile(): void
    {
        $files = FileProcessing::saveWithReturningFile(
            [
                0 => json_encode(
                    [
                        'id' => 'opqgdavos',
                        'name' => 'test.txt',
                        'type' => 'text/plain',
                        'size' => 7,
                        'metadata' => [],
                        'data' => 'VGVzdE1lCg==',
                    ],
                    JSON_THROW_ON_ERROR,
                ),
            ],
            __DIR__ . '/Support/runtime/',
            'category',
            false,
        );

        $this->assertFileExists(__DIR__ . '/Support/runtime/category.txt');
        $this->assertSame('category.txt', $files);
    }

    public function testSaveWithReturningFileUsesImageTransformOutputMimeType(): void
    {
        $files = FileProcessing::saveWithReturningFile(
            [
                0 => json_encode(
                    [
                        'id' => 'opqgdavos',
                        'name' => 'logo.jpg',
                        'type' => 'image/jpeg',
                        'size' => 7,
                        'metadata' => [
                            'output' => [
                                'type' => 'image/png',
                            ],
                        ],
                        'data' => 'iVBORw0KGgo=',
                    ],
                    JSON_THROW_ON_ERROR,
                ),
            ],
            __DIR__ . '/Support/runtime/',
            'logo',
            false,
        );

        $this->assertFileExists(__DIR__ . '/Support/runtime/logo.png');
        $this->assertFileDoesNotExist(__DIR__ . '/Support/runtime/logo.jpg');
        $this->assertSame('logo.png', $files);
    }

    /**
     * @throws JsonException
     */
    public function testSaveWithReturningFiles(): void
    {
        $files = FileProcessing::saveWithReturningFiles(
            [
                0 => json_encode(
                    [
                        'id' => 'opqgdavos',
                        'name' => 'test.txt',
                        'type' => 'text/plain',
                        'size' => 7,
                        'metadata' => [],
                        'data' => 'VGVzdE1lCg==',
                    ],
                    JSON_THROW_ON_ERROR,
                ),
                1 => json_encode(
                    [
                        'id' => 'opqgdavo2',
                        'name' => 'test1.txt',
                        'type' => 'text/plain',
                        'size' => 7,
                        'metadata' => [],
                        'data' => 'VGVzdE1lCg==',
                    ],
                    JSON_THROW_ON_ERROR,
                ),
            ],
            __DIR__ . '/Support/runtime/',
        );

        $this->assertFileExists(__DIR__ . '/Support/runtime/test.txt');
        $this->assertFileExists(__DIR__ . '/Support/runtime/test1.txt');
        $this->assertSame(
            [
                __DIR__ . '/Support/runtime/test.txt',
                __DIR__ . '/Support/runtime/test1.txt',
            ],
            $files,
        );
    }

    /**
     * @throws JsonException
     */
    public function testSaveWithReturningFilesEmptyData(): void
    {
        $files = FileProcessing::saveWithReturningFiles(
            [
                0 => json_encode([], JSON_THROW_ON_ERROR),
            ],
            __DIR__ . '/Support/runtime/',
        );

        $this->assertFileDoesNotExist(__DIR__ . '/Support/runtime/test.txt');
        $this->assertSame([], $files);
    }

    public function testSaveWithReturningFilesWithoutPath(): void
    {
        $files = FileProcessing::saveWithReturningFiles(
            [
                0 => json_encode(
                    [
                        'id' => 'opqgdavos',
                        'name' => 'test.txt',
                        'type' => 'text/plain',
                        'size' => 7,
                        'metadata' => [],
                        'data' => 'VGVzdE1lCg==',
                    ],
                    JSON_THROW_ON_ERROR,
                ),
                1 => json_encode(
                    [
                        'id' => 'opqgdavo2',
                        'name' => 'test1.txt',
                        'type' => 'text/plain',
                        'size' => 7,
                        'metadata' => [],
                        'data' => 'VGVzdE1lCg==',
                    ],
                    JSON_THROW_ON_ERROR,
                ),
            ],
            __DIR__ . '/Support/runtime/',
            withPath: false,
        );

        $this->assertFileExists(__DIR__ . '/Support/runtime/test.txt');
        $this->assertFileExists(__DIR__ . '/Support/runtime/test1.txt');
        $this->assertSame(['test.txt', 'test1.txt'], $files);
    }

    /**
     * @throws JsonException
     */
    public function testSaveCroppedAppliesServerRectangle(): void
    {
        if (!function_exists('imagecreatetruecolor')) {
            self::markTestSkipped('GD extension is required.');
        }

        $file = FileProcessing::saveCroppedWithReturningFile(
            [
                0 => json_encode(
                    [
                        'id' => 'cropme01',
                        'name' => 'logo.png',
                        'type' => 'image/png',
                        'size' => 7,
                        'metadata' => [
                            'output' => ['type' => 'image/png'],
                            'crop' => [
                                'rect' => [
                                    'x' => 8,
                                    'y' => 6,
                                    'width' => 20,
                                    'height' => 10,
                                    'naturalWidth' => 40,
                                    'naturalHeight' => 30,
                                ],
                            ],
                        ],
                        'data' => $this->createPngBase64(40, 30),
                    ],
                    JSON_THROW_ON_ERROR,
                ),
            ],
            __DIR__ . '/Support/runtime/',
            'logo',
            false,
        );

        $this->assertSame('logo.png', $file);

        $size = getimagesize(__DIR__ . '/Support/runtime/logo.png');

        $this->assertIsArray($size);
        $this->assertSame(20, $size[0]);
        $this->assertSame(10, $size[1]);
    }

    /**
     * @throws JsonException
     */
    public function testSaveCroppedClampsRectangleToImageBounds(): void
    {
        if (!function_exists('imagecreatetruecolor')) {
            self::markTestSkipped('GD extension is required.');
        }

        $file = FileProcessing::saveCroppedWithReturningFile(
            [
                0 => json_encode(
                    [
                        'id' => 'cropme02',
                        'name' => 'logo.png',
                        'type' => 'image/png',
                        'size' => 7,
                        'metadata' => [
                            'output' => ['type' => 'image/png'],
                            'crop' => [
                                'rect' => [
                                    'x' => 30,
                                    'y' => 20,
                                    'width' => 40,
                                    'height' => 40,
                                ],
                            ],
                        ],
                        'data' => $this->createPngBase64(40, 30),
                    ],
                    JSON_THROW_ON_ERROR,
                ),
            ],
            __DIR__ . '/Support/runtime/',
            'logo',
            false,
        );

        $this->assertSame('logo.png', $file);

        // The output keeps the requested rectangle size even when it overflows the source; the missing
        // area is padded rather than failing.
        $size = getimagesize(__DIR__ . '/Support/runtime/logo.png');

        $this->assertIsArray($size);
        $this->assertSame(40, $size[0]);
        $this->assertSame(40, $size[1]);
    }

    /**
     * @throws JsonException
     */
    public function testSaveCroppedWithoutRectangleStoresOriginal(): void
    {
        if (!function_exists('imagecreatetruecolor')) {
            self::markTestSkipped('GD extension is required.');
        }

        $file = FileProcessing::saveCroppedWithReturningFile(
            [
                0 => json_encode(
                    [
                        'id' => 'cropme03',
                        'name' => 'logo.png',
                        'type' => 'image/png',
                        'size' => 7,
                        'metadata' => ['output' => ['type' => 'image/png']],
                        'data' => $this->createPngBase64(40, 30),
                    ],
                    JSON_THROW_ON_ERROR,
                ),
            ],
            __DIR__ . '/Support/runtime/',
            'logo',
            false,
        );

        $this->assertSame('logo.png', $file);

        $size = getimagesize(__DIR__ . '/Support/runtime/logo.png');

        $this->assertIsArray($size);
        $this->assertSame(40, $size[0]);
        $this->assertSame(30, $size[1]);
    }

    private function createPngBase64(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);

        imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocate($image, 120, 80, 200));

        ob_start();
        imagepng($image);
        $binary = (string) ob_get_clean();
        imagedestroy($image);

        return base64_encode($binary);
    }
}
