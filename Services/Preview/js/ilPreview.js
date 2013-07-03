// the semi-colon before function invocation is a safety net against concatenated
// scripts and/or other plugins which may not be closed properly.
;(function ($, window, document, undefined)
{
    // TODO: remove for 4.3+
    if (!window.il)
        window.il = {};

    // constants
    // TODO: change to false!!!
    var DEBUG_ENABLED = false;

    // logging also in IE
    if (Function.prototype.bind && console && typeof console.log == "object") { ["log", "info", "warn", "error", "assert", "dir", "clear", "profile", "profileEnd"].forEach(function (e) { console[e] = this.call(console[e], console) }, Function.prototype.bind) } var konsole = { log: function (e) { }, dir: function (e) { } }; if (typeof window.console != "undefined" && typeof window.console.log == "function") { konsole = window.console; if (DEBUG_ENABLED) konsole.log("konsole initialized") } log = function () { if (DEBUG_ENABLED) konsole.log.apply(konsole, arguments) }

    /**
     * Preview.
     */
    var ilPreview = function ()
    {
        // constants
        var QTIP_NAMESPACE = "qtip";

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

        // public properties
        this.texts = {
            preview: "Preview",
            close: "Close",
            loading: "Loading Preview..."
        };

        this.previewSize = 280;

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
        this.toggle = function (e, id, options)
        {
            log("Preview.toggle()")

            // current element?
            if (id == null || currentId == id)
            {
                hide();
            }
            else
            {
                e.stopPropagation();
                e.preventDefault();

                currentId = id;

                // lets show the new one
                show(e, id, options);
                previewVisible = true;
            }
        };

        function show(e, id, options)
        {
            // remove old event handlers
            removeEventHandlers();

            // get preview label
            $label = $(options.htmlId).find(".il_ContainerItemPreview");

            // create tooltip
            initTooltip();

            // load preview and show it
            loadPreview(options.url, function (content)
            {
                // was a different preview requested in the meantime?
                if (id != currentId)
                    return;

                // replace wait
                $tooltip.qtip("api").set("content.text", buildContent(content));

                // initialize navigation
                initPreviews();

                // show the tooltip if not showing already
                // thats generaly the case when content comes from the cache
                if (!$qtip.is(":visible"))
                    $tooltip.qtip("show");
            });
        }

        /**
         * Loads the preview asynchronously.
         */
        function loadPreview(previewUrl, callback)
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
                // show preview
                var loading = "<ul class=\"ilPreviewList\"><il class=\"ilPreviewItem\"><div class=\"ilPreviewText\">" + self.texts.loading + "</div></il></ul>"
                $tooltip.qtip("api").set("content.text", buildContent(loading));
                $tooltip.qtip("api").set("position.target", $label);

                initPreviews();
                $tooltip.qtip("show");

                $.ajax(
                {
                    url: previewUrl,
                    type: "GET",
                    success: function (data)
                    {
                        cache[idToLoad] = data;
                        callback(data);
                    }
                });
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
        function hide()
        {
            log("Preview.hide()");

            currentId = null;
            previewVisible = false;

            // remove keyboard events
            removeEventHandlers();

            $label = null;
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
            var a = $previews.eq(index).find("a");
            var aTop = a.offset().top;
            var centerTop = $(window).height() / 2;
            var scrollTop = document.body.scrollTop;
            var diff = aTop - (centerTop + scrollTop);

            document.body.scrollTop += diff;
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
            //var $list = $qtip.find(".ilPreviewList");
            var $items = $qtip.find(".ilPreviewItem");
            var itemCount = $items.length;

            var currentIdx = -1;

            var previewSize = self.previewSize + 2; // add 2 because the image has a border
            //$list.css({ width: previewSize + "px", height: previewSize + "px" });

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
                    $tooltip.qtip("api").set("content.title.text", self.texts.preview + " " + (index + 1) + " / " + itemCount);
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
                    $tooltip.qtip("api").set("content.title.text", self.texts.preview);
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
                            hide();
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

                // attach mouse wheel (remove old first)
                mouseWheelHandler = handleMouseWheel;
                $qtip.bind("mousewheel", mouseWheelHandler);
                $label.bind("mousewheel", mouseWheelHandler);
            }
            else
            {
                $prev.hide();
                $next.hide();
            }

            // key handlers (remove old first)
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
                    title:
                    {
                        text: self.texts.preview,
                        button: self.texts.close
                    }
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
                    hide: function (e)
                    {
                        $container = $(e.target).closest(".il_ContainerItemPreview");
                        if ($container.length == 0)
                        {
                            hide();
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