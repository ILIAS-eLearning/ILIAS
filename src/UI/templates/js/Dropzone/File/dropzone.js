/**
 * dropzone.js
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This script manages files dropped onto a Wrapper component:
 * \ILIAS\UI\Implementation\Component\Dropzone\File\Wrapper.
 */

var il = il || {};
il.UI = il.UI || {};
il.UI.Dropzone = il.UI.Dropzone || {};

(function ($, UI) {

    /**
     * Public interface of a Wrapper component.
     *
     * @type {{init: init}}
     */
    il.UI.Dropzone.wrapper = (function ($) {

        /**
         * Enables or disables the debugging of this component.
         *
         * @type {boolean}
         */
        const DEBUG = false;

        /**
         * Holds the query selectors needed within this component.
         *
         * @type {object}
         */
        const SELECTOR = {
            darkener:           '.il-dropzone-page-darkener',
            modal:              '.il-modal-roundtrip',
            dropzone:           '.il-dropzone',
            former_dropzone:    '.il-former-dropzone',
            file_input:         '.il-file-input',
            file_dropzone:      '.il-file-input',
        };

        /**
         * Holds the CSS classes used for DOM manipulations of this component.
         *
         * @type {object}
         */
        const CSS = {
            darkened_background:         'modal-backdrop in',
            darkened_dropzone_highlight: 'darkened-highlight',
            default_dropzone_highlight:  'default-highlight',
            dropzone_drag_over:          'drag-hover',
        };

        /**
         * Keeps track of the components Dragster state.
         *
         * @type {boolean}
         */
        let dragster_active = false;

        /**
         * Helper function used for debugging.
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
         * disables the hovering highlight
         *
         * @param dragster_event
         * @param event
         */
        let disableHighlightHoverHook = function (dragster_event, event) {

            // in case the file was NOT dropped, the propagation of this event
            // must be stopped, to prevent document.dragLeave to trigger.
            if ('drop' !== event.type) {
                dragster_event.stopPropagation();
                event.stopPropagation();
            }

            $(dragster_event.target).removeClass(CSS.dropzone_drag_over);
            debug("Disable hover-highlight");
            enableHighlightHook();
        }

        /**
         * enables the hovering highlight
         *
         * @param {Event} dragster_event
         */
        let enableHighlightHoverHook = function (dragster_event) {
            $(dragster_event.target).addClass(CSS.dropzone_drag_over);
            debug("Enable hover-highlight");
        };

        /**
         * disables the darkener and all dropzone highlights.
         */
        let disableHighlightHook = function () {
            $(SELECTOR.darkener).removeClass(CSS.darkened_background);
            $(SELECTOR.dropzone).removeClass(CSS.darkened_dropzone_highlight);
            debug("Disable highlight");
        };

        /**
         * enables the darkener and highlights all dropzones.
         */
        let enableHighlightHook = function () {
            $(SELECTOR.darkener).addClass(CSS.darkened_background);
            $(SELECTOR.dropzone).addClass(CSS.darkened_dropzone_highlight);
            debug("Enable highlight");
        };

        /**
         * Manages dropped files onto a dropzone wrapper component.
         *
         * @param {Event} event
         */
        let processDroppedFilesHook = function (event) {
            event.preventDefault();

            // dataTransfer has to be fetched by triggering event (DragEvent).
            // there's also a console bug where dataTransfer will always be
            // shown empty, just in case you're debugging it.
            let data_transfer = event.originalEvent.dataTransfer;
            debug("Dropped files:", data_transfer.files);

            let file_input_id = $(this).find(SELECTOR.file_dropzone).attr('id');
            debug(file_input_id);

            for (let i = 0; i < data_transfer.files.length; i++) {
                il.UI.Input.file.renderFileListEntry(
                    file_input_id,
                    data_transfer.files[i]
                );
            }
        };

        /**
         * Moves a dropzone-area to the modal it has been rendered with.
         *
         * @param {Event} event
         * @param {*}     data
         */
        let moveDropzoneAreaHook = function (event, data) {
            let trigger  = $(event.target);
            let dropzone = trigger.find(SELECTOR.dropzone);
            let modal    = trigger.find(SELECTOR.modal);

            dropzone.removeClass(SELECTOR.dropzone);
            dropzone.addClass(SELECTOR.former_dropzone);
            modal.addClass(SELECTOR.dropzone);

            initDragster(true);
        };

        /**
         * Helper function to initialize the pages dragster.
         *
         * @param {boolean} force_init
         */
        let initDragster = function (force_init = false) {
            // only initialize dragster once, as it can be shared across
            // multiple instances of this component.
            if (!dragster_active || force_init) {
                // add a darkener element to DOM for dragster events (substr removes the dot).
                $('body').prepend(`<div class="${SELECTOR.darkener.substr(1)}"></div>`);

                $(SELECTOR.dropzone).dragster({
                    enter: enableHighlightHoverHook,
                    leave: disableHighlightHoverHook,
                    drop:  disableHighlightHoverHook,
                });

                // @TODO: fix document.dragLeave after file was hovering dropzone-wrapper once.
                $(document).dragster({
                    enter: enableHighlightHook,
                    leave: disableHighlightHook,
                    drop:  disableHighlightHook,
                });

                dragster_active = true;
            }
        }

        /**
         * Initializes a Wrapper component.
         *
         * @param {string} id
         * @param {string} json_settings
         */
        let init = function (id, json_settings) {
            let settings = Object.assign(JSON.parse(json_settings));

            $(document).on(settings.modal_show_signal, moveDropzoneAreaHook);

            // register event-listener for dropped files
            let dropzone = $(`#${id} ${SELECTOR.dropzone}`);
            dropzone.on('drop', processDroppedFilesHook);

            initDragster();
        };

        return {
            init: init
        };
    })($);
})($, il.UI);