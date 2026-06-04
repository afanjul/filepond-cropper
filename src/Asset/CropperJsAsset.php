<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset;

use yii\web\AssetBundle;

/**
 * Asset bundle for Cropper.js.
 */
final class CropperJsAsset extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/cropperjs';

    public function init(): void
    {
        parent::init();

        $this->js = YII_ENV === 'prod' ? ['dist/cropper.min.js'] : ['dist/cropper.js'];

        $this->publishOptions['only'] = $this->js;
    }
}
