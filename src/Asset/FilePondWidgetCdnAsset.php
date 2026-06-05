<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset;

use yii\web\AssetBundle;

/**
 * Asset bundle for widget-level FilePond layout fixes with CDN dependencies.
 */
final class FilePondWidgetCdnAsset extends AssetBundle
{
    public function init(): void
    {
        parent::init();

        $this->sourcePath = __DIR__ . '/filepond-widget';
        $this->css = ['filepond-widget.css'];
        $this->publishOptions['only'] = $this->css;
    }

    /**
     * {@inheritDoc}
     *
     * @phpstan-var array<class-string>
     */
    public $depends = [
        FilePondCdnAsset::class,
    ];
}
