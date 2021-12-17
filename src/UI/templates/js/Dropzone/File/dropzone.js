/**
 * this script is responsible for the dropzone highlighting and
 * file processing.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */

var il = il || {};
il.UI = il.UI || {};
(function ($, UI) {
    UI.Dropzone = (function ($) {
        /**
         * Contains a list of selectors used throughout this script.
         * @type {{}}
         */
        const SELECTOR = {
            dropzone: '.ui-dropzone',
            dropzone_container: '.ui-dropzone-container',
            file_input: '.ui-input-file',
        };

        /**
         * Holds whether the global event listeners were added or not.
         * @type {boolean}
         */
        let instantiated = false;

        /**
         * Holds all dropzone instances of the current page.
         * @type {*[]}
         */
        let dropzones = [];

        /**
         * @param {string} dropzone_id
         */
        let init = function (dropzone_id) {
            if (typeof dropzones[dropzone_id] !== 'undefined') {
                console.error(`Error: tried initializing dropzone '${dropzone_id}' twice.`);
                return;
            }

            dropzones[dropzone_id] = {
                file_input_id: $(`#${dropzone_id}`).find(SELECTOR.file_input).attr('id'),
            };

            initDropzoneEventListeners(dropzone_id);
            initGlobalEventListeners();
        }

        /**
         * @param {string} dropzone_id
         */
        let initDropzoneEventListeners = function (dropzone_id) {
            $(`#${dropzone_id}`).on('drop', transferDroppedFilesHook);
        }

        let initGlobalEventListeners = function () {
            if (!instantiated) {
                $(document).on({
                    dragover: disableDefaultEventBehaviour,
                    drop: disableDefaultEventBehaviour,
                });

                instantiated = true;
            }
        }

        /**
         * @param {Event} event
         */
        let transferDroppedFilesHook = function (event) {
            // prevent default drop behaviour.
            disableDefaultEventBehaviour(event);

            // dataTransfer has to be fetched by triggering event (DragEvent).
            // there's also a console bug where dataTransfer will be shown
            // empty in the console (in case you're debugging this).
            let data_transfer = event.originalEvent.dataTransfer;
            let file_count = data_transfer.files.length;
            if (0 < file_count) {
                for (let i = 0; i < file_count; i++) {
                    il.UI.Input.File.renderFileEntry(
                        data_transfer.files[i],
                        dropzones[$(this).attr('id')].file_input_id
                    );
                }
            }
        }

        /**
         * @param {Event} event
         */
        let disableDefaultEventBehaviour = function (event) {
            event.stopPropagation();
            event.preventDefault();
        }

        return {
            init: init,
        }
    })($);
})($, il.UI);