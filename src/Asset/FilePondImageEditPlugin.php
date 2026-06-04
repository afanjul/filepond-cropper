<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond image edit plugin, mainly used for publishing assets.
 */
final class FilePondImageEditPlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-plugin-image-edit';

    public function init(): void
    {
        parent::init();

        $this->css = YII_ENV === 'prod'
            ? ['dist/filepond-plugin-image-edit.min.css'] : ['dist/filepond-plugin-image-edit.css'];

        $this->js = YII_ENV === 'prod'
            ? ['dist/filepond-plugin-image-edit.min.js'] : ['dist/filepond-plugin-image-edit.js'];

        $this->publishOptions['only'] = array_merge($this->css, $this->js);
    }

    /**
     * {@inheritDoc}
     *
     * @phpstan-var array<class-string>
     */
    public $depends = [
        FilePondAsset::class,
    ];
}
