<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond;

use UIAwesome\Html\{FormControl\Input\File, Helper\CssClass, Helper\Utils};
use Yii2\Extensions\FilePond\Asset;
use Yii;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\widgets\InputWidget;

final class FilePond extends InputWidget
{
    public array $acceptedFileTypes = [];
    public bool $allowFileTypeValidation = true;
    public bool $allowFileRename = false;
    public bool $allowFileValidateSize = true;
    public bool $allowFilePoster = false;
    public bool $allowImageCrop = false;
    public bool $allowImageEdit = false;
    public bool $allowImageExifOrientation = true;
    public bool $allowImagePreview = true;
    public bool $allowImageTransform = false;
    public bool $allowMultiple = false;
    public bool $allowPdfPreview = false;
    public string $cssClass = '';
    public bool $cdn = false;
    public array $config = [];
    public string|null $cropperAspectRatio = null;
    public string $cropperModalTitle = 'Edit image';
    public array $cropperOptions = [];
    public string|null $cropperOutputMimeType = null;
    public int|float|null $cropperOutputQuality = null;
    public string $cropperCancelLabel = 'Cancel';
    public string $cropperConfirmLabel = 'Apply';
    public string $cropperResetLabel = 'Reset';
    public string $cropperZoomInLabel = 'Zoom in';
    public string $cropperZoomOutLabel = 'Zoom out';
    /**
     * @var array|false The aspect-ratio preset buttons shown in the crop toolbar.
     *
     * A list of strings such as `['Free', '1:1', '16:9', '4:3', '3:2']`. The label `Free` (case-insensitive)
     * produces an unconstrained crop. Set to `false` to hide the preset buttons entirely and honor only
     * {@see $cropperAspectRatio}. An empty array falls back to the JavaScript defaults.
     */
    public array|false $cropperAspectRatios = ['Free', '1:1', '16:9', '4:3', '3:2'];
    /**
     * @var bool Whether the crop dialog remembers the last selection (position, size and aspect ratio)
     * and restores it the next time the same editor instance is opened.
     */
    public bool $cropperRememberPosition = false;
    /**
     * @var array The FilePond `files` collection used to seed already-uploaded items.
     *
     * Each entry follows FilePond's native shape, for example to render an existing image as a poster
     * without re-uploading it:
     *
     * ```php
     * 'files' => [
     *     [
     *         'source' => $logoUrl,
     *         'options' => [
     *             'type' => 'local',
     *             'metadata' => ['poster' => $logoUrl],
     *         ],
     *     ],
     * ];
     * ```
     *
     * @link https://pqina.nl/filepond/docs/api/plugins/file-poster/
     */
    public array $files = [];
    /**
     * @var int|null Fixed file poster height in pixels; overrides {@see $filePosterMinHeight} and
     * {@see $filePosterMaxHeight}.
     */
    public int|null $filePosterHeight = null;
    public int|null $filePosterMaxHeight = null;
    public int|null $filePosterMinHeight = null;
    public string $fileRename = '';
    /**
     * @var string The file validate type detect type function.
     *
     * ```JS
     * fileValidateTypeDetectType: (source, type) =>
     *     new Promise((resolve, reject) => {
     *     // Do custom type detection here and return with promise
     *
     *     resolve(type);
     * }),
     * ```
     *
     * @link https://pqina.nl/filepond/docs/api/plugins/file-validate-type/#custom-type-detection
     */
    public string $fileValidateTypeDetectType = '';
    public string $fileValidateTypeLabelExpectedTypes = '';
    public string|null $imageCropAspectRatio = null;
    public bool $imageEditAllowEdit = true;
    public bool $imageEditInstantEdit = false;
    /**
     * @var string The image preview height.
     *
     * Fixed image preview height, overrides min and max preview height.
     */
    public string|null $imagePreviewHeight = null;
    public bool $imagePreviewMarkupShow = true;
    /**
     * @var string The image preview max file size.
     *
     * Maximum file size for images to preview immediately, if files are larger and the browser doesn't support
     * createImageBitmap the preview is queued till FilePond is in rest state.
     *
     * By default, no maximum file size is defined, expects a string, like `2MB` or `500KB`.
     */
    public string|null $imagePreviewMaxFileSize = null;
    /**
     * @var int The image preview max height.
     *
     * Maximum height of the image preview in pixels.
     */
    public int $imagePreviewMaxHeight = 256;
    /**
     * @var int The image preview max instant preview file size.
     *
     * Maximum file size for images to preview immediately, if files are larger and the browser doesn't support
     * createImageBitmap the preview is queued till FilePond is in rest state.
     */
    public int $imagePreviewMaxInstantPreviewFileSize = 1000000;
    /**
     * @var int The image preview min height.
     *
     * Minimum height of the image preview in pixels.
     */
    public int $imagePreviewMinHeight = 44;
    /**
     * @var string The image preview transparency indicator.
     *
     * Show a grid behind the preview, set to a color value (for example '#f00') to set transparent image background
     * color.
     *
     * Please note that this is only for preview purposes.
     *
     * The background color or grid isn't embedded in the output image.
     */
    public string|null $imagePreviewTransparencyIndicator = null;
    /**
     * @var array The image transform after create blob.
     *
     * A hook to make changes to the file after the file has been created.
     */
    public array|null $imageTransformAfterCreateBlob = null;
    /**
     * @var array The image transform before create blob.
     *
     * A hook to make changes to the canvas before the file is created.
     */
    public array|null $imageTransformBeforeCreateBlob = null;
    /**
     * @var int The image transform output quality.
     *
     * A number between 0 and 100 indicating image quality (e.g. 92 => 92%).
     */
    public int|null $imageTransformOutputQuality = null;
    public string|null $imageTransformOutputMimeType = null;
    /**
     * @var array The image transform client transforms.
     *
     * An array of transforms to apply on the client, useful if we, for instance, want to do resizing on the client but
     * cropping on the server. Null means apply all transforms ('resize', 'crop').
     */
    public array|null $imageTransformClientTransforms = null;
    /**
     * @var string The image transform output quality mode.
     *
     * Should output quality be enforced, set the 'optional' to only apply when a transform is required due to other
     * requirements (e.g. resize or crop).
     */
    public string $imageTransformOutputQualityMode = 'always';
    public bool $imageTransformOutputStripImageHead = true;
    /**
     * @var array The image transform variants.
     *
     * An object that can be used to output many files based on different transform instructions.
     */
    public array|null $imageTransformVariants = null;
    /**
     * @var bool Whether the image transform variants include original.
     *
     * Should the transform plugin output the original file.
     */
    public bool $imageTransformVariantsIncludeDefault = true;
    /**
     * @var string The image transform variants default name.
     *
     * The name to use in front of the file name.
     */
    public string|null $imageTransformVariantsDefaultName = null;
    /**
     * @var bool Whether the image transform variants include original.
     *
     * Should the transform plugin output the original file.
     */
    public bool $imageTransformVariantsIncludeOriginal = false;
    public string $labelIdle = '';
    public string $labelMaxFileSize = '';
    public string $labelMaxFileSizeExceeded = '';
    public string $labelMaxTotalFileSize = '';
    public string $labelMaxTotalFileSizeExceeded = '';
    public string $labelFileTypeNotAllowed = '';
    public string $loadFileDefault = '';
    public int $maxFiles = 1;
    public string|null $maxFileSize = null;
    public string|null $maxTotalFileSize = null;
    public string|null $minFileSize = null;
    /**
     * @phpstan-var string[] The default plugins to load.
     */
    public array $pluginDefault = [
        'FilePondPluginFileEncode',
        'FilePondPluginFileValidateSize',
        'FilePondPluginFileValidateType',
        'FilePondPluginImageExifOrientation',
        'FilePondPluginImagePreview',
    ];
    public int $pdfPreviewHeight = 320;
    public string $pdfComponentExtraParams = 'toolbar=0&view=fit&page=1';
    public bool $required = false;

    private string $id = '';

    public function init(): void
    {
        parent::init();

        $this->config = array_merge(
            [
                'acceptedFileTypes' => $this->acceptedFileTypes,
                'allowFileRename' => $this->allowFileRename,
                'allowFilePoster' => $this->allowFilePoster,
                'allowFileTypeValidation' => $this->allowFileTypeValidation,
                'allowFileValidateSize' => $this->allowFileValidateSize,
                'allowImageCrop' => $this->allowImageCrop,
                'allowImageEdit' => $this->allowImageEdit,
                'allowImageExifOrientation' => $this->allowImageExifOrientation,
                'allowImagePreview' => $this->allowImagePreview,
                'allowImageTransform' => $this->allowImageTransform,
                'allowMultiple' => $this->allowMultiple,
                'className' => $this->cssClass,
                'filePosterHeight' => $this->filePosterHeight,
                'filePosterMaxHeight' => $this->filePosterMaxHeight,
                'filePosterMinHeight' => $this->filePosterMinHeight,
                'fileValidateTypeLabelExpectedTypes' => Yii::t(
                    'yii.filepond',
                    'Expects {allButLastType} or {lastType}',
                ),
                'imageCropAspectRatio' => $this->imageCropAspectRatio,
                'imageEditAllowEdit' => $this->imageEditAllowEdit,
                'imageEditInstantEdit' => $this->imageEditInstantEdit,
                'imagePreviewHeight' => $this->imagePreviewHeight,
                'imagePreviewMarkupShow' => $this->imagePreviewMarkupShow,
                'imagePreviewMaxFileSize' => $this->imagePreviewMaxFileSize,
                'imagePreviewMaxHeight' => $this->imagePreviewMaxHeight,
                'imagePreviewMaxInstantPreviewFileSize' => $this->imagePreviewMaxInstantPreviewFileSize,
                'imagePreviewMinHeight' => $this->imagePreviewMinHeight,
                'imagePreviewTransparencyIndicator' => $this->imagePreviewTransparencyIndicator,
                'imageTransformAfterCreateBlob' => $this->imageTransformAfterCreateBlob,
                'imageTransformBeforeCreateBlob' => $this->imageTransformBeforeCreateBlob,
                'imageTransformClientTransforms' => $this->imageTransformClientTransforms,
                'imageTransformOutputMimeType' => $this->imageTransformOutputMimeType ?? $this->cropperOutputMimeType,
                'imageTransformOutputQuality' => $this->imageTransformOutputQuality,
                'imageTransformOutputQualityMode' => $this->imageTransformOutputQualityMode,
                'imageTransformOutputStripImageHead' => $this->imageTransformOutputStripImageHead,
                'imageTransformVariants' => $this->imageTransformVariants,
                'imageTransformVariantsDefaultName' => $this->imageTransformVariantsDefaultName,
                'imageTransformVariantsIncludeDefault' => $this->imageTransformVariantsIncludeDefault,
                'imageTransformVariantsIncludeOriginal' => $this->imageTransformVariantsIncludeOriginal,
                'labelFileTypeNotAllowed' => Yii::t('yii.filepond', 'File type not allowed'),
                'labelIdle' => $this->labelIdle === ''
                    ? Yii::t(
                        'yii.filepond',
                        'Drag & Drop your files or <span class="filepond--label-action"> Browse </span>',
                    )
                    : $this->labelIdle,
                'labelMaxFileSize' => Yii::t('yii.filepond', 'Maximum file size is {filesize}'),
                'labelMaxFileSizeExceeded' => Yii::t('yii.filepond', 'File is too large'),
                'labelMaxTotalFileSize' => Yii::t('yii.filepond', 'Maximum total file size is {filesize}'),
                'labelMaxTotalFileSizeExceeded' => Yii::t('yii.filepond', 'Maximum total size exceeded'),
                'maxFiles' => $this->maxFiles,
                'maxFileSize' => $this->maxFileSize,
                'maxTotalFileSize' => $this->maxTotalFileSize,
                'minFileSize' => $this->minFileSize,
                'pdfPreviewHeight' => $this->pdfPreviewHeight,
                'pdfComponentExtraParams' => $this->pdfComponentExtraParams,
                'required' => $this->required,
            ],
            $this->config,
        );

        $this->registerOptionalPlugins();

        if ($this->files !== [] && array_key_exists('files', $this->config) === false) {
            $this->config['files'] = $this->files;
        }

        if ($this->allowImageEdit && array_key_exists('imageEditEditor', $this->config) === false) {
            $this->config['imageEditEditor'] = $this->createImageEditEditorExpression();
        }

        if ($this->cropperOutputQuality !== null && $this->config['imageTransformOutputQuality'] === null) {
            $this->config['imageTransformOutputQuality'] = $this->normalizeOutputQuality($this->cropperOutputQuality);
        }

        $this->id = $this->hasModel()
            ? Utils::generateInputId($this->model->formName(), $this->attribute)
            : $this->getId() . '-filepond';
    }

    public function run(): string
    {
        $this->registerClientScript();

        return $this->renderInputFile();
    }

    private function getScript(): string
    {
        $inlineOptions = trim($this->fileRename);

        if ($this->fileValidateTypeDetectType !== '') {
            $inlineOptions = trim("{$this->fileValidateTypeDetectType} {$inlineOptions}");
        }

        $id = Json::htmlEncode($this->id);
        $loadFileDefault = Json::htmlEncode($this->loadFileDefault);
        $pluginConfig = implode(', ', $this->pluginDefault);
        $filePondOptions = Json::htmlEncode($this->config);
        $mergeInlineOptions = $inlineOptions === '' ? '' : "Object.assign(filePondOptions, {{$inlineOptions}})\n";

        return <<<JS
        FilePond.registerPlugin($pluginConfig)

        var filePondOptions = $filePondOptions
        $mergeInlineOptions
        var loadFileDefault = $loadFileDefault
        var filePondInput = document.getElementById($id)
        var pond = FilePond.create(filePondInput, filePondOptions)

        if (loadFileDefault !== '') {
            pond.addFiles(loadFileDefault)
        }
        JS;
    }

    private function registerClientScript(): void
    {
        $view = $this->getView();

        match ($this->cdn) {
            true => Asset\FilePondWidgetCdnAsset::register($view),
            default => Asset\FilePondWidgetAsset::register($view),
        };

        $this->registerOptionalPluginAssets();

        $view->registerJs($this->getScript());
    }

    private function createImageEditEditorExpression(): JsExpression
    {
        $cropperOptions = Json::htmlEncode(
            [
                'aspectRatios' => $this->cropperAspectRatios,
                'cancelLabel' => $this->cropperCancelLabel,
                'confirmLabel' => $this->cropperConfirmLabel,
                'cropperAspectRatio' => $this->cropperAspectRatio ?? $this->imageCropAspectRatio,
                'cropperOptions' => $this->cropperOptions,
                'modalTitle' => $this->cropperModalTitle,
                'rememberCropPosition' => $this->cropperRememberPosition,
                'resetLabel' => $this->cropperResetLabel,
                'zoomInLabel' => $this->cropperZoomInLabel,
                'zoomOutLabel' => $this->cropperZoomOutLabel,
            ],
        );

        return new JsExpression("Yii2FilePondCropper.createEditor($cropperOptions)");
    }

    private function normalizeOutputQuality(int|float $quality): int
    {
        $normalizedQuality = (int) round($quality);

        if ($quality <= 1) {
            $normalizedQuality = (int) round($quality * 100);
        }

        return min(100, max(0, $normalizedQuality));
    }

    private function registerOptionalPluginAssets(): void
    {
        $view = $this->getView();

        if ($this->allowFileRename) {
            match ($this->cdn) {
                true => Asset\Cdn\FilePondRenamePlugin::register($view),
                default => Asset\FilePondRenamePlugin::register($view),
            };
        }

        if ($this->allowFilePoster) {
            match ($this->cdn) {
                true => Asset\Cdn\FilePondFilePosterPlugin::register($view),
                default => Asset\FilePondFilePosterPlugin::register($view),
            };
        }

        if ($this->allowImageCrop) {
            match ($this->cdn) {
                true => Asset\Cdn\FilePondImageCropPlugin::register($view),
                default => Asset\FilePondImageCropPlugin::register($view),
            };
        }

        if ($this->allowImageEdit) {
            match ($this->cdn) {
                true => Asset\FilePondCropperCdnAsset::register($view),
                default => Asset\FilePondCropperAsset::register($view),
            };
        }

        if ($this->allowImageTransform) {
            match ($this->cdn) {
                true => Asset\Cdn\FilePondImageTransformPlugin::register($view),
                default => Asset\FilePondImageTransformPlugin::register($view),
            };
        }

        if ($this->allowPdfPreview) {
            match ($this->cdn) {
                true => Asset\Cdn\FilePondPdfPreviewPlugin::register($view),
                default => Asset\FilePondPdfPreviewPlugin::register($view),
            };
        }
    }

    private function registerOptionalPlugins(): void
    {
        $plugins = [];

        if ($this->allowFileRename) {
            $plugins[] = 'FilePondPluginFileRename';
        }

        if ($this->allowFilePoster) {
            $plugins[] = 'FilePondPluginFilePoster';
        }

        if ($this->allowImageCrop) {
            $plugins[] = 'FilePondPluginImageCrop';
        }

        if ($this->allowImageEdit) {
            $plugins[] = 'FilePondPluginImageEdit';
        }

        if ($this->allowImageTransform) {
            $plugins[] = 'FilePondPluginImageTransform';
        }

        if ($this->allowPdfPreview) {
            $plugins[] = 'FilePondPluginPdfPreview';
        }

        $this->pluginDefault = array_values(array_unique(array_merge($this->pluginDefault, $plugins)));
    }

    /**
     * @return string the generated input tag.
     */
    private function renderInputFile(): string
    {
        $name = $this->name;
        $options = $this->options;

        if (isset($options['class']) && str_contains($options['class'], 'form-control')) {
            $options['class'] = str_replace('form-control', '', $options['class']);
        }

        if (array_key_exists('allowMultiple', $this->config) && $this->config['allowMultiple']) {
            $options['multiple'] = true;
        }

        if (array_key_exists('className', $this->config) && is_string($this->config['className'])) {
            $class = str_replace('form-control', '', $this->config['className']);
            CssClass::add($options, $class);
        }

        if (array_key_exists('required', $this->config) && $this->config['required']) {
            $options['required'] = true;
        }

        CssClass::add($options, 'filepond');

        $name = match ($this->hasModel()) {
            true => Utils::generateArrayableName(Utils::generateInputName($this->model->formName(), $this->attribute)),
            default => Utils::generateArrayableName($name),
        };

        // input type="file" not supported value attribute.
        unset($options['id'], $options['placeholder'], $options['value']);

        return File::widget()->attributes($options)->id($this->id)->name($name)->render();
    }
}
