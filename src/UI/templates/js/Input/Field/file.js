/**
 * file.js
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This script wraps the Dropzone.js library for the UI Component
 * \ILIAS\UI\Implementation\Component\Input\Field.
 */

var il = il || {};
il.UI = il.UI || {};
il.UI.Input = il.UI.Input || {};

Dropzone.autoDiscover = false;

(function ($, UI) {

    il.UI.Input.file = (function ($) {

        /**
         * @type {boolean}
         */
        const DEBUG = true;

        /**
         * contains a list of all selectors used for DOM manipulations.
         *
         * @type {Object}
         */
        const SELECTOR = {
            dropzone:       '.il-dropzone',
            darkener:       '.il-dropzone-darkened',
            clickable:      '.il-dropzone-clickable-container .btn',
            file_list:      '.il-dropzone-file-list',
            file_preview:   '.il-dropzone-file-preview',
            file_input:     '.il-dropzone-file-input',
            error_msg:      '.il-dropzone-file-upload-error span',
            remove_btn:     '.il-dropzone-file-info-close button',
            metadata:       '.il-dropzone-file-list .metadata',
            progress:       '.progress',
            glyph:          '.toggle .glyph',
        };

        /**
         * contains a list of all css-classes used by DOM manipulations.
         *
         * @type {Object}
         */
        const CSS = {
            darkened_background:         "modal-backdrop in",
            darkened_dropzone_highlight: "darkened-highlight",
            default_dropzone_highlight:  "default-highlight",
            dropzone_drag_over:          "drag-hover",
        };


        /**
         * @type {Object}
         */
        const SETTINGS = {
            upload_url:     '',
            removal_url:    '',
            info_url:       '',
            file_types:     {},
            max_files:      1,
            identifier:     'file_id',
            type_error:     'Invalid filetype',
        };

        /**
         * @type {Dropzone}
         */
        let dropzone = {};

        /**
         * @type {Object}
         */
        let settings = {};

        /**
         * logs one or many variables to the console if debug is enabled.
         *
         * @param variables
         */
        let debug = function (...variables) {
            if (DEBUG) {
                for (let i in variables) {
                    console.log(variables[i]);
                }
            }
        }

        /**
         * cuts an HTML element from DOM and returns it.
         *
         * @param {string} id
         * @param {string} template
         * @returns {*|jQuery|!jQuery}
         */
        let cutTemplateFromDOM = function (id, template) {

            let template_id    = `#${id} ${template}`,
                template_clone = $(template_id).clone()
            ;

            // remove initial HTML from DOM
            $(template_id).remove();

            return template_clone;
        }

        /**
         * initializes a dropzone component.
         *
         * @param {string} id               unique dropzone id
         * @param {string} json_Settings    json settings string
         */
        let init = function (id, json_Settings) {

            // parse settings-string to object and apply defaults if a value isn't provided
            settings = Object.assign(SETTINGS, JSON.parse(json_Settings));
            debug(settings);

            // get plain JS objects for dropzone.js
            let file_list = document.querySelector(`#${id} ${SELECTOR.file_list}`),
                clickable = document.querySelector(`#${id} ${SELECTOR.clickable}`)
            ;

            debug(file_list, clickable);

            // initialize dropzone.js object
            dropzone = new Dropzone(`#${id} ${SELECTOR.dropzone}`, {
                method:                'post',
                url:                   encodeURI(settings.upload_url),
                maxFiles:              settings.max_files,
                acceptedFiles:         settings.file_types,
                dictInvalidFileType:   settings.type_error,
                previewTemplate:       $(`#${id} ${SELECTOR.file_list}`).html(),
                previewsContainer:     file_list,
                clickable:             clickable,
                dictDefaultMessage:    '',
                autoProcessQueue:      true,
                createImageThumbnails: true,
                uploadMultiple:        false,
                parallelUploads:       1,
            });

            debug(dropzone);

            // cut template elements from DOM
            cutTemplateFromDOM(id, SELECTOR.file_preview);
            cutTemplateFromDOM(id, SELECTOR.file_input);

            initEventListeners();
            initDragster();
        };

        /**
         * helper function to manage all event-listeners of dropzone.js object
         */
        let initEventListeners = function () {

            dropzone.on('uploadprogress', uploadProgressHook);
            dropzone.on('removedfile', removedFileHook);
            dropzone.on('success', successHook);
            dropzone.on('error', errorHook);
        };

        /**
         * updates the file previews progress bar periodically.
         *
         * @param file
         * @param progress
         * @param bytes
         */
        let uploadProgressHook = function (file, progress, bytes) {

            if (file.previewElement) {
                let progress_bar = $(file.previewElement).find('[data-dz-uploadprogress]');
                progress_bar.css('width', progress + '%');
                progress_bar.attr('aria-valuenow', progress);
                debug(file, progress, bytes);
            }
        }

        /**
         * updates the file id to the one returned by the IRSS (upload url).
         * if the upload wasn't successful errorHook gets called.
         *
         * @param file
         * @param response
         */
        let successHook = function (file, response) {

            response = Object.assign(JSON.parse(response));
            if (1 === response.status) {
                file.file_id = response[settings.identifier];
                $(file.previewElement).addClass('alert-success');
                $(file.previewElement).find(SELECTOR.progress).remove();
                debug("file upload successful, new id: " + file.file_id);
            } else {
                errorHook(file, response.message, response);
            }
        };

        /**
         * changes the files preview element class and updates the error-message.
         *
         * @param file
         * @param {string} error
         * @param response
         */
        let errorHook = function (file, error, response) {

            $(file.previewElement).addClass('alert-danger');
            $(file.previewElement).find(SELECTOR.error_msg).text(error);

            debug(file, error, response);
        };

        /**
         * calls the configured removal URL to delete the file from the IRSS
         * if it has been processed.
         *
         * @param file
         */
        let removedFileHook = async function (file) {

            if  ('success' === file.status) {
                await $.ajax({
                    type: 'GET',
                    url: settings.removal_url,
                    data: { [settings.identifier]: file.file_id },
                    success: function(response) {
                        response = Object.assign(JSON.parse(response));
                        if (1 !== response.status) {
                            errorHook(file, response.message, response);
                        }
                    },
                    error: function(response) {
                        errorHook(file, "failed to call removal URL", response);
                    }
                });
            }
        }

        /**
         * helper function to initialise dragster and register event-listeners.
         */
        let initDragster = function () {

            // add a darkener element to DOM for dragster events (substr removes '.')
            $('body').prepend(`<div class="${SELECTOR.darkener.substr(1)}"></div>`);

            $(SELECTOR.dropzone).dragster({
                enter: enableHighlightHoverHook,
                leave: disableHighlightHoverHook,
                drop:  disableHighlightHoverHook,
            });

            $(document).dragster({
                enter: enableHighlightHook,
                leave: disableHighlightHook,
                drop:  disableHighlightHook,
            });
        };

        /**
         * disables the hovering highlight
         *
         * @param dragster_event
         * @param event
         */
        let disableHighlightHoverHook = function (dragster_event, event) {
            if ('drop' !== event.type) {
                // prevent further event-listeners to be triggered by this event (document.dragleave)
                dragster_event.stopPropagation();
                event.stopPropagation();
            }

            $(dragster_event.target).removeClass(CSS.dropzone_drag_over);
            enableHighlightHook();
        }

        /**
         * enables the hovering highlight
         *
         * @param dragster_event
         */
        let enableHighlightHoverHook = function (dragster_event) {
            $(dragster_event.target).addClass(CSS.dropzone_drag_over);
        };

        /**
         * disables the darkener and all dropzone highlights.
         */
        let disableHighlightHook = function () {
            $(SELECTOR.darkener).removeClass(CSS.darkened_background);
            $(SELECTOR.dropzone).removeClass(CSS.darkened_dropzone_highlight);
        };

        /**
         * enables the darkener and highlights all dropzones.
         */
        let enableHighlightHook = function () {
            $(SELECTOR.darkener).addClass(CSS.darkened_background);
            $(SELECTOR.dropzone).addClass(CSS.darkened_dropzone_highlight);
        };

        /**
         * @TODO: implement this render trigger.
         *
         * @param id
         */
        let toBeImplemented = function(id) {};

        return {
            init: init,
            renderMetadataInputs: toBeImplemented,
        };

    })($);
})($, il.UI);
