<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset\Cdn;

use yii\web\AssetBundle;

/**
 * Asset bundle for Cropper.js in content delivery network (CDN) mode.
 */
final class CropperJsAsset extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $js = [
        'https://unpkg.com/cropperjs@2.1.0/dist/cropper.min.js',
    ];
}
