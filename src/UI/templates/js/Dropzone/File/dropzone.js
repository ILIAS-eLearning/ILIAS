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
         * Holds a list of css classes used within this script.
         * @type {{}}
         */
        const CSS = {
            highlight: 'highlight',
            highlight_current: 'highlight-current',
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
         * Keeps track of the amount of 'dragenter' events that got fired.
         * @see https://stackoverflow.com/a/21002544
         * @type {number}
         */
        let drag_enter_counter = 0;

        /**
         * @param {string} dropzone_id
         */
        let init = function (dropzone_id) {
            if (typeof dropzones[dropzone_id] !== 'undefined') {
                console.error(`Error: tried initializing dropzone '${dropzone_id}' twice.`);
                return;
            }

            let dropzone = $(`#${dropzone_id}`);
            dropzones[dropzone_id] = {
                file_input_id: dropzone.find(SELECTOR.file_input).attr('id'),
            };

            adjustModalContentStyles(dropzone);
            initDropzoneEventListeners(dropzone);
            initGlobalEventListeners();
        }

        /**
         * @param {jQuery} dropzone
         */
        let initDropzoneEventListeners = function (dropzone) {
            dropzone.on({
                dragover: highlightCurrentDropzoneHook,
                dragenter: highlightCurrentDropzoneHook,
                dragleave: removeHighlightFromDropzoneHook,
                drop: transferDroppedFilesHook,
            });
        }

        let initGlobalEventListeners = function () {
            if (!instantiated) {
                $(document).on({
                    dragenter: highlightPossibleDropzones,
                    dragleave: removeHighlightFromDropzones,
                    drop: removeHighlightFromDropzones,
                });

                instantiated = true;
            }
        }

        /**
         * @param {Event} event
         */
        let transferDroppedFilesHook = function (event) {
            removeHighlightFromDropzoneHook(event);
            removeHighlightFromDropzones(event);

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
        let highlightCurrentDropzoneHook = function (event) {
            event.preventDefault();
            let current_dropzone = $(event.currentTarget);
            current_dropzone.addClass(CSS.highlight_current);
        }

        /**
         * @param {Event} event
         */
        let removeHighlightFromDropzoneHook = function (event) {
            let current_dropzone = $(event.currentTarget);
            current_dropzone.removeClass(CSS.highlight_current);
        }

        /**
         * @param {Event} event
         */
        let highlightPossibleDropzones = function (event) {
            disableDefaultEventBehaviour(event);
            drag_enter_counter++;

            let dropzones = $(document).find(SELECTOR.dropzone);
            let dropzone_count = dropzones.length;
            if (0 < dropzone_count) {
                for (let i = 0; i < dropzone_count; i++) {
                    $(dropzones[i]).addClass(CSS.highlight);
                }
            }
        }

        /**
         * @param {Event} event
         */
        let removeHighlightFromDropzones = function (event) {
            disableDefaultEventBehaviour(event);
            drag_enter_counter--;

            if (0 === drag_enter_counter) {
                let dropzones = $(document).find(SELECTOR.dropzone);
                let dropzone_count = dropzones.length;
                if (0 < dropzone_count) {
                    for (let i = 0; i < dropzone_count; i++) {
                        $(dropzones[i]).removeClass(CSS.highlight);
                    }
                }
            }
        }

        /**
         * @param {jQuery} dropzone
         */
        let adjustModalContentStyles = function (dropzone) {
            // remove the first form-group column (labels) and
            // expand the content column to the width of the modal.
            dropzone.find('.form-group .col-sm-3').css('display', 'none');
            dropzone.find('.form-group .col-sm-9').css('width', '100%');
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