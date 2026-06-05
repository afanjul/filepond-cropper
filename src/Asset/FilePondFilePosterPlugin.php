<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond file poster plugin, mainly used for publishing assets.
 */
final class FilePondFilePosterPlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-plugin-file-poster';

    public function init(): void
    {
        parent::init();

        $this->css = YII_ENV === 'prod'
            ? ['dist/filepond-plugin-file-poster.min.css'] : ['dist/filepond-plugin-file-poster.css'];

        $this->js = YII_ENV === 'prod'
            ? ['dist/filepond-plugin-file-poster.min.js'] : ['dist/filepond-plugin-file-poster.js'];

        $this->publishOptions['only'] = array_merge($this->css, $this->js);
    }
}
