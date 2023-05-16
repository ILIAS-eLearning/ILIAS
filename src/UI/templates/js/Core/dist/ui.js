var il = il || {};
(function (il) {
    'use strict';

    il = il && Object.prototype.hasOwnProperty.call(il, 'default') ? il['default'] : il;

    /**
     * Replace a component or parts of a component using ajax call
     *
     * @param id component id
     * @param url replacement url
     * @param marker replacement marker ("component", "content", "header", ...)
     */
    var replaceContent = function($) {
        return function (id, url, marker) {
            // get new stuff via ajax
            $.ajax({
                url: url,
                dataType: 'html'
            }).done(function(html) {
                var $new_content = $("<div>" + html + "</div>");
                var $marked_new_content = $new_content.find("[data-replace-marker='" + marker + "']").first();

                if ($marked_new_content.length == 0) {

                    // if marker does not come with the new content, we put the new content into the existing marker
                    // (this includes all script tags already)
                    $("#" + id + " [data-replace-marker='" + marker + "']").html(html);

                } else {

                    // if marker is in new content, we replace the complete old node with the marker
                    // with the new marked node
                    $("#" + id + " [data-replace-marker='" + marker + "']").first()
                        .replaceWith($marked_new_content);

                    // append included script (which will not be part of the marked node
                    $("#" + id + " [data-replace-marker='" + marker + "']").first()
                        .after($new_content.find("[data-replace-marker='script']"));
                }
            });
        }
    };

    /**
     * This represents one tooltip on the page.
     */
    class Tooltip {
        /**
         * @type {HTMLElement}
         */
        #tooltip;

        /**
         * The tooltip element itself.
         * @type {Element}
         */
        #element;

        /**
         * The container of the tooltip and the trigger element.
         * @type {Element}
         */
        #container;

        /**
         * The HTMLDocument this all exists inside.
         * @type {HTMLDocument}
         */
        #document;

        /**
         * The Window through which we see that stuff.
         * @type {Window}
         */
        #window;

        /**
         * This will be the "main"-container if the tooltip is inside one.
         * @type {?Element}
         */
        #main = null;

        constructor(element) {
            this.#container = element.parentElement;
            this.#element = element;
            this.#document = element.ownerDocument;
            this.#window = this.#document.defaultView || this.#document.parentWindow;

            var tooltip_id = this.#element.getAttribute("aria-describedby");
            if (tooltip_id === null) {
                throw new Error("Could not find expected attribute aria-describedby for element with tooltip.");
            }

            this.#tooltip = this.#document.getElementById(tooltip_id);
            if (this.#tooltip === null) {
                throw new Error("Tooltip " + tooltip_id + " not found.", {cause: this.#element});
            }

            let main = getVisibleMainElement(this.#document);
            if (null !== main && main.contains(this.#tooltip)) {
                this.#main = main;
            }

            this.showTooltip = this.showTooltip.bind(this);
            this.hideTooltip = this.hideTooltip.bind(this);
            this.onKeyDown = this.onKeyDown.bind(this);
            this.onPointerDown = this.onPointerDown.bind(this);

            this.bindElementEvents();
            this.bindContainerEvents();
        }

        /**
         * @returns {HTMLElement}
         */
        get tooltip() {
            return this.#tooltip;
        }

        /**
         * @returns {undefined}
         */
        showTooltip() {
            this.#container.classList.add("c-tooltip--visible");
            this.bindDocumentEvents();

            this.checkVerticalBounds();
            this.checkHorizontalBounds();
        }

        /**
         * @returns {undefined}
         */
        hideTooltip() {
            this.#container.classList.remove("c-tooltip--visible");
            this.unbindDocumentEvents();

            this.#container.classList.remove("c-tooltip--top");
            this.#tooltip.style.transform = null;
        }

        /**
         * @returns {undefined}
         */
        bindElementEvents() {
            this.#element.addEventListener("focus", this.showTooltip);
            this.#element.addEventListener("blur", this.hideTooltip);
        }

        /**
         * @returns {undefined}
         */
        bindContainerEvents() {
            this.#container.addEventListener("mouseenter", this.showTooltip);
            this.#container.addEventListener("touchstart", this.showTooltip);
            this.#container.addEventListener("mouseleave", this.hideTooltip);
        }

        /**
         * @returns {undefined}
         */
        bindDocumentEvents() {
            this.#document.addEventListener("keydown", this.onKeyDown);
            this.#document.addEventListener("pointerdown", this.onPointerDown);
        }

        /**
         * @returns {undefined}
         */
        unbindDocumentEvents() {
            this.#document.removeEventListener("keydown", this.onKeyDown);
            this.#document.removeEventListener("pointerdown", this.onPointerDown);
        }

        /**
         * @returns {undefined}
         */
        onKeyDown(event) {
            if (event.key === "Esc" || event.key === "Escape") {
                this.hideTooltip();
            }
        }

        /**
         * @returns {undefined}
         */
        onPointerDown(event) {
            if(event.target === this.#element || event.target === this.#tooltip) {
                event.preventDefault();
            }
            else {
                this.hideTooltip();
                this.#element.blur();
            }
        }

        /**
         * @returns {undefined}
         */
        checkVerticalBounds() {
            var ttRect = this.#tooltip.getBoundingClientRect();
            var dpRect = this.getDisplayRect();

            if (ttRect.bottom > (dpRect.top + dpRect.height)) {
                this.#container.classList.add("c-tooltip--top");
            }
        }

        /**
         * @returns {undefined}
         */
        checkHorizontalBounds() {
            var ttRect = this.#tooltip.getBoundingClientRect();
            var dpRect = this.getDisplayRect();

            if ((dpRect.width - dpRect.left) < ttRect.right) {
                this.#tooltip.style.transform = "translateX(" + ((dpRect.width - dpRect.left) - ttRect.right) + "px)";
            }
            if (ttRect.left < dpRect.left) {
                this.#tooltip.style.transform = "translateX(" + ((dpRect.left - ttRect.left) - ttRect.width/2) + "px)";
            }
        }

        /**
         * @returns {{left: number, top: number, width: number, height: number}}
         */
        getDisplayRect() {
            if (this.#main !== null) {
                return this.#main.getBoundingClientRect();
            }

            return {
                left: 0,
                top: 0,
                width: this.#window.innerWidth,
                height: this.#window.innerHeight
            }
        }
    }

    /**
     * Returns the visible main-element of the given document.
     *
     * A document may contain multiple main-elemets, only one must be visible
     * (not have a hidden-attribute).
     *
     * @param {HTMLDocument} document
     * @returns {HTMLElement|null}
     * @see https://html.spec.whatwg.org/multipage/grouping-content.html#the-main-element
     */
    function getVisibleMainElement(document) {
        const main_elements = document.getElementsByTagName("main");
        const visible_main = main_elements.find((element) => !element.hasOwnProperty('hidden'));

        return (undefined !== visible_main) ? visible_main : null;
    }

    il.UI = il.UI || {};
    il.UI.core = il.UI.core || {};

    il.UI.core.replaceContent = replaceContent($);
    il.UI.core.Tooltip = Tooltip;

}(il));
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidWkuanMiLCJzb3VyY2VzIjpbIi4uL3NyYy9jb3JlLnJlcGxhY2VDb250ZW50LmpzIiwiLi4vc3JjL2NvcmUuVG9vbHRpcC5qcyIsIi4uL3NyYy9jb3JlLmpzIl0sInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogUmVwbGFjZSBhIGNvbXBvbmVudCBvciBwYXJ0cyBvZiBhIGNvbXBvbmVudCB1c2luZyBhamF4IGNhbGxcbiAqXG4gKiBAcGFyYW0gaWQgY29tcG9uZW50IGlkXG4gKiBAcGFyYW0gdXJsIHJlcGxhY2VtZW50IHVybFxuICogQHBhcmFtIG1hcmtlciByZXBsYWNlbWVudCBtYXJrZXIgKFwiY29tcG9uZW50XCIsIFwiY29udGVudFwiLCBcImhlYWRlclwiLCAuLi4pXG4gKi9cbnZhciByZXBsYWNlQ29udGVudCA9IGZ1bmN0aW9uKCQpIHtcbiAgICByZXR1cm4gZnVuY3Rpb24gKGlkLCB1cmwsIG1hcmtlcikge1xuICAgICAgICAvLyBnZXQgbmV3IHN0dWZmIHZpYSBhamF4XG4gICAgICAgICQuYWpheCh7XG4gICAgICAgICAgICB1cmw6IHVybCxcbiAgICAgICAgICAgIGRhdGFUeXBlOiAnaHRtbCdcbiAgICAgICAgfSkuZG9uZShmdW5jdGlvbihodG1sKSB7XG4gICAgICAgICAgICB2YXIgJG5ld19jb250ZW50ID0gJChcIjxkaXY+XCIgKyBodG1sICsgXCI8L2Rpdj5cIik7XG4gICAgICAgICAgICB2YXIgJG1hcmtlZF9uZXdfY29udGVudCA9ICRuZXdfY29udGVudC5maW5kKFwiW2RhdGEtcmVwbGFjZS1tYXJrZXI9J1wiICsgbWFya2VyICsgXCInXVwiKS5maXJzdCgpO1xuXG4gICAgICAgICAgICBpZiAoJG1hcmtlZF9uZXdfY29udGVudC5sZW5ndGggPT0gMCkge1xuXG4gICAgICAgICAgICAgICAgLy8gaWYgbWFya2VyIGRvZXMgbm90IGNvbWUgd2l0aCB0aGUgbmV3IGNvbnRlbnQsIHdlIHB1dCB0aGUgbmV3IGNvbnRlbnQgaW50byB0aGUgZXhpc3RpbmcgbWFya2VyXG4gICAgICAgICAgICAgICAgLy8gKHRoaXMgaW5jbHVkZXMgYWxsIHNjcmlwdCB0YWdzIGFscmVhZHkpXG4gICAgICAgICAgICAgICAgJChcIiNcIiArIGlkICsgXCIgW2RhdGEtcmVwbGFjZS1tYXJrZXI9J1wiICsgbWFya2VyICsgXCInXVwiKS5odG1sKGh0bWwpO1xuXG4gICAgICAgICAgICB9IGVsc2Uge1xuXG4gICAgICAgICAgICAgICAgLy8gaWYgbWFya2VyIGlzIGluIG5ldyBjb250ZW50LCB3ZSByZXBsYWNlIHRoZSBjb21wbGV0ZSBvbGQgbm9kZSB3aXRoIHRoZSBtYXJrZXJcbiAgICAgICAgICAgICAgICAvLyB3aXRoIHRoZSBuZXcgbWFya2VkIG5vZGVcbiAgICAgICAgICAgICAgICAkKFwiI1wiICsgaWQgKyBcIiBbZGF0YS1yZXBsYWNlLW1hcmtlcj0nXCIgKyBtYXJrZXIgKyBcIiddXCIpLmZpcnN0KClcbiAgICAgICAgICAgICAgICAgICAgLnJlcGxhY2VXaXRoKCRtYXJrZWRfbmV3X2NvbnRlbnQpO1xuXG4gICAgICAgICAgICAgICAgLy8gYXBwZW5kIGluY2x1ZGVkIHNjcmlwdCAod2hpY2ggd2lsbCBub3QgYmUgcGFydCBvZiB0aGUgbWFya2VkIG5vZGVcbiAgICAgICAgICAgICAgICAkKFwiI1wiICsgaWQgKyBcIiBbZGF0YS1yZXBsYWNlLW1hcmtlcj0nXCIgKyBtYXJrZXIgKyBcIiddXCIpLmZpcnN0KClcbiAgICAgICAgICAgICAgICAgICAgLmFmdGVyKCRuZXdfY29udGVudC5maW5kKFwiW2RhdGEtcmVwbGFjZS1tYXJrZXI9J3NjcmlwdCddXCIpKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG4gICAgfVxufTtcblxuZXhwb3J0IGRlZmF1bHQgcmVwbGFjZUNvbnRlbnQ7XG4iLCIvKipcbiAqIFRoaXMgcmVwcmVzZW50cyBvbmUgdG9vbHRpcCBvbiB0aGUgcGFnZS5cbiAqL1xuY2xhc3MgVG9vbHRpcCB7XG4gICAgLyoqXG4gICAgICogQHR5cGUge0hUTUxFbGVtZW50fVxuICAgICAqL1xuICAgICN0b29sdGlwO1xuXG4gICAgLyoqXG4gICAgICogVGhlIHRvb2x0aXAgZWxlbWVudCBpdHNlbGYuXG4gICAgICogQHR5cGUge0VsZW1lbnR9XG4gICAgICovXG4gICAgI2VsZW1lbnQ7XG5cbiAgICAvKipcbiAgICAgKiBUaGUgY29udGFpbmVyIG9mIHRoZSB0b29sdGlwIGFuZCB0aGUgdHJpZ2dlciBlbGVtZW50LlxuICAgICAqIEB0eXBlIHtFbGVtZW50fVxuICAgICAqL1xuICAgICNjb250YWluZXI7XG5cbiAgICAvKipcbiAgICAgKiBUaGUgSFRNTERvY3VtZW50IHRoaXMgYWxsIGV4aXN0cyBpbnNpZGUuXG4gICAgICogQHR5cGUge0hUTUxEb2N1bWVudH1cbiAgICAgKi9cbiAgICAjZG9jdW1lbnQ7XG5cbiAgICAvKipcbiAgICAgKiBUaGUgV2luZG93IHRocm91Z2ggd2hpY2ggd2Ugc2VlIHRoYXQgc3R1ZmYuXG4gICAgICogQHR5cGUge1dpbmRvd31cbiAgICAgKi9cbiAgICAjd2luZG93O1xuXG4gICAgLyoqXG4gICAgICogVGhpcyB3aWxsIGJlIHRoZSBcIm1haW5cIi1jb250YWluZXIgaWYgdGhlIHRvb2x0aXAgaXMgaW5zaWRlIG9uZS5cbiAgICAgKiBAdHlwZSB7P0VsZW1lbnR9XG4gICAgICovXG4gICAgI21haW4gPSBudWxsO1xuXG4gICAgY29uc3RydWN0b3IoZWxlbWVudCkge1xuICAgICAgICB0aGlzLiNjb250YWluZXIgPSBlbGVtZW50LnBhcmVudEVsZW1lbnQ7XG4gICAgICAgIHRoaXMuI2VsZW1lbnQgPSBlbGVtZW50O1xuICAgICAgICB0aGlzLiNkb2N1bWVudCA9IGVsZW1lbnQub3duZXJEb2N1bWVudDtcbiAgICAgICAgdGhpcy4jd2luZG93ID0gdGhpcy4jZG9jdW1lbnQuZGVmYXVsdFZpZXcgfHwgdGhpcy4jZG9jdW1lbnQucGFyZW50V2luZG93O1xuXG4gICAgICAgIHZhciB0b29sdGlwX2lkID0gdGhpcy4jZWxlbWVudC5nZXRBdHRyaWJ1dGUoXCJhcmlhLWRlc2NyaWJlZGJ5XCIpO1xuICAgICAgICBpZiAodG9vbHRpcF9pZCA9PT0gbnVsbCkge1xuICAgICAgICAgICAgdGhyb3cgbmV3IEVycm9yKFwiQ291bGQgbm90IGZpbmQgZXhwZWN0ZWQgYXR0cmlidXRlIGFyaWEtZGVzY3JpYmVkYnkgZm9yIGVsZW1lbnQgd2l0aCB0b29sdGlwLlwiKTtcbiAgICAgICAgfVxuXG4gICAgICAgIHRoaXMuI3Rvb2x0aXAgPSB0aGlzLiNkb2N1bWVudC5nZXRFbGVtZW50QnlJZCh0b29sdGlwX2lkKTtcbiAgICAgICAgaWYgKHRoaXMuI3Rvb2x0aXAgPT09IG51bGwpIHtcbiAgICAgICAgICAgIHRocm93IG5ldyBFcnJvcihcIlRvb2x0aXAgXCIgKyB0b29sdGlwX2lkICsgXCIgbm90IGZvdW5kLlwiLCB7Y2F1c2U6IHRoaXMuI2VsZW1lbnR9KTtcbiAgICAgICAgfVxuXG4gICAgICAgIGxldCBtYWluID0gZ2V0VmlzaWJsZU1haW5FbGVtZW50KHRoaXMuI2RvY3VtZW50KTtcbiAgICAgICAgaWYgKG51bGwgIT09IG1haW4gJiYgbWFpbi5jb250YWlucyh0aGlzLiN0b29sdGlwKSkge1xuICAgICAgICAgICAgdGhpcy4jbWFpbiA9IG1haW47XG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLnNob3dUb29sdGlwID0gdGhpcy5zaG93VG9vbHRpcC5iaW5kKHRoaXMpO1xuICAgICAgICB0aGlzLmhpZGVUb29sdGlwID0gdGhpcy5oaWRlVG9vbHRpcC5iaW5kKHRoaXMpO1xuICAgICAgICB0aGlzLm9uS2V5RG93biA9IHRoaXMub25LZXlEb3duLmJpbmQodGhpcyk7XG4gICAgICAgIHRoaXMub25Qb2ludGVyRG93biA9IHRoaXMub25Qb2ludGVyRG93bi5iaW5kKHRoaXMpO1xuXG4gICAgICAgIHRoaXMuYmluZEVsZW1lbnRFdmVudHMoKTtcbiAgICAgICAgdGhpcy5iaW5kQ29udGFpbmVyRXZlbnRzKCk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQHJldHVybnMge0hUTUxFbGVtZW50fVxuICAgICAqL1xuICAgIGdldCB0b29sdGlwKCkge1xuICAgICAgICByZXR1cm4gdGhpcy4jdG9vbHRpcDtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBAcmV0dXJucyB7dW5kZWZpbmVkfVxuICAgICAqL1xuICAgIHNob3dUb29sdGlwKCkge1xuICAgICAgICB0aGlzLiNjb250YWluZXIuY2xhc3NMaXN0LmFkZChcImMtdG9vbHRpcC0tdmlzaWJsZVwiKTtcbiAgICAgICAgdGhpcy5iaW5kRG9jdW1lbnRFdmVudHMoKTtcblxuICAgICAgICB0aGlzLmNoZWNrVmVydGljYWxCb3VuZHMoKTtcbiAgICAgICAgdGhpcy5jaGVja0hvcml6b250YWxCb3VuZHMoKTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBAcmV0dXJucyB7dW5kZWZpbmVkfVxuICAgICAqL1xuICAgIGhpZGVUb29sdGlwKCkge1xuICAgICAgICB0aGlzLiNjb250YWluZXIuY2xhc3NMaXN0LnJlbW92ZShcImMtdG9vbHRpcC0tdmlzaWJsZVwiKTtcbiAgICAgICAgdGhpcy51bmJpbmREb2N1bWVudEV2ZW50cygpO1xuXG4gICAgICAgIHRoaXMuI2NvbnRhaW5lci5jbGFzc0xpc3QucmVtb3ZlKFwiYy10b29sdGlwLS10b3BcIik7XG4gICAgICAgIHRoaXMuI3Rvb2x0aXAuc3R5bGUudHJhbnNmb3JtID0gbnVsbDtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBAcmV0dXJucyB7dW5kZWZpbmVkfVxuICAgICAqL1xuICAgIGJpbmRFbGVtZW50RXZlbnRzKCkge1xuICAgICAgICB0aGlzLiNlbGVtZW50LmFkZEV2ZW50TGlzdGVuZXIoXCJmb2N1c1wiLCB0aGlzLnNob3dUb29sdGlwKTtcbiAgICAgICAgdGhpcy4jZWxlbWVudC5hZGRFdmVudExpc3RlbmVyKFwiYmx1clwiLCB0aGlzLmhpZGVUb29sdGlwKTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBAcmV0dXJucyB7dW5kZWZpbmVkfVxuICAgICAqL1xuICAgIGJpbmRDb250YWluZXJFdmVudHMoKSB7XG4gICAgICAgIHRoaXMuI2NvbnRhaW5lci5hZGRFdmVudExpc3RlbmVyKFwibW91c2VlbnRlclwiLCB0aGlzLnNob3dUb29sdGlwKTtcbiAgICAgICAgdGhpcy4jY29udGFpbmVyLmFkZEV2ZW50TGlzdGVuZXIoXCJ0b3VjaHN0YXJ0XCIsIHRoaXMuc2hvd1Rvb2x0aXApO1xuICAgICAgICB0aGlzLiNjb250YWluZXIuYWRkRXZlbnRMaXN0ZW5lcihcIm1vdXNlbGVhdmVcIiwgdGhpcy5oaWRlVG9vbHRpcCk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQHJldHVybnMge3VuZGVmaW5lZH1cbiAgICAgKi9cbiAgICBiaW5kRG9jdW1lbnRFdmVudHMoKSB7XG4gICAgICAgIHRoaXMuI2RvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoXCJrZXlkb3duXCIsIHRoaXMub25LZXlEb3duKVxuICAgICAgICB0aGlzLiNkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKFwicG9pbnRlcmRvd25cIiwgdGhpcy5vblBvaW50ZXJEb3duKVxuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEByZXR1cm5zIHt1bmRlZmluZWR9XG4gICAgICovXG4gICAgdW5iaW5kRG9jdW1lbnRFdmVudHMoKSB7XG4gICAgICAgIHRoaXMuI2RvY3VtZW50LnJlbW92ZUV2ZW50TGlzdGVuZXIoXCJrZXlkb3duXCIsIHRoaXMub25LZXlEb3duKVxuICAgICAgICB0aGlzLiNkb2N1bWVudC5yZW1vdmVFdmVudExpc3RlbmVyKFwicG9pbnRlcmRvd25cIiwgdGhpcy5vblBvaW50ZXJEb3duKVxuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEByZXR1cm5zIHt1bmRlZmluZWR9XG4gICAgICovXG4gICAgb25LZXlEb3duKGV2ZW50KSB7XG4gICAgICAgIGlmIChldmVudC5rZXkgPT09IFwiRXNjXCIgfHwgZXZlbnQua2V5ID09PSBcIkVzY2FwZVwiKSB7XG4gICAgICAgICAgICB0aGlzLmhpZGVUb29sdGlwKCk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBAcmV0dXJucyB7dW5kZWZpbmVkfVxuICAgICAqL1xuICAgIG9uUG9pbnRlckRvd24oZXZlbnQpIHtcbiAgICAgICAgaWYoZXZlbnQudGFyZ2V0ID09PSB0aGlzLiNlbGVtZW50IHx8IGV2ZW50LnRhcmdldCA9PT0gdGhpcy4jdG9vbHRpcCkge1xuICAgICAgICAgICAgZXZlbnQucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgIHRoaXMuaGlkZVRvb2x0aXAoKTtcbiAgICAgICAgICAgIHRoaXMuI2VsZW1lbnQuYmx1cigpO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQHJldHVybnMge3VuZGVmaW5lZH1cbiAgICAgKi9cbiAgICBjaGVja1ZlcnRpY2FsQm91bmRzKCkge1xuICAgICAgICB2YXIgdHRSZWN0ID0gdGhpcy4jdG9vbHRpcC5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKTtcbiAgICAgICAgdmFyIGRwUmVjdCA9IHRoaXMuZ2V0RGlzcGxheVJlY3QoKTtcblxuICAgICAgICBpZiAodHRSZWN0LmJvdHRvbSA+IChkcFJlY3QudG9wICsgZHBSZWN0LmhlaWdodCkpIHtcbiAgICAgICAgICAgIHRoaXMuI2NvbnRhaW5lci5jbGFzc0xpc3QuYWRkKFwiYy10b29sdGlwLS10b3BcIik7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBAcmV0dXJucyB7dW5kZWZpbmVkfVxuICAgICAqL1xuICAgIGNoZWNrSG9yaXpvbnRhbEJvdW5kcygpIHtcbiAgICAgICAgdmFyIHR0UmVjdCA9IHRoaXMuI3Rvb2x0aXAuZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCk7XG4gICAgICAgIHZhciBkcFJlY3QgPSB0aGlzLmdldERpc3BsYXlSZWN0KCk7XG5cbiAgICAgICAgaWYgKChkcFJlY3Qud2lkdGggLSBkcFJlY3QubGVmdCkgPCB0dFJlY3QucmlnaHQpIHtcbiAgICAgICAgICAgIHRoaXMuI3Rvb2x0aXAuc3R5bGUudHJhbnNmb3JtID0gXCJ0cmFuc2xhdGVYKFwiICsgKChkcFJlY3Qud2lkdGggLSBkcFJlY3QubGVmdCkgLSB0dFJlY3QucmlnaHQpICsgXCJweClcIjtcbiAgICAgICAgfVxuICAgICAgICBpZiAodHRSZWN0LmxlZnQgPCBkcFJlY3QubGVmdCkge1xuICAgICAgICAgICAgdGhpcy4jdG9vbHRpcC5zdHlsZS50cmFuc2Zvcm0gPSBcInRyYW5zbGF0ZVgoXCIgKyAoKGRwUmVjdC5sZWZ0IC0gdHRSZWN0LmxlZnQpIC0gdHRSZWN0LndpZHRoLzIpICsgXCJweClcIjtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEByZXR1cm5zIHt7bGVmdDogbnVtYmVyLCB0b3A6IG51bWJlciwgd2lkdGg6IG51bWJlciwgaGVpZ2h0OiBudW1iZXJ9fVxuICAgICAqL1xuICAgIGdldERpc3BsYXlSZWN0KCkge1xuICAgICAgICBpZiAodGhpcy4jbWFpbiAhPT0gbnVsbCkge1xuICAgICAgICAgICAgcmV0dXJuIHRoaXMuI21haW4uZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCk7XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgbGVmdDogMCxcbiAgICAgICAgICAgIHRvcDogMCxcbiAgICAgICAgICAgIHdpZHRoOiB0aGlzLiN3aW5kb3cuaW5uZXJXaWR0aCxcbiAgICAgICAgICAgIGhlaWdodDogdGhpcy4jd2luZG93LmlubmVySGVpZ2h0XG4gICAgICAgIH1cbiAgICB9XG59XG5cbi8qKlxuICogUmV0dXJucyB0aGUgdmlzaWJsZSBtYWluLWVsZW1lbnQgb2YgdGhlIGdpdmVuIGRvY3VtZW50LlxuICpcbiAqIEEgZG9jdW1lbnQgbWF5IGNvbnRhaW4gbXVsdGlwbGUgbWFpbi1lbGVtZXRzLCBvbmx5IG9uZSBtdXN0IGJlIHZpc2libGVcbiAqIChub3QgaGF2ZSBhIGhpZGRlbi1hdHRyaWJ1dGUpLlxuICpcbiAqIEBwYXJhbSB7SFRNTERvY3VtZW50fSBkb2N1bWVudFxuICogQHJldHVybnMge0hUTUxFbGVtZW50fG51bGx9XG4gKiBAc2VlIGh0dHBzOi8vaHRtbC5zcGVjLndoYXR3Zy5vcmcvbXVsdGlwYWdlL2dyb3VwaW5nLWNvbnRlbnQuaHRtbCN0aGUtbWFpbi1lbGVtZW50XG4gKi9cbmZ1bmN0aW9uIGdldFZpc2libGVNYWluRWxlbWVudChkb2N1bWVudCkge1xuICAgIGNvbnN0IG1haW5fZWxlbWVudHMgPSBkb2N1bWVudC5nZXRFbGVtZW50c0J5VGFnTmFtZShcIm1haW5cIik7XG4gICAgY29uc3QgdmlzaWJsZV9tYWluID0gbWFpbl9lbGVtZW50cy5maW5kKChlbGVtZW50KSA9PiAhZWxlbWVudC5oYXNPd25Qcm9wZXJ0eSgnaGlkZGVuJykpO1xuXG4gICAgcmV0dXJuICh1bmRlZmluZWQgIT09IHZpc2libGVfbWFpbikgPyB2aXNpYmxlX21haW4gOiBudWxsO1xufVxuXG5leHBvcnQgZGVmYXVsdCBUb29sdGlwO1xuIiwiaW1wb3J0IGlsIGZyb20gJ2lsJztcbmltcG9ydCByZXBsYWNlQ29udGVudCBmcm9tICcuL2NvcmUucmVwbGFjZUNvbnRlbnQuanMnXG5pbXBvcnQgVG9vbHRpcCBmcm9tICcuL2NvcmUuVG9vbHRpcC5qcydcblxuaWwuVUkgPSBpbC5VSSB8fCB7fTtcbmlsLlVJLmNvcmUgPSBpbC5VSS5jb3JlIHx8IHt9O1xuXG5pbC5VSS5jb3JlLnJlcGxhY2VDb250ZW50ID0gcmVwbGFjZUNvbnRlbnQoJCk7XG5pbC5VSS5jb3JlLlRvb2x0aXAgPSBUb29sdGlwO1xuXG4iXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7Ozs7O0lBQUE7SUFDQTtJQUNBO0lBQ0E7SUFDQTtJQUNBO0lBQ0E7SUFDQSxJQUFJLGNBQWMsR0FBRyxTQUFTLENBQUMsRUFBRTtJQUNqQyxJQUFJLE9BQU8sVUFBVSxFQUFFLEVBQUUs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
