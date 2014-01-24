// the semi-colon before function invocation is a safety net against concatenated
// scripts and/or other plugins which may not be closed properly.
;(function ($, window, document, undefined)
{
    // constants
    var DEBUG_ENABLED = false;

    // logging also in IE
    if (Function.prototype.bind && console && typeof console.log == "object") { ["log", "info", "warn", "error", "assert", "dir", "clear", "profile", "profileEnd"].forEach(function (e) { console[e] = this.call(console[e], console) }, Function.prototype.bind) } var konsole = { log: function (e) { }, dir: function (e) { } }; if (typeof window.console != "undefined" && typeof window.console.log == "function") { konsole = window.console; if (DEBUG_ENABLED) konsole.log("konsole initialized") } function log() { if (DEBUG_ENABLED) konsole.log.apply(konsole, arguments) }

    /**
     * Preview.
     */
    var ilPreview = function ()
    {
        // constants
        var QTIP_NAMESPACE = "qtip";
        var STATUS_NONE = "none";
        var STATUS_CREATED = "created";
        var STATUS_PENDING = "pending";
        var STATUS_FAILED = "failed";

        var HIDDEN_ACTION_CLASS = "ilPreviewActionHidden";

        // variables
        var currentId = null;
        var previewVisible = false;
        var keyHandler = null;
        var mouseWheelHandler = null;
        var self = this;
        var isKeyPressed = false;
        var cache = { };

        // jquery objects
        var $tooltip = null;
        var $previews = null;
        var $label = null;
        var $qtip = null;
        var $highlightedItem = null;

        // public properties
        this.texts = {
            preview: "Preview",
            showPreview: "Show Preview",
            close: "Close"
        };
        this.initialHtml = null;
        this.previewSize = 280;
        this.highlightClass = "ilContainerListItemOuterHighlight";

        /**
         * Initializes the preview.
         */
        this.init = function ()
        {
            $previews = $(".il_ContainerItemPreview");
            log("Preview.init(): Previews=%s", $previews.length);
        };

        /**
         * Adds a new object where files can be uploaded to.
         */
        this.toggle = function (e, options)
        {
            log("Preview.toggle()");
            var id = options.id;

            // current element?
            if (id == null || currentId == id)
            {
                hideTooltip();
            }
            else
            {
                e.stopPropagation();
                e.preventDefault();

                currentId = id;

                // lets show the new one
                showTooltip(e, options);
                previewVisible = true;
            }
        };

        /**
         * Renders the preview for the calling object.
         */
        this.render = function (e, options)
        {
            log("Preview.render()");

            $("#preview_render_" + options.id).addClass(HIDDEN_ACTION_CLASS);
            executeAction(options);
        };

        /**
         * Deletes the preview for the calling object.
         */
        this.delete = function (e, options)
        {
            log("Preview.delete()");

            $("#preview_delete_" + options.id).addClass(HIDDEN_ACTION_CLASS);
            executeAction(options);
        }

        /**
         * Executes an action as specified in the specified options.
         */
        function executeAction(options)
        {
            // create new html and display loading animation
            var $html = $(self.initialHtml.replace("%%0%%", options.loadingText));
            $html.find(".ilPreviewTextLoading").css("display", "inline-block");

            // replace old content and set new id
            $("#" + options.htmlId).replaceWith($html);
            $html.attr("id", options.htmlId);

            // call url
            $.ajax(
            {
                url: options.url,
                type: "GET",
                dataType: "json",
                success: function (data)
                {
                    log(" -> Action executed: id=%s, status=%s", options.id, data.status);

                    // replace preview
                    $html.replaceWith(data.html);

                    // enable / disable actions
                    if (data.status == STATUS_FAILED || data.status == STATUS_NONE)
                        $("#preview_render_" + options.id).removeClass(HIDDEN_ACTION_CLASS);
                    else
                        $("#preview_render_" + options.id).addClass(HIDDEN_ACTION_CLASS);

                    if (data.status == STATUS_CREATED)
                        $("#preview_delete_" + options.id).removeClass(HIDDEN_ACTION_CLASS);
                    else
                        $("#preview_delete_" + options.id).addClass(HIDDEN_ACTION_CLASS);
                }
            });
        }

        /**
         * Displays the specified content.
         */
        function displayContent(options, content)
        {
            // was a different preview requested in the meantime?
            if (options.id != currentId)
                return;

            // replace wait
            $tooltip.qtip("api").set("content.text", buildContent(content));

            // initialize navigation
            initPreviews();

            // show the tooltip if not showing already
            // thats generaly the case when content comes from the cache
            if (!$qtip.is(":visible"))
                $tooltip.qtip("show");
        }

        /**
         * Shows the preview tooltip.
         */
        function showTooltip(e, options)
        {
            // remove old event handlers
            removeEventHandlers();

            // get preview label
            $label = $("#" + options.htmlId).find(".il_ContainerItemPreview");

            // create tooltip
            initTooltip();

            // load preview and show it
            loadPreview(options, function (content)
            {
                displayContent(options, content);
            });
        }

        /**
         * Loads the preview asynchronously.
         */
        function loadPreview(options, callback)
        {
            var idToLoad = currentId;

            // object already in cache?
            if (cache.hasOwnProperty(idToLoad))
            {
                $tooltip.qtip("api").set("position.target", $label);
                callback(cache[idToLoad]);
            }
            else
            {
                var loading = self.initialHtml.replace("%%0%%", options.loadingText);
                // display spinner if needed
                if (options.status == STATUS_NONE)
                    $(loading).find(".ilPreviewTextLoading").css("display", "inline-block");

                $tooltip.qtip("api").set("content.text", buildContent(loading));
                $tooltip.qtip("api").set("position.target", $label);

                // cache the loading text to prevent multiple server calls if
                // the tooltip is hidden and shown again while the request is running
                cache[idToLoad] = loading;

                initPreviews();
                $tooltip.qtip("show");

                $.ajax(
                {
                    url: options.url,
                    type: "GET",
                    dataType: "json",
                    success: function (data)
                    {
                        log(" -> Preview loaded: id=%s, status=%s", options.id, data.status);
                        updateCache(options.id, data.status, data.html);
                        callback(data.html);
                        updatePreviewIcon(options, data.status);
                    }
                });
            }

            highlightItem(options.htmlId);
        }

        /**
         * Updates the preview icon if the preview was renderered on demand.
         */
        function updatePreviewIcon(options, newStatus)
        {
            // if previous status was none, update status
            if (options.status == STATUS_NONE &&
                newStatus == STATUS_CREATED || newStatus == STATUS_FAILED)
            {
                $("#" + options.htmlId).find(".il_ContainerItemPreview")
                    .removeClass("ilPreviewStatusNone")
                    .children("a").prop("title", self.texts.showPreview);
            }
        }

        /**
         * Updates the cache of the specified item with the specified content.
         */
        function updateCache(id, status, content)
        {
            // set cache if preview was created
            switch (status)
            {
                // delete cache entry that the request is done again
                case STATUS_PENDING:
                    delete cache[id];
                    break;

                    // cache response
                case STATUS_CREATED:
                case STATUS_FAILED:
                case STATUS_NONE:
                default:
                    cache[id] = content;
                    break;
            }
        }

        /**
         * Highlights the item with the specified id.
         */
        function highlightItem(htmlId)
        {
            // remove old highlight
            if ($highlightedItem != null)
            {
                $highlightedItem.removeClass(self.highlightClass);
                $highlightedItem = null;
            }

            // highlight new item
            if (htmlId != null)
            {
                $highlightedItem = $("#" + htmlId);
                if ($highlightedItem.length == 1)
                    $highlightedItem.addClass(self.highlightClass);
                else
                    $highlightedItem = null;
            }
        }

        /**
         * Builds the content.
         */
        function buildContent(content)
        {
            var html = "<div class=\"ilPreviewTooltipContent\"><div class=\"ilPreviewTooltipPrev\"></div>";
            html += content;
            html += "<div class=\"ilPreviewTooltipNext\"></div></div>";

            // set dimensions
            var previewSize = self.previewSize + 2; // add 2 because the image has a border
            var $content = $(html);
            $content.find(".ilPreviewList").css({ width: previewSize + "px", height: previewSize + "px" });

            return $content.get(0);
        }

        /**
         * Hides the preview.
         */
        function hideTooltip()
        {
            log("Preview.hideTooltip()");

            currentId = null;
            previewVisible = false;

            // remove keyboard events
            removeEventHandlers();

            $label = null;

            highlightItem(null);
        }

        /**
         * Removes the event handlers for keyboard and mouse events.
         */
        function removeEventHandlers()
        {
            // remove keyboard events
            if (keyHandler != null)
            {
                $(document).unbind("keydown keyup", keyHandler);
            }

            // remove mouse wheel events
            if (mouseWheelHandler != null)
            {
                $qtip.unbind("mousewheel", mouseWheelHandler);
                $label.unbind("mousewheel", mouseWheelHandler);
            }

            keyHandler = null;
            mouseWheelHandler = null;
        }

        /**
         * Shows the preview of the next object.
         */
        function showNextPreview()
        {
            if ($label == null)
                return;

            log("Preview.showNextPreview()");

            // get current label
            var nextIndex = $previews.index($label) + 1;
            if (nextIndex < $previews.length)
                showPreview(nextIndex);
        }

        /**
         * Shows the preview of the previous object.
         */
        function showPreviousPreview()
        {
            if ($label == null)
                return;

            log("Preview.showPreviousPreview()");

            // get current label
            var prevIndex = $previews.index($label) - 1;
            if (prevIndex >= 0)
                showPreview(prevIndex);
        }

        /**
         * Shows the preview of the object at the specified index.
         */
        function showPreview(index)
        {
            var $window = $(window);

            // get a element
            var a = $previews.eq(index).find("a");
            var aTop = a.offset().top;
            var centerTop = $window.height() / 2;
            var scrollTop = $window.scrollTop();
            var diff = aTop - (centerTop + scrollTop);

            $window.scrollTop(scrollTop + diff);

            a.click();
        }

        /**
         * Initializes the previews for the current object.
         */
        function initPreviews()
        {
            // remove all previous events
            removeEventHandlers();

            // attach click events
            var $prev = $qtip.find(".ilPreviewTooltipPrev");
            var $next = $qtip.find(".ilPreviewTooltipNext");
            var $items = $qtip.find(".ilPreviewItem");
            var itemCount = $items.length;

            var currentIdx = -1;
            var previewSize = self.previewSize + 2; // add 2 because the image has a border

            /**
             * Show the preview image at the specified index.
             */
            function showIndex(index)
            {
                log("Preview.showIndex(): current=%s, idx=%s", currentIdx, index);

                // same index as before?
                if (index == currentIdx)
                    return;

                log("Preview.showIndex(%s)", index);

                $items.hide();

                $item = $items.eq(index);
                var height = $item.children().height();
                $item.css("margin-top", ((previewSize - height) / 2) + "px");
                $item.show();

                // more than one item?
                if (itemCount > 1)
                {
                    $tooltip.qtip("api").set("content.title", self.texts.preview + " " + (index + 1) + " / " + itemCount);
                    if (index < 1)
                        $prev.addClass("ilPreviewDisabled");
                    else
                        $prev.removeClass("ilPreviewDisabled");

                    if (index >= itemCount - 1)
                        $next.addClass("ilPreviewDisabled");
                    else
                        $next.removeClass("ilPreviewDisabled");
                }
                else
                {
                    $tooltip.qtip("api").set("content.title", self.texts.preview);
                }

                currentIdx = index;
            }

            /**
             * Show the next preview image.
             */
            function showNext()
            {
                if (currentIdx < itemCount - 1)
                    showIndex(currentIdx + 1);
            }

            /**
             * Show the previous preview image.
             */
            function showPrevious()
            {
                if (currentIdx > 0)
                    showIndex(currentIdx - 1);
            }

            /**
             * Handles the events when a key was released.
             */
            function handleKeyUp(e)
            {
                // key already pressed? only execute once
                if (e.type == "keydown")
                {
                    if (isKeyPressed)
                    {
                        // prevent default if up or down arrow
                        if (e.which == 38 || e.which == 40)
                            e.preventDefault();

                        return;
                    }

                    isKeyPressed = true;

                    // which key was pressed?
                    switch (e.which)
                    {
                        case 38: // up arrow key
                            e.preventDefault();
                            showPreviousPreview();
                            break;

                        case 40: // down arrow key
                            e.preventDefault();
                            showNextPreview();
                            break;
                    }
                }
                else
                {
                    isKeyPressed = false;

                    // which key was pressed?
                    switch (e.which)
                    {
                        case 37: // left arrow key
                            e.preventDefault();
                            showPrevious();
                            break;

                        case 39: // right arrow key
                            e.preventDefault();
                            showNext();
                            break;

                        case 36: // HOME
                            e.preventDefault();
                            showIndex(0);
                            break;

                        case 35: // END
                            e.preventDefault();
                            showIndex(itemCount - 1);
                            break;

                        case 27: // ESC
                            e.preventDefault();
                            $tooltip.qtip("hide");
                            hideTooltip();
                            break;
                    }
                }
            }

            /**
             * Handles the events when the mouse wheel was rotated.
             */
            function handleMouseWheel(e, delta, deltaX, deltaY)
            {
                if (deltaY != 0)
                {
                    e.preventDefault();
                    if (deltaY < 0)
                        showNext();
                    else
                        showPrevious();
                }
            }

            // more than one item?
            if (itemCount > 1)
            {
                // set number
                $items.each(function (index, elem)
                {
                    $(elem).attr("data-index", index);
                });

                // click events
                $prev.click(showPrevious);
                $next.click(showNext);

                $prev.show();
                $next.show();

                // attach mouse wheel
                // (assign to variable is important that it can be removed later on)
                mouseWheelHandler = handleMouseWheel;
                $qtip.bind("mousewheel", mouseWheelHandler);
                $label.bind("mousewheel", mouseWheelHandler);
            }
            else
            {
                $prev.hide();
                $next.hide();
            }

            // key handlers
            // (assign to variable is important that it can be removed later on)
            keyHandler = handleKeyUp;
            $(document).bind("keydown keyup", keyHandler);

            // hide items and show first
            showIndex(0);
        }

        /**
         * Initializes the tooltip.
         */
        function initTooltip()
        {
            if ($tooltip != null)
                return;

            $tooltip = $("<div />").qtip(
            {
                id: "preview",
                content: {
                    text: " ",
                    title: self.texts.preview,
                    button: self.texts.close
                },
                position: {
                    my: "left center",
                    at: "right center",
                    adjust: { x: 5 },
                    effect: false,
                    viewport: $(window)
                },
                show: false,
                hide: {
                    effect: false,
                    event: "unfocus"
                },
                style: {
                    classes: "ilPreviewTooltip",
                    widget: false,
                    tip: {
                        width: QTIP_NAMESPACE == "ui-tooltip" ? 11 : 21,
                        height: QTIP_NAMESPACE == "ui-tooltip" ? 21 : 11
                    }
                },
                events: {
                    hide: function (e, api)
                    {
                        if (e.originalEvent)
                        {
                            $container = $(e.originalEvent.target).closest(".il_ContainerItemPreview");
                            if ($container.length == 0)
                                hideTooltip();
                        }
                    }
                }
            });

            $tooltip.qtip("render");
            $qtip = $("#" + QTIP_NAMESPACE + "-preview");
        }
    };
    il.Preview = new ilPreview();

})(jQuery, window, document);