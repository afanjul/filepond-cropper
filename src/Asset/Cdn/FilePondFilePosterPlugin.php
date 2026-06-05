<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset\Cdn;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond file poster plugin in content delivery network (CDN) mode, mainly used for publishing
 * assets.
 */
final class FilePondFilePosterPlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $css = [
        'https://unpkg.com/filepond-plugin-file-poster/dist/filepond-plugin-file-poster.min.css',
    ];

    /**
     * {@inheritDoc}
     */
    public $js = [
        'https://unpkg.com/filepond-plugin-file-poster/dist/filepond-plugin-file-poster.min.js',
    ];
}
