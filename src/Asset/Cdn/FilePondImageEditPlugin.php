<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset\Cdn;

use Yii2\Extensions\FilePond\Asset\FilePondCdnAsset;
use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond image edit plugin in content delivery network (CDN) mode.
 */
final class FilePondImageEditPlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $css = [
        'https://unpkg.com/filepond-plugin-image-edit@^1.6.3/dist/filepond-plugin-image-edit.min.css',
    ];

    /**
     * {@inheritDoc}
     */
    public $js = [
        'https://unpkg.com/filepond-plugin-image-edit@^1.6.3/dist/filepond-plugin-image-edit.min.js',
    ];

    /**
     * {@inheritDoc}
     */
    public $depends = [
        FilePondCdnAsset::class,
    ];
}
