(function (window, document) {
    'use strict';

    var namespace = window.Yii2FilePondCropper = window.Yii2FilePondCropper || {};

    // Lightweight debug logger. Every relevant cropper action is traced via console.debug under the
    // '[filepond-cropper]' prefix so upload/crop/storage issues can be diagnosed from the browser console.
    var log = function () {
        var args = Array.prototype.slice.call(arguments);

        args.unshift('[filepond-cropper]');
        console.debug.apply(console, args);
    };

    var describeFile = function (file) {
        if (!file) {
            return null;
        }

        return { name: file.name, type: file.type, size: file.size };
    };

    var DEFAULT_ASPECT_RATIOS = ['Free', '1:1', '16:9', '4:3', '3:2'];

    var ICONS = {
        zoomIn: '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>',
        zoomOut: '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>',
        reset: '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>'
    };

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

    var buildAspectRatioList = function (value) {
        if (value === false) {
            return [];
        }

        var source = Array.isArray(value) && value.length ? value : DEFAULT_ASPECT_RATIOS;

        return source.map(function (item) {
            if (item && typeof item === 'object') {
                return { label: String(item.label), value: parseAspectRatio(item.value) };
            }

            var label = String(item);

            return {
                label: label,
                value: /^free$/i.test(label) ? null : parseAspectRatio(label)
            };
        });
    };

    var createTemplate = function (aspectRatio) {
        var ratio = aspectRatio ? ' aspect-ratio="' + aspectRatio + '" initial-aspect-ratio="' + aspectRatio + '"' : '';

        return '<cropper-canvas background>' +
            '<cropper-image rotatable scalable skewable translatable initial-center-size="contain"></cropper-image>' +
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

    var createIconButton = function (className, icon, label) {
        var button = createElement('button', className);

        button.type = 'button';
        button.innerHTML = icon;

        if (label) {
            button.title = label;
            button.setAttribute('aria-label', label);
        }

        return button;
    };

    var getCropperConstructor = function () {
        return typeof window.Cropper === 'function'
            ? window.Cropper
            : window.Cropper && typeof window.Cropper.default === 'function'
                ? window.Cropper.default
                : null;
    };

    var buildSourceRect = function (selectionRect, imageRect, sourceImage) {
        var naturalWidth = sourceImage && sourceImage.naturalWidth ? sourceImage.naturalWidth : 0;
        var naturalHeight = sourceImage && sourceImage.naturalHeight ? sourceImage.naturalHeight : 0;

        if (!imageRect || imageRect.width <= 0 || imageRect.height <= 0 || naturalWidth <= 0 || naturalHeight <= 0) {
            return null;
        }

        var scaleX = naturalWidth / imageRect.width;
        var scaleY = naturalHeight / imageRect.height;
        var rawX = (selectionRect.left - imageRect.left) * scaleX;
        var rawY = (selectionRect.top - imageRect.top) * scaleY;
        var x = clamp(rawX, 0, naturalWidth);
        var y = clamp(rawY, 0, naturalHeight);
        var width = clamp(selectionRect.width * scaleX + (rawX - x), 0, naturalWidth - x);
        var height = clamp(selectionRect.height * scaleY + (rawY - y), 0, naturalHeight - y);

        return {
            x: Math.round(x),
            y: Math.round(y),
            width: Math.round(width),
            height: Math.round(height),
            rotate: 0,
            scaleX: 1,
            scaleY: 1,
            naturalWidth: naturalWidth,
            naturalHeight: naturalHeight
        };
    };

    var buildCropData = function (cropper, selection, canvas, sourceImage) {
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
        var crop = {
            center: center,
            flip: {
                horizontal: false,
                vertical: false
            },
            zoom: zoom,
            rotation: 0,
            aspectRatio: isFiniteNumber(aspectRatio) && aspectRatio > 0 ? aspectRatio : null,
            scaleToFit: true
        };

        // Source-pixel rectangle for server-side cropping. Rides along in `metadata.crop.rect`
        // (FilePond ImageEdit copies `data.crop` verbatim). The client-side ImageTransform plugin
        // ignores this extra key, so it is harmless when transforms run in the browser.
        var rect = hasRects ? buildSourceRect(selectionRect, imageRect, sourceImage) : null;

        if (rect) {
            crop.rect = rect;
        }

        var sizeWidth = Math.round(canvasWidth);
        var sizeHeight = Math.round(canvasHeight);

        log('buildCropData', {
            selectionRect: { width: selectionRect.width, height: selectionRect.height, left: selectionRect.left, top: selectionRect.top },
            imageRect: imageRect ? { width: imageRect.width, height: imageRect.height, left: imageRect.left, top: imageRect.top } : null,
            sourceNatural: { width: sourceImage ? sourceImage.naturalWidth : null, height: sourceImage ? sourceImage.naturalHeight : null },
            canvas: { width: canvas.width, height: canvas.height },
            selectionWH: { width: selectionWidth, height: selectionHeight },
            computed: { center: center, zoom: zoom, aspectRatio: crop.aspectRatio, sizeWidth: sizeWidth, sizeHeight: sizeHeight },
            rect: rect,
            hasRects: hasRects
        });

        var result = { crop: crop };

        // Only emit a resize step when both dimensions are positive integers. A sub-pixel canvas can
        // round to 0, which makes ImageTransform build a 0-width source and throw IndexSizeError.
        if (sizeWidth > 0 && sizeHeight > 0) {
            result.size = {
                upscale: false,
                mode: 'contain',
                width: sizeWidth,
                height: sizeHeight
            };
        }

        return result;
    };

    namespace.createEditor = function (options) {
        var settings = options || {};

        log('createEditor', {
            cropperAspectRatio: settings.cropperAspectRatio,
            aspectRatios: settings.aspectRatios,
            rememberCropPosition: settings.rememberCropPosition,
            cropperOptions: settings.cropperOptions
        });

        var editor = {
            cropAspectRatio: settings.cropperAspectRatio || null,
            onconfirm: null,
            oncancel: null,
            onclose: null,
            open: function (file) {
                log('open', { file: describeFile(file) });

                var objectUrl = window.URL.createObjectURL(file);
                var initialAspectRatio = parseAspectRatio(settings.cropperAspectRatio || editor.cropAspectRatio);
                var aspectRatioList = buildAspectRatioList(settings.aspectRatios);

                log('open: parsed config', {
                    objectUrl: objectUrl,
                    initialAspectRatio: initialAspectRatio,
                    aspectRatioList: aspectRatioList
                });

                var overlay = createElement('div', 'filepond-cropper');
                var dialog = createElement('div', 'filepond-cropper__dialog');
                var header = createElement('div', 'filepond-cropper__header', settings.modalTitle || 'Edit image');
                var body = createElement('div', 'filepond-cropper__body');
                var footer = createElement('div', 'filepond-cropper__footer');
                var toolbar = createElement('div', 'filepond-cropper__toolbar');
                var actions = createElement('div', 'filepond-cropper__actions');
                var image = createElement('img', 'filepond-cropper__image');
                var cancelButton = createElement('button', 'filepond-cropper__button', settings.cancelLabel || 'Cancel');
                var confirmButton = createElement(
                    'button',
                    'filepond-cropper__button filepond-cropper__button--primary',
                    settings.confirmLabel || 'Apply'
                );
                var cropper = null;
                var closed = false;
                var aspectButtons = [];

                var getImage = function () {
                    return cropper && cropper.getCropperImage ? cropper.getCropperImage() : null;
                };

                var getSelection = function () {
                    return cropper && cropper.getCropperSelection ? cropper.getCropperSelection() : null;
                };

                var getCanvas = function () {
                    return cropper && cropper.getCropperCanvas ? cropper.getCropperCanvas() : null;
                };

                // Remember crop position across reopens of the same editor instance. Geometry is stored
                // normalized to the canvas size so it survives a different image being loaded next time.
                var captureState = function () {
                    if (!settings.rememberCropPosition) {
                        return;
                    }

                    var selection = getSelection();
                    var canvas = getCanvas();

                    if (!selection || !canvas) {
                        return;
                    }

                    var canvasWidth = canvas.offsetWidth;
                    var canvasHeight = canvas.offsetHeight;

                    if (canvasWidth > 0 && canvasHeight > 0 && selection.width > 0 && selection.height > 0) {
                        editor._lastSelection = {
                            centerX: (selection.x + selection.width * 0.5) / canvasWidth,
                            centerY: (selection.y + selection.height * 0.5) / canvasHeight,
                            width: selection.width / canvasWidth,
                            height: selection.height / canvasHeight,
                            aspectRatio: selection.aspectRatio
                        };

                        log('captureState', editor._lastSelection);
                    } else {
                        log('captureState: skipped (non-positive canvas/selection)', {
                            canvasWidth: canvasWidth, canvasHeight: canvasHeight,
                            selectionWidth: selection.width, selectionHeight: selection.height
                        });
                    }
                };

                var restoreState = function () {
                    if (!settings.rememberCropPosition || !editor._lastSelection) {
                        return;
                    }

                    var selection = getSelection();
                    var canvas = getCanvas();

                    if (!selection || !canvas || typeof selection.$change !== 'function') {
                        return;
                    }

                    var canvasWidth = canvas.offsetWidth;
                    var canvasHeight = canvas.offsetHeight;

                    if (canvasWidth <= 0 || canvasHeight <= 0) {
                        return;
                    }

                    var state = editor._lastSelection;
                    var width = state.width * canvasWidth;
                    var height = state.height * canvasHeight;
                    var x = state.centerX * canvasWidth - width * 0.5;
                    var y = state.centerY * canvasHeight - height * 0.5;

                    if (typeof state.aspectRatio === 'number' && !isNaN(state.aspectRatio)) {
                        selection.aspectRatio = state.aspectRatio;
                    }

                    log('restoreState', { x: x, y: y, width: width, height: height, aspectRatio: state.aspectRatio });

                    selection.$change(x, y, width, height);
                };

                var setActiveAspect = function (button) {
                    aspectButtons.forEach(function (entry) {
                        var isActive = entry.button === button;

                        entry.button.classList.toggle('is-active', isActive);
                        entry.button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    });
                };

                var applyAspectRatio = function (value, button) {
                    log('applyAspectRatio', { value: value, label: button ? button.textContent : null });

                    var selection = getSelection();

                    if (!selection) {
                        log('applyAspectRatio: no selection');
                        return;
                    }

                    selection.aspectRatio = value && value > 0 ? value : NaN;

                    if (typeof selection.$initSelection === 'function') {
                        selection.$initSelection(true, true);
                    }

                    setActiveAspect(button || null);
                };

                var zoomBy = function (step) {
                    log('zoomBy', { step: step });

                    var cropperImage = getImage();

                    if (cropperImage && typeof cropperImage.$zoom === 'function') {
                        cropperImage.$zoom(step);
                    } else {
                        log('zoomBy: no cropper image / $zoom unavailable');
                    }
                };

                var reset = function () {
                    log('reset');

                    var cropperImage = getImage();

                    if (cropperImage) {
                        if (typeof cropperImage.$resetTransform === 'function') {
                            cropperImage.$resetTransform();
                        }

                        if (typeof cropperImage.$center === 'function') {
                            cropperImage.$center('contain');
                        }
                    }

                    var initialButton = aspectButtons.length ? aspectButtons[0] : null;

                    aspectButtons.some(function (entry) {
                        if (entry.value === initialAspectRatio) {
                            initialButton = entry;
                            return true;
                        }

                        return false;
                    });

                    if (initialButton) {
                        applyAspectRatio(initialButton.value, initialButton.button);
                    } else {
                        applyAspectRatio(initialAspectRatio, null);
                    }
                };

                var initializeCropper = function () {
                    var Cropper = getCropperConstructor();
                    var cropperOptions = Object.assign({}, settings.cropperOptions || {});

                    if (!Cropper || cropper) {
                        log('initializeCropper: skipped', { hasCropperConstructor: !!Cropper, alreadyInitialized: !!cropper });
                        return;
                    }

                    if (!cropperOptions.template) {
                        cropperOptions.template = createTemplate(initialAspectRatio);
                    }

                    log('initializeCropper', {
                        imageNatural: { width: image.naturalWidth, height: image.naturalHeight },
                        initialAspectRatio: initialAspectRatio
                    });

                    cropper = new Cropper(image, cropperOptions);

                    var cropperImage = getImage();

                    if (cropperImage && typeof cropperImage.$ready === 'function') {
                        cropperImage.$ready(function () {
                            log('cropper ready');

                            if (typeof cropperImage.$center === 'function') {
                                cropperImage.$center('contain');
                            }

                            restoreState();
                        });
                    } else {
                        log('initializeCropper: cropper image / $ready unavailable', { hasCropperImage: !!cropperImage });
                    }

                    if (initialAspectRatio) {
                        aspectButtons.some(function (entry) {
                            if (entry.value === initialAspectRatio) {
                                setActiveAspect(entry.button);
                                return true;
                            }

                            return false;
                        });
                    } else if (aspectButtons.length) {
                        setActiveAspect(aspectButtons[0].button);
                    }
                };

                var close = function () {
                    if (closed) {
                        return;
                    }

                    log('close');

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
                    log('cancel');

                    captureState();

                    if (typeof editor.oncancel === 'function') {
                        editor.oncancel();
                    }

                    close();
                };

                var zoomOutButton = createIconButton(
                    'filepond-cropper__tool',
                    ICONS.zoomOut,
                    settings.zoomOutLabel || 'Zoom out'
                );
                var zoomInButton = createIconButton(
                    'filepond-cropper__tool',
                    ICONS.zoomIn,
                    settings.zoomInLabel || 'Zoom in'
                );
                var resetButton = createIconButton(
                    'filepond-cropper__tool',
                    ICONS.reset,
                    settings.resetLabel || 'Reset'
                );

                toolbar.appendChild(zoomOutButton);
                toolbar.appendChild(zoomInButton);
                toolbar.appendChild(resetButton);

                if (aspectRatioList.length) {
                    var aspectGroup = createElement('div', 'filepond-cropper__aspects');

                    aspectRatioList.forEach(function (item) {
                        var button = createElement('button', 'filepond-cropper__aspect', item.label);

                        button.type = 'button';
                        button.setAttribute('aria-pressed', 'false');
                        aspectButtons.push({ button: button, value: item.value });
                        button.addEventListener('click', function () {
                            applyAspectRatio(item.value, button);
                        });
                        aspectGroup.appendChild(button);
                    });

                    toolbar.appendChild(aspectGroup);
                }

                cancelButton.type = 'button';
                confirmButton.type = 'button';
                image.alt = '';
                overlay.tabIndex = -1;

                image.addEventListener('load', function () {
                    log('image load', { naturalWidth: image.naturalWidth, naturalHeight: image.naturalHeight });
                    initializeCropper();
                }, { once: true });
                image.addEventListener('error', function (event) {
                    log('image load error', event);
                });

                actions.appendChild(cancelButton);
                actions.appendChild(confirmButton);
                footer.appendChild(toolbar);
                footer.appendChild(actions);
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

                zoomOutButton.addEventListener('click', function () {
                    zoomBy(-0.1);
                });
                zoomInButton.addEventListener('click', function () {
                    zoomBy(0.1);
                });
                resetButton.addEventListener('click', reset);
                cancelButton.addEventListener('click', cancel);
                // Permanent modal: dismiss only via the Cancel/Confirm buttons. Backdrop clicks and the
                // Escape key are intentionally ignored so an accidental click outside — or a crop drag
                // that ends over the backdrop — never closes the dialog and discards the user's edit.
                confirmButton.addEventListener('click', function () {
                    log('confirm: clicked');

                    var selection = getSelection();

                    if (!selection || !selection.$toCanvas) {
                        log('confirm: aborted (no selection / $toCanvas unavailable)');
                        return;
                    }

                    confirmButton.disabled = true;
                    captureState();

                    selection.$toCanvas().then(function (canvas) {
                        log('confirm: $toCanvas resolved', {
                            canvas: { width: canvas.width, height: canvas.height },
                            image: { naturalWidth: image ? image.naturalWidth : null, naturalHeight: image ? image.naturalHeight : null }
                        });

                        var data = buildCropData(cropper, selection, canvas, image);

                        log('confirm: dispatching onconfirm', { data: data });

                        if (typeof editor.onconfirm === 'function') {
                            editor.onconfirm({ data: data });
                        }

                        close();
                    }).catch(function (error) {
                        log('confirm: $toCanvas error', error);
                        confirmButton.disabled = false;
                    });
                });
            }
        };

        return editor;
    };
})(window, document);
