<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset;

use yii\web\AssetBundle;

/**
 * Asset bundle for the FilePond Cropper.js adapter.
 */
final class FilePondCropperAsset extends AssetBundle
{
    public function init(): void
    {
        parent::init();

        $this->sourcePath = __DIR__ . '/filepond-cropper';
        $this->css = ['filepond-cropper.css'];
        $this->js = ['filepond-cropper.js'];
        $this->publishOptions['only'] = array_merge($this->css, $this->js);
    }

    /**
     * {@inheritDoc}
     *
     * @phpstan-var array<class-string>
     */
    public $depends = [
        CropperJsAsset::class,
        FilePondImageEditPlugin::class,
    ];
}
