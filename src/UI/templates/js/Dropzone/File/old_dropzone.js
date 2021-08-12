/**
 * Provides the behavior of all dropzone types.
 *
 * @author nmaerchy <nm@studer-raimann.ch>
 */

var il = il || {};
il.UI = il.UI || {};
(function ($, UI) {
    UI.dropzone = (function ($) {

        /**
         * Contains all css classes used for dropzone manipulation.
         * These classes MUST NOT have the . symbol at the beginning.
         */
        var CSS = {
            "darkenedBackground": "modal-backdrop in", // <- bootstrap classes, should not be changed
            "darkenedDropzoneHighlight": "darkened-highlight",
            "defaultDropzoneHighlight": "default-highlight",
            "dropzoneDragHover": "drag-hover"
        };

        /**
         * Contains all css selectors used for dropzone manipulation.
         * Selectors MUST be declared like in css.
         * e.g.
         *  .this-is-a-class
         *  #this-is-a-id
         */
        var SELECTOR = {
            "darkenedBackground": "#il-dropzone-darkened",
            "dropzones": ".il-dropzone"
        };

        /**
         * Contains all supported dropzone types.
         * The type MUST be equal to the full qualified class name used in php.
         * NOTE backslashes needs to be removed.
         * e.g. ILIAS\UI\Component\Dropzone\Standard -> Standard
         */
        var DROPZONE = {
            "standard": "Standard",
            "wrapper": "Wrapper"
        };

        var _darkenedBackground = false;

        /**
         * Initializes a dropzone depending on the passed in type with the passed in options.
         *
         * @param {string} type the type of the dropzone
         *                      MUST be the full qualified class name.
         * @param {Object} options possible settings for this dropzone
         */
        var initializeDropzone = function (type, options) {
            // console.log("INIT DROPZONE " + options.id);
            // disable default behavior of browsers for file drops
            $(document).on("dragenter dragstart dragend dragleave dragover drag drop", function (e) {
                e.preventDefault();
            });

            var settings = $.extend({
                // default settings
                registeredSignals: [],
                darkenedBackground: false,
                uploadUrl: '',
                uploadButton: null
            }, options);

            if (settings.id === undefined) {
                throw new Error("Missing dropzone id in parameter options: options.id not found");
            }

            switch (type) {
                case DROPZONE.standard:
                    _initStandardDropzone(settings);
                    break;
                case DROPZONE.wrapper:
                    _configureDarkenedBackground(true);
                    _initWrapperDropzone(settings);
                    break;
                default:
                    throw new Error("Unsupported dropzone type found: " + type);
            }

        };

        /**
         * Adds a html div to enable the darkened background, if the passed in argument is true.
         * Sets the state of the darkened background availability to the value of the passed in argument.
         *
         * @param {boolean} darkenedBackground true, if the darkened background should be available
         *
         * @private
         */
        var _configureDarkenedBackground = function (darkenedBackground) {
            _darkenedBackground = darkenedBackground;
            if (!$(SELECTOR.darkenedBackground).length && darkenedBackground) {
                $("body").prepend("<div id=" + SELECTOR.darkenedBackground.substring(1) + "></div>"); // <- str.substring(1) removes the # symbol used in css
            }
        };

        /**
         * Enables the highlighting on all dropzones depending on the passed in argument.
         * Does NOT affect the highlighting of a single dropzone on drag hover.
         *
         * @param {boolean} darkenedBackground true to use the darkened background for highlighting, otherwise false
         *
         * @private
         */
        var _enableHighlighting = function (darkenedBackground) {
            if (darkenedBackground) {
                $(SELECTOR.darkenedBackground).addClass(CSS.darkenedBackground);
                $(SELECTOR.dropzones).addClass(CSS.darkenedDropzoneHighlight);
            } else {
                $(SELECTOR.dropzones).addClass(CSS.defaultDropzoneHighlight);
            }
        };

        /**
         * Disables the highlighting of all dropzones.
         * Does NOT affect the highlighting of a single dropzone on drag hover.
         *
         * @private
         */
        var _disableHighlighting = function () {
            $(SELECTOR.darkenedBackground).removeClass(CSS.darkenedBackground);
            $(SELECTOR.dropzones).removeClass(CSS.darkenedDropzoneHighlight)
                .removeClass(CSS.defaultDropzoneHighlight);
        };


        /**
         * @private functions to initialize different types of dropzones -----------------------------------
         *
         * Every dropzone MUST have its own init function (improves code readability).
         * The function for the appropriate dropzone is simply called in the switch statement
         * from the {@link initializeDropzone} function.
         */


        /**
         *
         * @param {Object} options possible settings for this dropzone
         *                         @see {@link initializeDropzone}
         *
         * @private
         */
        var _initStandardDropzone = function (options) {
            var $dropzone = $("#" + options.id).find(".il-dropzone");
            // Find the element acting as "Select Files" button/link
            var $selectFilesButton = $dropzone.find('.il-dropzone-standard-select-files-wrapper')
                .children('a');
            if ($selectFilesButton.length) {
                options.selectFilesButton = $selectFilesButton;
            }

            options.fileListContainer = $dropzone.parent().prevAll('.il-upload-file-list');

            il.UI.uploader.init(options.id, options);

            $dropzone.dragster({
                enter: function (dragsterEvent, event) {
                    $(this).addClass(CSS.dropzoneDragHover);
                },
                leave: function (dragsterEvent, event) {
                    $(this).removeClass(CSS.dropzoneDragHover);
                },
                drop: function (dragsterEvent, event) {
                    $(this).removeClass(CSS.dropzoneDragHover);
                    var files = event.originalEvent.dataTransfer.files;
                    $.each(files, function (index, file) {
                        il.UI.uploader.addFile(options.id, file);
                    });
                }
            });
        };


        /**
         *
         * @param {Object} options possible settings for this dropzone
         *                         @see {@link initializeDropzone}
         *
         * @private
         */
        var _initWrapperDropzone = function (options) {
            var $dropzone = $("#" + options.id);
            var topmost = ($dropzone.find(".il-dropzone").length === 1);

            if (topmost) {
                // Highlighting handler
                $(document).dragster({
                    enter: function (dragsterEvent, event) {
                        _enableHighlighting(_darkenedBackground);
                    },
                    leave: function (dragsterEvent, event) {
                        _disableHighlighting();
                    },
                    drop: function (dragsterEvent, event) {
                        _disableHighlighting();
                    }
                });

                options.fileListContainer = $dropzone.find('.il-modal-roundtrip').find('.il-upload-file-list');
                options.uploadButton = $dropzone.find('.modal-footer button.btn-primary:first');

                il.UI.uploader.init(options.id, options);
            }

            /*
                * event.stopImmediatePropagation() is needed
                * to prevent dragster to fire leave events on the document,
                * when a user just leaves on the dropzone.
                */
            $dropzone.dragster({
                enter: function (dragsterEvent, event) {
                    dragsterEvent.stopImmediatePropagation();
                    $(this).find(".il-dropzone").addClass(CSS.dropzoneDragHover);
                },
                leave: function (dragsterEvent, event) {
                    dragsterEvent.stopImmediatePropagation();
                    $(this).find(".il-dropzone").removeClass(CSS.dropzoneDragHover);
                },
                drop: function (dragsterEvent, event) {
                    $(this).find(".il-dropzone").removeClass(CSS.dropzoneDragHover);
                    _disableHighlighting();
                    if (!topmost) {
                        event.stopImmediatePropagation();
                        return false;
                    }
                    // Reset the uploader in case files have been dropped before, e.g.
                    // the user drops some files, closes the modal and drops again
                    il.UI.uploader.reset(options.id);
                    var files = event.originalEvent.dataTransfer.files;
                    $.each(files, function (index, file) {
                        il.UI.uploader.addFile(options.id, file);
                    });
                    // This will trigger (at least) the show signal of the modal
                    // _triggerSignals(options.registeredSignals, event, $dropzone);
                }
            });

        };

        return {
            initializeDropzone: initializeDropzone
        };

    })($);
})($, il.UI);