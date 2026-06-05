<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Tests;

use Yii2\Extensions\FilePond\Asset;
use Yii2\Extensions\FilePond\FilePond;
use Yii;
use yii\web\View;

final class AssetTest extends TestCase
{
    public function setup(): void
    {
        parent::setUp();
        $this->mockApplication();

        FilePond::$counter = 0;

        $this->view = Yii::$app->getView();
    }

    public function testFilePondAssetSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondAsset::register($this->view);

        $this->assertCount(6, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateTypePlugin::class, $this->view->assetBundles);
    }

    public function testFilePondAssetRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondAsset::register($this->view);

        $this->assertCount(6, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString('/dist/filepond.css', $result);
        $this->assertStringContainsString('/dist/filepond.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-encode.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-exif-orientation.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-size.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-type.js', $result);
    }

    public function testFilePondCdnAssetSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondCdnAsset::register($this->view);

        $this->assertCount(6, $this->view->assetBundles);

        $this->assertArrayHasKey(Asset\FilePondCdnAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateTypePlugin::class, $this->view->assetBundles);
    }

    public function testFilePondCdnAssetRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondCdnAsset::register($this->view);

        $this->assertCount(6, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond@^4/dist/filepond.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond@^4/dist/filepond.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-encode/dist/filepond-plugin-file-encode.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
            HTML,
            $result,
        );
    }

    public function testCropperJsAssetRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\CropperJsAsset::register($this->view);

        $this->assertCount(1, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\CropperJsAsset::class, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString('/dist/cropper.js', $result);
    }

    public function testCropperJsAssetCdnRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\Cdn\CropperJsAsset::register($this->view);

        $this->assertCount(1, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\CropperJsAsset::class, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/cropperjs@2.1.0/dist/cropper.min.js"></script>
            HTML,
            $result,
        );
    }

    public function testFilePondImageCropPluginSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondImageCropPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateTypePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageCropPlugin::class, $this->view->assetBundles);
    }

    public function testFilePondImageCropPluginRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondImageCropPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.css', $result);
        $this->assertStringContainsString('/dist/filepond.css', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-encode.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-exif-orientation.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-size.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-type.js', $result);
        $this->assertStringContainsString('/dist/filepond.js', $result);
        $this->assertStringContainsString('filepond-plugin-image-crop.js', $result);
    }

    public function testFilePondImageCropPluginCdnSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\Cdn\FilePondImageCropPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondCdnAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateTypePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageCropPlugin::class, $this->view->assetBundles);
    }

    public function testFilePondImageCropPluginCdnRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\Cdn\FilePondImageCropPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond@^4/dist/filepond.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-encode/dist/filepond-plugin-file-encode.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond@^4/dist/filepond.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-crop/dist/filepond-plugin-image-crop.min.js"></script>
            HTML,
            $result,
        );
    }

    public function testFilePondImageTransformPluginSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondImageTransformPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateTypePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageTransformPlugin::class, $this->view->assetBundles);
    }

    public function testFilePondImageEditPluginSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondImageEditPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageEditPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateTypePlugin::class, $this->view->assetBundles);
    }

    public function testFilePondImageEditPluginRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondImageEditPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.css', $result);
        $this->assertStringContainsString('/dist/filepond.css', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-encode.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-edit.css', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-edit.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-exif-orientation.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-size.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-type.js', $result);
        $this->assertStringContainsString('/dist/filepond.js', $result);
    }

    public function testFilePondImageEditPluginCdnSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\Cdn\FilePondImageEditPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondCdnAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageEditPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateTypePlugin::class, $this->view->assetBundles);
    }

    public function testFilePondImageEditPluginCdnRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\Cdn\FilePondImageEditPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond-plugin-image-edit@^1.6.3/dist/filepond-plugin-image-edit.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-edit@^1.6.3/dist/filepond-plugin-image-edit.min.js"></script>
            HTML,
            $result,
        );
    }

    public function testFilePondCropperAssetSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondCropperAsset::register($this->view);

        $this->assertCount(9, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\CropperJsAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondCropperAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageEditPlugin::class, $this->view->assetBundles);
    }

    public function testFilePondCropperAssetRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondCropperAsset::register($this->view);

        $this->assertCount(9, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString('/dist/cropper.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-edit.css', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-edit.js', $result);
        $this->assertStringContainsString('/filepond-cropper.css', $result);
        $this->assertStringContainsString('/filepond-cropper.js', $result);
    }

    public function testFilePondCropperCdnAssetRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondCropperCdnAsset::register($this->view);

        $this->assertCount(9, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\CropperJsAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondCdnAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondCropperCdnAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageEditPlugin::class, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/cropperjs@2.1.0/dist/cropper.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString('/filepond-cropper.css', $result);
        $this->assertStringContainsString('/filepond-cropper.js', $result);
    }

    public function testFilePondWidgetAssetCentersCompactDropLabel(): void
    {
        $asset = file_get_contents(dirname(__DIR__) . '/src/Asset/filepond-widget/filepond-widget.css');

        $this->assertIsString($asset);
        $this->assertStringContainsString('[data-style-panel-layout~="compact"] .filepond--drop-label', $asset);
        $this->assertStringContainsString('bottom: 0', $asset);
        $this->assertStringContainsString('height: auto', $asset);
    }

    public function testFilePondCropperAdapterUsesCropperV2CanvasApi(): void
    {
        $adapter = file_get_contents(dirname(__DIR__) . '/src/Asset/filepond-cropper/filepond-cropper.js');

        $this->assertIsString($adapter);
        $loadListenerPosition = strpos($adapter, "image.addEventListener('load', initializeCropper");
        $imageSourcePosition = strpos($adapter, 'image.src = objectUrl');

        $this->assertIsInt($loadListenerPosition);
        $this->assertIsInt($imageSourcePosition);
        $this->assertStringContainsString('$toCanvas', $adapter);
        $this->assertStringContainsString('window.Cropper.default', $adapter);
        $this->assertLessThan($imageSourcePosition, $loadListenerPosition);
        $this->assertStringNotContainsString('getCroppedCanvas', $adapter);
    }

    public function testFilePondCropperAdapterReturnsImageTransformMetadata(): void
    {
        $adapter = file_get_contents(dirname(__DIR__) . '/src/Asset/filepond-cropper/filepond-cropper.js');

        $this->assertIsString($adapter);
        $this->assertStringContainsString('editor.onconfirm({ data: buildCropData(cropper, selection, canvas, image) })', $adapter);
        $this->assertStringContainsString('crop:', $adapter);
        $this->assertStringContainsString('center: center', $adapter);
        $this->assertStringContainsString('zoom: zoom', $adapter);
        $this->assertStringContainsString('aspectRatio:', $adapter);
        $this->assertStringContainsString('scaleToFit: true', $adapter);
        $this->assertStringContainsString('size:', $adapter);
        $this->assertStringContainsString("mode: 'contain'", $adapter);
    }

    public function testFilePondImageTransformPluginRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondImageTransformPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.css', $result);
        $this->assertStringContainsString('/dist/filepond.css', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-encode.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-exif-orientation.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-size.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-type.js', $result);
        $this->assertStringContainsString('/dist/filepond.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-transform.js', $result);
    }

    public function testFilePondImageTransformPluginCdnSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\Cdn\FilePondImageTransformPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondCdnAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateTypePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageTransformPlugin::class, $this->view->assetBundles);
    }

    public function testFilePondImageTransformPluginCdnRegister(): void
    {
        $this->mockApplication();

        $view = new View();

        $this->assertEmpty($view->assetBundles);

        Asset\Cdn\FilePondImageTransformPlugin::register($view);

        $this->assertCount(7, $view->assetBundles);

        $result = $view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond@^4/dist/filepond.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-encode/dist/filepond-plugin-file-encode.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond@^4/dist/filepond.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-transform/dist/filepond-plugin-image-transform.min.js"></script>
            HTML,
            $result,
        );
    }

    public function testFilePondPdfPreviewPluginSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondPdfPreviewPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateTypePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondPdfPreviewPlugin::class, $this->view->assetBundles);
    }

    public function testFilePondPdfPreviewPluginRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondPdfPreviewPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.css', $result);
        $this->assertStringContainsString('/dist/filepond.css', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-encode.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-exif-orientation.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-size.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-type.js', $result);
        $this->assertStringContainsString('/dist/filepond.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-pdf-preview.js', $result);
    }

    public function testFilePondPdfPreviewPluginCdnSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\Cdn\FilePondPdfPreviewPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondCdnAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateTypePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondPdfPreviewPlugin::class, $this->view->assetBundles);
    }

    public function testFilePondPdfPreviewPluginCdnRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\Cdn\FilePondPdfPreviewPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond@^4/dist/filepond.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-encode/dist/filepond-plugin-file-encode.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond@^4/dist/filepond.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond-plugin-pdf-preview/dist/filepond-plugin-pdf-preview.min.css" rel="stylesheet">
            HTML,
            $result,
        );
    }

    public function testFilePondRenamePluginSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondRenamePlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);

        $this->assertArrayHasKey(Asset\FilePondAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateTypePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondRenamePlugin::class, $this->view->assetBundles);
    }

    public function testFilePondRenamePluginRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondRenamePlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.css', $result);
        $this->assertStringContainsString('/dist/filepond.css', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-encode.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-exif-orientation.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-size.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-type.js', $result);
        $this->assertStringContainsString('/dist/filepond.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-rename.js', $result);
    }

    public function testFilePondRenamePluginCdnSimpleDependency(): void
    {
        Asset\Cdn\FilePondRenamePlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondCdnAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateTypePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondRenamePlugin::class, $this->view->assetBundles);
    }

    public function testFilePondRenamePluginCdnRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\Cdn\FilePondRenamePlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond@^4/dist/filepond.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-encode/dist/filepond-plugin-file-encode.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond@^4/dist/filepond.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-rename/dist/filepond-plugin-file-rename.js"></script>
            HTML,
            $result,
        );
    }

    public function testFilePondFilePosterPluginRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondFilePosterPlugin::register($this->view);

        $this->assertCount(1, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondFilePosterPlugin::class, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString('/dist/filepond-plugin-file-poster.css', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-poster.js', $result);
    }

    public function testFilePondFilePosterPluginCdnRegister(): void
    {
        $this->mockApplication();

        $view = new View();

        $this->assertEmpty($view->assetBundles);

        Asset\Cdn\FilePondFilePosterPlugin::register($view);

        $this->assertCount(1, $view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondFilePosterPlugin::class, $view->assetBundles);

        $result = $view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond-plugin-file-poster/dist/filepond-plugin-file-poster.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-poster/dist/filepond-plugin-file-poster.min.js"></script>
            HTML,
            $result,
        );
    }
}
