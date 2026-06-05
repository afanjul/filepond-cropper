<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Tests;

use Yii;
use Yii2\Extensions\FilePond\FilePond;
use Yii2\Extensions\FilePond\Tests\Support\LogoForm;
use Yii2\Extensions\FilePond\Tests\Support\TestForm;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class RenderTest extends TestCase
{
    public function setup(): void
    {
        parent::setUp();
        $this->mockApplication();

        FilePond::$counter = 0;

        $this->view = Yii::$app->getView();
    }

    public function testAllowMultiple(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'allowMultiple' => true,
                'model' => new TestForm(),
            ],
        );

        $this->assertSame(
            '<input class="filepond" id="testform-array" name="TestForm[array][]" type="file" multiple>',
            $filePond,
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString('"allowMultiple":true', $result);
    }

    public function testCssClass(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'cssClass' => 'test-class',
                'model' => new TestForm(),
            ],
        );

        $this->assertSame(
            '<input class="test-class filepond" id="testform-array" name="TestForm[array][]" type="file">',
            $filePond,
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString('"className":"test-class"', $result);
    }

    public function testConfig(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'config' => ['forceRevert' => true, 'storeAsFile' => true],
                'model' => new TestForm(),
            ],
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString('"forceRevert":true,"storeAsFile":true', $result);
    }

    public function testFileRename(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'fileRename' => <<<JS
                    functionFileRename() {
                        return 'my_new_name.jpg';
                    }
                JS,
                'model' => new TestForm(),
            ],
        );

        $this->assertSame(
            '<input class="filepond" id="testform-array" name="TestForm[array][]" type="file">',
            $filePond,
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString('functionFileRename()', $result);

        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'fileRename' => <<<JS
                    functionFileRename() {
                        return 'my_new_name.jpg';
                    }
                JS,
                'fileValidateTypeDetectType' => <<<JS
                    fileValidateTypeDetectType: (source, type) =>
                        new Promise((resolve, reject) => {
                        // Do custom type detection here and return with promise
                        resolve(type);
                    }),
                JS,
                'model' => new TestForm(),
            ],
        );

        $this->assertSame(
            '<input class="filepond" id="testform-array" name="TestForm[array][]" type="file">',
            $filePond,
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString('fileValidateTypeDetectType: (source, type) =>', $result);
        $this->assertStringContainsString('functionFileRename()', $result);
    }

    public function testLabelIdle(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'labelIdle' => 'Drag & Drop or <span class="filepond--label-action"> Browse </span>',
                'model' => new TestForm(),
            ],
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString(
            '"labelIdle":"Drag \u0026 Drop or \u003Cspan class=\u0022filepond--label-action\u0022\u003E Browse \u003C\/span\u003E"',
            $result,
        );
    }

    public function testMaxFiles(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'maxFiles' => 3,
                'model' => new TestForm(),
            ],
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString('"maxFiles":3', $result);
    }

    public function testName(): void
    {
        $filePond = FilePond::widget(
            [
                'name' => 'filepond',
            ],
        );

        $this->assertSame(
            '<input class="filepond" id="w0-filepond" name="filepond[]" type="file">',
            $filePond,
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString('document.getElementById("w0-filepond")', $result);
    }

    public function testNotClassFormControl(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'model' => new TestForm(),
                'options' => ['class' => 'form-control'],
            ],
        );

        $this->assertSame(
            '<input class="filepond" id="testform-array" name="TestForm[array][]" type="file">',
            $filePond,
        );

        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'cssClass' => 'form-control',
                'model' => new TestForm(),
            ],
        );

        $this->assertSame(
            '<input class="filepond" id="testform-array" name="TestForm[array][]" type="file">',
            $filePond,
        );
    }

    public function testNotPlaceholder(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'model' => new TestForm(),
                'options' => ['placeholder' => 'test-placeholder'],
            ],
        );

        $this->assertSame(
            '<input class="filepond" id="testform-array" name="TestForm[array][]" type="file">',
            $filePond,
        );
    }

    public function testPluingDefault(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'maxFiles' => 3,
                'model' => new TestForm(),
            ],
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString('FilePondPluginFileEncode', $result);
        $this->assertStringContainsString('FilePondPluginFileValidateSize', $result);
        $this->assertStringContainsString('FilePondPluginFileValidateType', $result);
        $this->assertStringContainsString('FilePondPluginImageExifOrientation', $result);
        $this->assertStringContainsString('FilePondPluginImagePreview', $result);
    }

    public function testRender(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'model' => new TestForm(),
            ],
        );

        $this->assertSame(
            '<input class="filepond" id="testform-array" name="TestForm[array][]" type="file">',
            $filePond,
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString(
            'FilePond.registerPlugin(FilePondPluginFileEncode, FilePondPluginFileValidateSize, FilePondPluginFileValidateType, FilePondPluginImageExifOrientation, FilePondPluginImagePreview)',
            $result,
        );
        $this->assertStringContainsString('var filePondOptions = {', $result);
        $this->assertStringContainsString('"allowImageEdit":false', $result);
        $this->assertStringContainsString('"imagePreviewMaxInstantPreviewFileSize":1000000', $result);
        $this->assertStringContainsString('"imageTransformOutputQualityMode":"always"', $result);
        $this->assertStringContainsString('var filePondInput = document.getElementById("testform-array")', $result);
        $this->assertStringContainsString('var pond = FilePond.create(filePondInput, filePondOptions)', $result);
        $this->assertStringNotContainsString('FilePond.setOptions', $result);
        $this->assertStringNotContainsString('const pond', $result);
    }

    public function testImageEditWithCropper(): void
    {
        $filePond = FilePond::widget(
            [
                'acceptedFileTypes' => ['image/png', 'image/jpeg', 'image/webp'],
                'allowImageEdit' => true,
                'allowImageTransform' => true,
                'attribute' => 'array',
                'cropperOutputMimeType' => 'image/png',
                'cropperOutputQuality' => 0.92,
                'imageCropAspectRatio' => '1:1',
                'maxFileSize' => '2MB',
                'model' => new TestForm(),
            ],
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString('FilePondPluginImageEdit', $result);
        $this->assertStringContainsString('FilePondPluginImageTransform', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-edit.css', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-edit.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-transform.js', $result);
        $this->assertStringContainsString('/dist/cropper.js', $result);
        $this->assertStringContainsString('/filepond-cropper.css', $result);
        $this->assertStringContainsString('/filepond-cropper.js', $result);
        $this->assertStringContainsString('"allowImageEdit":true', $result);
        $this->assertStringContainsString('"imageEditAllowEdit":true', $result);
        $this->assertStringContainsString('"imageEditInstantEdit":false', $result);
        $this->assertStringContainsString('"imageTransformOutputMimeType":"image\/png"', $result);
        $this->assertStringContainsString('"imageTransformOutputQuality":92', $result);
        $this->assertStringContainsString(
            '"imageEditEditor":Yii2FilePondCropper.createEditor({"aspectRatios":["Free","1:1","16:9","4:3","3:2"],'
            . '"cancelLabel":"Cancel","confirmLabel":"Apply","cropperAspectRatio":"1:1"',
            $result,
        );
    }

    public function testInstantImageEditCropperTransformForLogoArrayInput(): void
    {
        $filePond = FilePond::widget(
            [
                'acceptedFileTypes' => ['image/png', 'image/jpeg', 'image/webp'],
                'allowImageEdit' => true,
                'allowImageTransform' => true,
                'attribute' => 'logo_file',
                'cropperOutputMimeType' => 'image/png',
                'imageCropAspectRatio' => '1:1',
                'imageEditInstantEdit' => true,
                'model' => new LogoForm(),
            ],
        );

        $this->assertSame(
            '<input class="filepond" id="model-logo_file" name="Model[logo_file][]" type="file">',
            $filePond,
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString('"acceptedFileTypes":["image\/png","image\/jpeg","image\/webp"]', $result);
        $this->assertStringContainsString('"imageEditInstantEdit":true', $result);
        $this->assertStringContainsString('"imageTransformOutputMimeType":"image\/png"', $result);
        $this->assertStringContainsString('"imageEditEditor":Yii2FilePondCropper.createEditor(', $result);
        $this->assertStringContainsString('FilePondPluginFileEncode', $result);

        $imageEditPosition = strpos($result, 'FilePondPluginImageEdit');
        $imageTransformPosition = strpos($result, 'FilePondPluginImageTransform');
        $imageEditAssetPosition = strpos($result, '/dist/filepond-plugin-image-edit.js');
        $imageTransformAssetPosition = strpos($result, '/dist/filepond-plugin-image-transform.js');
        $cropperAssetPosition = strpos($result, '/dist/cropper.js');
        $cropperAdapterPosition = strpos($result, '/filepond-cropper.js');
        $inlineEditorPosition = strpos($result, 'Yii2FilePondCropper.createEditor(');

        $this->assertIsInt($imageEditPosition);
        $this->assertIsInt($imageTransformPosition);
        $this->assertIsInt($imageEditAssetPosition);
        $this->assertIsInt($imageTransformAssetPosition);
        $this->assertIsInt($cropperAssetPosition);
        $this->assertIsInt($cropperAdapterPosition);
        $this->assertIsInt($inlineEditorPosition);
        $this->assertLessThan($imageTransformPosition, $imageEditPosition);
        $this->assertLessThan($imageTransformAssetPosition, $imageEditAssetPosition);
        $this->assertLessThan($cropperAdapterPosition, $cropperAssetPosition);
        $this->assertLessThan($inlineEditorPosition, $cropperAdapterPosition);
        $this->assertStringNotContainsString('https://unpkg.com', $result);
    }

    public function testImageEditEditorIsCreatedWithoutImageTransform(): void
    {
        $filePond = FilePond::widget(
            [
                'allowImageEdit' => true,
                'attribute' => 'array',
                'model' => new TestForm(),
            ],
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString('"allowImageEdit":true', $result);
        $this->assertStringContainsString('"allowImageTransform":false', $result);
        $this->assertStringContainsString('"imageEditEditor":Yii2FilePondCropper.createEditor(', $result);
        $this->assertStringNotContainsString('FilePondPluginImageTransform', $result);
    }

    public function testMultipleWidgetsDoNotEmitCollidingConstPond(): void
    {
        $first = FilePond::widget(['name' => 'first']);
        $second = FilePond::widget(['name' => 'second']);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $first . $second]);

        $this->assertStringContainsString('document.getElementById("w0-filepond")', $result);
        $this->assertStringContainsString('document.getElementById("w1-filepond")', $result);
        $this->assertSame(2, substr_count($result, 'var pond = FilePond.create(filePondInput, filePondOptions)'));
        $this->assertStringNotContainsString('const pond', $result);
        $this->assertStringNotContainsString('FilePond.setOptions', $result);
    }

    public function testRequired(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'model' => new TestForm(),
                'required' => true,
            ],
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertSame(
            '<input class="filepond" id="testform-array" name="TestForm[array][]" type="file" required>',
            $filePond,
        );
        $this->assertStringContainsString('"required":true', $result);
    }
}
