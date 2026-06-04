(function (window, document) {
    'use strict';

    var namespace = window.Yii2FilePondCropper = window.Yii2FilePondCropper || {};

    var clamp = function (value, min, max) {
        return Math.min(Math.max(value, min), max);
    };

    var isFiniteNumber = function (value) {
        return typeof value === 'number' && isFinite(value);
    };

    var getPositiveNumber = function (value, fallback) {
        var number = Number(value);

        return number > 0 && isFinite(number) ? number : fallback;
    };

    var parseAspectRatio = function (value) {
        if (typeof value === 'number' && value > 0) {
            return value;
        }

        if (typeof value !== 'string' || value === '') {
            return null;
        }

        var parts = value.split(':');

        if (parts.length === 2) {
            var width = Number(parts[0]);
            var height = Number(parts[1]);

            return width > 0 && height > 0 ? width / height : null;
        }

        var ratio = Number(value);

        return ratio > 0 ? ratio : null;
    };

    var createTemplate = function (aspectRatio) {
        var ratio = aspectRatio ? ' aspect-ratio="' + aspectRatio + '" initial-aspect-ratio="' + aspectRatio + '"' : '';

        return '<cropper-canvas background>' +
            '<cropper-image rotatable scalable skewable translatable></cropper-image>' +
            '<cropper-shade hidden></cropper-shade>' +
            '<cropper-handle action="select" plain></cropper-handle>' +
            '<cropper-selection initial-coverage="0.8" movable resizable' + ratio + '>' +
            '<cropper-grid role="grid" covered></cropper-grid>' +
            '<cropper-crosshair centered></cropper-crosshair>' +
            '<cropper-handle action="move" theme-color="rgba(255,255,255,0.35)"></cropper-handle>' +
            '<cropper-handle action="n-resize"></cropper-handle>' +
            '<cropper-handle action="e-resize"></cropper-handle>' +
            '<cropper-handle action="s-resize"></cropper-handle>' +
            '<cropper-handle action="w-resize"></cropper-handle>' +
            '<cropper-handle action="ne-resize"></cropper-handle>' +
            '<cropper-handle action="nw-resize"></cropper-handle>' +
            '<cropper-handle action="se-resize"></cropper-handle>' +
            '<cropper-handle action="sw-resize"></cropper-handle>' +
            '</cropper-selection>' +
            '</cropper-canvas>';
    };

    var createElement = function (tag, className, text) {
        var element = document.createElement(tag);

        if (className) {
            element.className = className;
        }

        if (typeof text === 'string') {
            element.textContent = text;
        }

        return element;
    };

    var getCropperConstructor = function () {
        return typeof window.Cropper === 'function'
            ? window.Cropper
            : window.Cropper && typeof window.Cropper.default === 'function'
                ? window.Cropper.default
                : null;
    };

    var buildCropData = function (cropper, selection, canvas) {
        var image = cropper.getCropperImage ? cropper.getCropperImage() : null;
        var selectionRect = selection.getBoundingClientRect();
        var imageRect = image ? image.getBoundingClientRect() : null;
        var selectionWidth = getPositiveNumber(selection.width, selectionRect.width);
        var selectionHeight = getPositiveNumber(selection.height, selectionRect.height);
        var canvasWidth = getPositiveNumber(canvas.width, selectionWidth);
        var canvasHeight = getPositiveNumber(canvas.height, selectionHeight);
        var hasRects = imageRect && imageRect.width > 0 && imageRect.height > 0 &&
            selectionRect.width > 0 && selectionRect.height > 0;
        var center = hasRects
            ? {
                x: clamp(((selectionRect.left + selectionRect.width * 0.5) - imageRect.left) / imageRect.width, 0, 1),
                y: clamp(((selectionRect.top + selectionRect.height * 0.5) - imageRect.top) / imageRect.height, 0, 1)
            }
            : { x: 0.5, y: 0.5 };
        var zoom = hasRects
            ? Math.max(imageRect.width / selectionRect.width, imageRect.height / selectionRect.height, 1)
            : 1;
        var aspectRatio = selectionWidth > 0
            ? selectionHeight / selectionWidth
            : canvasHeight / canvasWidth;

        return {
            crop: {
                center: center,
                flip: {
                    horizontal: false,
                    vertical: false
                },
                zoom: zoom,
                rotation: 0,
                aspectRatio: isFiniteNumber(aspectRatio) && aspectRatio > 0 ? aspectRatio : null,
                scaleToFit: true
            },
            size: {
                upscale: false,
                mode: 'contain',
                width: canvasWidth,
                height: canvasHeight
            }
        };
    };

    namespace.createEditor = function (options) {
        var settings = options || {};
        var editor = {
            cropAspectRatio: settings.cropperAspectRatio || null,
            onconfirm: null,
            oncancel: null,
            onclose: null,
            open: function (file) {
                var objectUrl = window.URL.createObjectURL(file);
                var overlay = createElement('div', 'filepond-cropper');
                var dialog = createElement('div', 'filepond-cropper__dialog');
                var header = createElement('div', 'filepond-cropper__header', settings.modalTitle || 'Edit image');
                var body = createElement('div', 'filepond-cropper__body');
                var footer = createElement('div', 'filepond-cropper__footer');
                var image = createElement('img', 'filepond-cropper__image');
                var cancelButton = createElement('button', 'filepond-cropper__button', settings.cancelLabel || 'Cancel');
                var confirmButton = createElement(
                    'button',
                    'filepond-cropper__button filepond-cropper__button--primary',
                    settings.confirmLabel || 'Apply'
                );
                var cropper = null;
                var closed = false;

                var initializeCropper = function () {
                    var Cropper = getCropperConstructor();
                    var cropperOptions = Object.assign({}, settings.cropperOptions || {});
                    var aspectRatio = parseAspectRatio(settings.cropperAspectRatio || editor.cropAspectRatio);

                    if (!Cropper || cropper) {
                        return;
                    }

                    if (!cropperOptions.template) {
                        cropperOptions.template = createTemplate(aspectRatio);
                    }

                    cropper = new Cropper(image, cropperOptions);
                };

                var close = function () {
                    if (closed) {
                        return;
                    }

                    closed = true;

                    if (cropper && cropper.destroy) {
                        cropper.destroy();
                    }

                    window.URL.revokeObjectURL(objectUrl);
                    overlay.remove();

                    if (typeof editor.onclose === 'function') {
                        editor.onclose();
                    }
                };

                var cancel = function () {
                    if (typeof editor.oncancel === 'function') {
                        editor.oncancel();
                    }

                    close();
                };

                cancelButton.type = 'button';
                confirmButton.type = 'button';
                image.alt = '';
                overlay.tabIndex = -1;

                image.addEventListener('load', initializeCropper, { once: true });

                footer.appendChild(cancelButton);
                footer.appendChild(confirmButton);
                body.appendChild(image);
                dialog.appendChild(header);
                dialog.appendChild(body);
                dialog.appendChild(footer);
                overlay.appendChild(dialog);
                document.body.appendChild(overlay);
                overlay.focus();
                image.src = objectUrl;

                if (image.complete && image.naturalWidth > 0) {
                    initializeCropper();
                }

                cancelButton.addEventListener('click', cancel);
                overlay.addEventListener('click', function (event) {
                    if (event.target === overlay) {
                        cancel();
                    }
                });
                overlay.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        cancel();
                    }
                });
                confirmButton.addEventListener('click', function () {
                    if (!cropper || !cropper.getCropperSelection) {
                        return;
                    }

                    var selection = cropper.getCropperSelection();

                    if (!selection || !selection.$toCanvas) {
                        return;
                    }

                    confirmButton.disabled = true;

                    selection.$toCanvas().then(function (canvas) {
                        if (typeof editor.onconfirm === 'function') {
                            editor.onconfirm({ data: buildCropData(cropper, selection, canvas) });
                        }

                        close();
                    }).catch(function () {
                        confirmButton.disabled = false;
                    });
                });
            }
        };

        return editor;
    };
})(window, document);
