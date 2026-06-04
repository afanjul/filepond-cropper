<?php

declare(strict_types=1);

use Yii2\Extensions\FilePond\FilePond;

/**
 * Minimal local-assets Cropper.js v2 demo for a Yii2 ActiveField.
 *
 * Expects a model attribute named `logo_file`; the rendered input name is
 * `Model[logo_file][]` when the model formName() returns `Model`.
 */
echo $form
    ->field($model, 'logo_file')
    ->widget(
        FilePond::class,
        [
            'acceptedFileTypes' => ['image/png', 'image/jpeg', 'image/webp'],
            'allowImageEdit' => true,
            'allowImageTransform' => true,
            'cdn' => false,
            'cropperOutputMimeType' => 'image/png',
            'imageCropAspectRatio' => '1:1',
            'imageEditInstantEdit' => true,
            'maxFiles' => 1,
        ],
    );
