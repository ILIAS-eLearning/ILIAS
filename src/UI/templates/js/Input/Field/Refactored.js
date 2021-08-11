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

    /**
     * Public interface of a file-input element.
     *
     * @type {{init: init, renderFileListEntry: renderFileListEntry}}
     */
    il.UI.Input.file = (function ($) {

        /**
         * Constant to enable or disable the debugging of a file-input
         *
         * @type {boolean}
         */
        const DEBUG = true;

        /**
         * Default settings used for dropzone.js initialization.
         *
         * @type {object}
         */
        const DEFAULT_SETTINGS = {
            file_input_name:    '',
            file_upload_url:    '',
            file_removal_url:   '',
            file_info_url:      '',
            file_identifier:    'file_id',
            max_file_amount:    1,
            file_mime_types:    null,
            existing_file_info: null,
            max_file_size:      null,
            with_metadata:      false,
        };

        /**
         * Default file-input translation messages.
         *
         * @type {object}
         */
        const DEFAULT_TRANSLATIONS = {
            msg_invalid_mime:   'Type of uploaded file(s) is not supported.',
            msg_invalid_amount: 'Too many files were uploaded at once.',
            msg_invalid_size:   'Max file-size exceeded, upload aborted.',
            msg_upload_failure: 'Something went wrong when uploading...',
            msg_upload_success: 'File(s) successfully uploaded.',
        };

        /**
         * Selectors used for DOM manipulations within a file-input element.
         *
         * @type {object}
         */
        const SELECTOR = {
            dropzone:           '.il-file-input-dropzone',
            action_btn:         '.il-file-input-dropzone button',
            submit_btn:         '.il-standard-form-cmd > button',
            file_list:          '.il-file-input-list',
            file_preview:       '.il-file-input-preview',
            file_input:         '.il-file-input-template',
            file_metadata:      '.il-file-input-metadata',
            file_toggle:        '.il-file-input-preview .metadata-toggle',
            file_removal:       '.il-file-input-preview .remove',
            file_error_msg:     '.il-file-input-upload-error',
            darkener:           '.il-file-input-darkener',
            glyph:              '.metadata-toggle .glyph',
        };

        /**
         * Holds the initialized file-input dropzone.
         *
         * @type {Dropzone}
         */
        let dropzone = {};

        /**
         * File-input settings used initialization.
         *
         * @type {object}
         */
        let settings = {};

        /**
         * File-input translations used within this element.
         *
         * @type {object}
         */
        let translations = {};

        /**
         * Helper function to debug a file-input.
         *
         * @param {*} variables
         */
        let debug = function (...variables) {
            if (DEBUG) {
                for (let i in variables) {
                    console.log(variables[i]);
                }
            }
        };

        /**
         * Handles files added by dropzone.js, adjusts the files preview element.
         *
         * @param {File} file
         */
        let addFileHook = function (file) {
            // adjust file-input value.
            $(file.previewElement).find(SELECTOR.file_input).val(file.file_id);

            // adjust metadata inputs to be accessible by file-id
            // on the server side.
            if (settings.with_metadata) {
                $(file.previewElement)
                    .find(SELECTOR.file_metadata)
                    .each(function (i, input) {
                        input = $(input).find('input');
                        // @TODO: either we can solve naming issue server-side or we have
                        //        to figure out how to structure the input names here.
                        // input.attr('name', `${settings.file_input_name}[${file.file_id}][]`);
                    })
                ;
            }

            debug(file);
        };

        /**
         * Removes files from the server that were removed
         * from the dropzone.js file-list.
         *
         * @param {File} file
         */
        let removeFileHook = async function (file) {
            // if the file-status is not successful, the file doesn't
            // need to be removed.
            if ('success' === file.status) {
                await $.ajax({
                    type: 'GET',
                    url:  settings.file_removal_url,
                    data: {
                        [settings.file_identifier]: file.file_id
                    },
                    success: function(response) {
                        response = Object.assign(JSON.parse(response));
                        if (1 !== response.status) {
                            uploadFailureHook(file, response.message, response);
                        }
                    },
                    error: function(response) {
                        uploadFailureHook(file, `Failed to remove file: ${file.file_id}`, response);
                    }
                });
            }

            debug(file);
        };

        /**
         * Handles the successful upload of a file by dropzone.js.
         *
         * @param {File}   file
         * @param {string} json_response
         */
        let uploadSuccessHook = function (file, json_response) {
            let response = Object.assign(JSON.parse(json_response));
            if (1 === response.status) {
                // override dropzone.js file-id with IRSS file-id.
                file.file_id = response[settings.file_identifier];
                addFileHook(file);
            } else {
                uploadFailureHook(file, response.message, response);
            }

            debug(file, response);
        };

        /**
         * Handles the unsuccessful upload of a file by dropzone.js.
         *
         * @param {File}   file
         * @param {string} message
         * @param {xhr}    response
         */
        let uploadFailureHook = function (file, message, response) {
            // give feedback to user (highlight list-entry red, show message).
            $(file.previewElement).addClass('alert-danger');
            $(file.previewElement).find(SELECTOR.file_error_msg).text(message);

            debug(file, message, response);
        };

        /**
         * Handles the form-submission of the closest form to file-input.
         *
         * @param {Event} event
         */
        let formSubmissionHook = function (event) {

            // currently unused, maybe do some stuff with
            // the metadata input?
        };

        /**
         * Disables ALL submit-buttons during the upload process.
         *
         * @param {File} current_file
         */
        let processFileHook = function (current_file) {
            $(document)
                .find(SELECTOR.submit_btn)
                .each(function() {
                    $(this).attr('disabled', true);
                })
            ;
        };

        /**
         * Enables ALL submit-buttons after files from the queue
         * were processed.
         */
        let finishQueueHook = function () {
            $(document)
                .find(SELECTOR.submit_btn)
                .each(function() {
                    $(this).attr('disabled', false);
                })
            ;
        };

        /**
         * Toggles the state of each list-entry's metadata inputs section.
         *
         * @param {Event} event
         */
        let toggleMetadataHook = function (event) {
            if (settings.with_metadata) {
                $(this)
                    .parent()
                    .parent()
                    .find(SELECTOR.file_metadata)
                    .toggle()
                ;

                $(this)
                    .parent()
                    .find(SELECTOR.glyph)
                    .each(function () {
                        $(this).toggle();
                    }
                );
            }
        };

        /**
         *
         */
        let loadExistingFiles = function () {
            if (null !== settings.existing_file_info) {
                settings.existing_file_info.forEach(function (file_info) {
                    // @TODO: how can we fetch metadata input values? we could use a further
                    //        setting with an URL that can be used as metadata value source.

                    // emit a dropped file to dropzone.js
                    file_info.accepted = true;
                    file_info.is_existing = true;
                    dropzone.files.push(file_info);
                    dropzone.emit('addedfile', file_info);
                    dropzone._updateMaxFilesReachedClass();
                });
            }
        };

        /**
         * Returns the prepared file previews HTML.
         *
         * @param {string} id
         * @return {string}
         */
        let getPreparedFilePreview = function (id) {
            let preview  = $(`#${id} ${SELECTOR.file_preview}`);
            let metadata = $(SELECTOR.file_preview).find(SELECTOR.metadata);

            if (settings.with_metadata) {
                // if metadata inputs were provided, the toggle is set up.
                preview.find(SELECTOR.glyph + ':first').hide();
            } else {
                // if no metadata inputs were provided, the toggle and
                // the container are removed.
                preview.find(SELECTOR.glyph).remove();
                metadata.remove();
            }

            // remove initial preview HTML from the page.
            if (!DEBUG) {
                preview.remove();
            }

            return preview.html();
        };

        /**
         * Helper function to manage the event-listeners of a file-input element.
         */
        let initEventListeners = function () {
            // general event-listeners
            $(SELECTOR.dropzone).closest('form').on('click', SELECTOR.submit_btn, formSubmissionHook);
            $(SELECTOR.file_list).on('click', SELECTOR.glyph, toggleMetadataHook);

            // dropzone.js event-listeners
            dropzone.on('queuecomplete', finishQueueHook);
            dropzone.on('processing', processFileHook)
            dropzone.on('removedfile', removeFileHook);
            dropzone.on('fileadded', addFileHook);
            dropzone.on('success', uploadSuccessHook);
            dropzone.on('error', uploadFailureHook);
        };

        /**
         * Initializes a file-input element.
         *
         * @param {string} id
         * @param {string} json_settings
         */
        let init = function (id, json_settings) {
            // parse json settings to object and override defaults.
            settings = Object.assign(DEFAULT_SETTINGS, JSON.parse(json_settings));
            debug(settings);

            // parse translations given by json settings and override defaults.
            translations = Object.assign(DEFAULT_TRANSLATIONS, settings.translations);
            debug(translations);

            // file list and action button must be fetched with vanilla js in
            // order to work properly with dropzone.js.
            let file_list  = document.querySelector(`#${id} ${SELECTOR.file_list}`);
            let action_btn = document.querySelector(`#${id} ${SELECTOR.action_btn}`);
            debug(file_list, action_btn);

            // initialize the dropzone.js element.
            dropzone = new Dropzone(`#${id} ${SELECTOR.dropzone}`, {
                url:                encodeURI(settings.file_upload_url),
                uploadMultiple:     (1 < settings.max_file_amount),
                maxFiles:           settings.max_file_amount,
                maxFileSize:        settings.max_file_size,
                acceptedFiles:      settings.file_mime_types,
                previewTemplate:    getPreparedFilePreview(id),
                previewsContainer:  file_list,
                clickable:          action_btn,
                parallelUploads:    1, // maybe allow more?
            });

            initEventListeners();
            loadExistingFiles();

            debug(dropzone);
        };

        /**
         * Renders an existing file-preview within a file-input.
         *
         * @param {string} file_input_id
         * @param {string} file_id
         */
        let renderFileListEntry = function (file_input_id, file_id) {

        };

        return {
            renderFileListEntry: renderFileListEntry,
            init: init,
        };

    })($);
})($, il.UI);
