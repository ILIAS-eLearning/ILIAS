(function () {
    'use strict';

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
        #main;

        constructor(element) {
            this.container = element.parentElement;
            this.element = element;
            this.document = element.ownerDocument;
            this.window = this.document.defaultView || this.document.parentWindow;

            var tooltip_id = this.element.getAttribute("aria-describedby");
            if (tooltip_id === null) {
                throw new Error("Could not find expected attribute aria-describedby for element with tooltip.");
            }

            this.tooltip = this.document.getElementById(tooltip_id);
            if (this.tooltip === null) {
                throw new Error("Tooltip " + tooltip_id + " not found.", {cause: this.element});
            }

            var main = this.document.getElementsByTagName("main");
            if (main.length !== 1) {
                console.log("Tooltip could not find single main-element in page. Assuming that it does not matter...");
                this.main = null;
            }

            if (main[0].contains(this.tooltip)) {
                this.main = main[0];
            }
            else {
                this.main = null;
            }


            this.showTooltip = this.showTooltip.bind(this);
            this.hideTooltip = this.hideTooltip.bind(this);
            this.onKeyDown = this.onKeyDown.bind(this);
            this.onPointerDown = this.onPointerDown.bind(this);

            this.bindElementEvents();
            this.bindContainerEvents();
        }

        /**
         * @returns {undefined}
         */
        showTooltip() {
            this.container.classList.add("c-tooltip--visible");
            this.bindDocumentEvents();

            this.checkVerticalBounds();
            this.checkHorizontalBounds();
        }

        /**
         * @returns {undefined}
         */
        hideTooltip() {
            this.container.classList.remove("c-tooltip--visible");
            this.unbindDocumentEvents();

            this.container.classList.remove("c-tooltip--top");
            this.tooltip.style.transform = null;
        }

        /**
         * @returns {undefined}
         */
        bindElementEvents() {
            this.element.addEventListener("focus", this.showTooltip);
            this.element.addEventListener("blur", this.hideTooltip);
        }

        /**
         * @returns {undefined}
         */
        bindContainerEvents() {
            this.container.addEventListener("mouseenter", this.showTooltip);
            this.container.addEventListener("touchstart", this.showTooltip);
            this.container.addEventListener("mouseleave", this.hideTooltip);
        }

        /**
         * @returns {undefined}
         */
        bindDocumentEvents() {
            this.document.addEventListener("keydown", this.onKeyDown);
            this.document.addEventListener("pointerdown", this.onPointerDown);
        }

        /**
         * @returns {undefined}
         */
        unbindDocumentEvents() {
            this.document.removeEventListener("keydown", this.onKeyDown);
            this.document.removeEventListener("pointerdown", this.onPointerDown);
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
            if(event.target === this.element || event.target === this.tooltip) {
                event.preventDefault();
            }
            else {
                this.hideTooltip();
                this.element.blur();
            }
        }

        /**
         * @returns {undefined}
         */
        checkVerticalBounds() {
            var ttRect = this.tooltip.getBoundingClientRect();
            var dpRect = this.getDisplayRect();

            if (ttRect.bottom > (dpRect.top + dpRect.height)) {
                this.container.classList.add("c-tooltip--top");
            }
        }

        /**
         * @returns {undefined}
         */
        checkHorizontalBounds() {
            var ttRect = this.tooltip.getBoundingClientRect();
            var dpRect = this.getDisplayRect();

            if ((dpRect.width - dpRect.left) < ttRect.right) {
                this.tooltip.style.transform = "translateX(" + ((dpRect.width - dpRect.left) - ttRect.right) + "px)";
            }
            if (ttRect.left < dpRect.left) {
                this.tooltip.style.transform = "translateX(" + ((dpRect.left - ttRect.left) - ttRect.width/2) + "px)";
            }
        }

        /**
         * @returns {{left: number, top: number, width: number, height: number}}
         */
        getDisplayRect() {
            if (this.main !== null) {
                return this.main.getBoundingClientRect();
            }

            return {
                left: 0,
                top: 0,
                width: this.window.innerWidth,
                height: this.window.innerHeight
            }
        }
    }

    if (typeof il === 'undefined') {
        var il = {};
    }
    il.UI = il.UI || {};
    il.UI.core = il.UI.core || {};

    il.UI.core.replaceContent = replaceContent($);
    il.UI.core.Tooltip = Tooltip;

})();
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidWkuanMiLCJzb3VyY2VzIjpbIi4uL3NyYy9jb3JlLnJlcGxhY2VDb250ZW50LmpzIiwiLi4vc3JjL2NvcmUuVG9vbHRpcC5qcyIsIi4uL3NyYy9jb3JlLmpzIl0sInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogUmVwbGFjZSBhIGNvbXBvbmVudCBvciBwYXJ0cyBvZiBhIGNvbXBvbmVudCB1c2luZyBhamF4IGNhbGxcbiAqXG4gKiBAcGFyYW0gaWQgY29tcG9uZW50IGlkXG4gKiBAcGFyYW0gdXJsIHJlcGxhY2VtZW50IHVybFxuICogQHBhcmFtIG1hcmtlciByZXBsYWNlbWVudCBtYXJrZXIgKFwiY29tcG9uZW50XCIsIFwiY29udGVudFwiLCBcImhlYWRlclwiLCAuLi4pXG4gKi9cbnZhciByZXBsYWNlQ29udGVudCA9IGZ1bmN0aW9uKCQpIHtcbiAgICByZXR1cm4gZnVuY3Rpb24gKGlkLCB1cmwsIG1hcmtlcikge1xuICAgICAgICAvLyBnZXQgbmV3IHN0dWZmIHZpYSBhamF4XG4gICAgICAgICQuYWpheCh7XG4gICAgICAgICAgICB1cmw6IHVybCxcbiAgICAgICAgICAgIGRhdGFUeXBlOiAnaHRtbCdcbiAgICAgICAgfSkuZG9uZShmdW5jdGlvbihodG1sKSB7XG4gICAgICAgICAgICB2YXIgJG5ld19jb250ZW50ID0gJChcIjxkaXY+XCIgKyBodG1sICsgXCI8L2Rpdj5cIik7XG4gICAgICAgICAgICB2YXIgJG1hcmtlZF9uZXdfY29udGVudCA9ICRuZXdfY29udGVudC5maW5kKFwiW2RhdGEtcmVwbGFjZS1tYXJrZXI9J1wiICsgbWFya2VyICsgXCInXVwiKS5maXJzdCgpO1xuXG4gICAgICAgICAgICBpZiAoJG1hcmtlZF9uZXdfY29udGVudC5sZW5ndGggPT0gMCkge1xuXG4gICAgICAgICAgICAgICAgLy8gaWYgbWFya2VyIGRvZXMgbm90IGNvbWUgd2l0aCB0aGUgbmV3IGNvbnRlbnQsIHdlIHB1dCB0aGUgbmV3IGNvbnRlbnQgaW50byB0aGUgZXhpc3RpbmcgbWFya2VyXG4gICAgICAgICAgICAgICAgLy8gKHRoaXMgaW5jbHVkZXMgYWxsIHNjcmlwdCB0YWdzIGFscmVhZHkpXG4gICAgICAgICAgICAgICAgJChcIiNcIiArIGlkICsgXCIgW2RhdGEtcmVwbGFjZS1tYXJrZXI9J1wiICsgbWFya2VyICsgXCInXVwiKS5odG1sKGh0bWwpO1xuXG4gICAgICAgICAgICB9IGVsc2Uge1xuXG4gICAgICAgICAgICAgICAgLy8gaWYgbWFya2VyIGlzIGluIG5ldyBjb250ZW50LCB3ZSByZXBsYWNlIHRoZSBjb21wbGV0ZSBvbGQgbm9kZSB3aXRoIHRoZSBtYXJrZXJcbiAgICAgICAgICAgICAgICAvLyB3aXRoIHRoZSBuZXcgbWFya2VkIG5vZGVcbiAgICAgICAgICAgICAgICAkKFwiI1wiICsgaWQgKyBcIiBbZGF0YS1yZXBsYWNlLW1hcmtlcj0nXCIgKyBtYXJrZXIgKyBcIiddXCIpLmZpcnN0KClcbiAgICAgICAgICAgICAgICAgICAgLnJlcGxhY2VXaXRoKCRtYXJrZWRfbmV3X2NvbnRlbnQpO1xuXG4gICAgICAgICAgICAgICAgLy8gYXBwZW5kIGluY2x1ZGVkIHNjcmlwdCAod2hpY2ggd2lsbCBub3QgYmUgcGFydCBvZiB0aGUgbWFya2VkIG5vZGVcbiAgICAgICAgICAgICAgICAkKFwiI1wiICsgaWQgKyBcIiBbZGF0YS1yZXBsYWNlLW1hcmtlcj0nXCIgKyBtYXJrZXIgKyBcIiddXCIpLmZpcnN0KClcbiAgICAgICAgICAgICAgICAgICAgLmFmdGVyKCRuZXdfY29udGVudC5maW5kKFwiW2RhdGEtcmVwbGFjZS1tYXJrZXI9J3NjcmlwdCddXCIpKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG4gICAgfVxufTtcblxuZXhwb3J0IGRlZmF1bHQgcmVwbGFjZUNvbnRlbnQ7XG4iLCIvKipcbiAqIFRoaXMgcmVwcmVzZW50cyBvbmUgdG9vbHRpcCBvbiB0aGUgcGFnZS5cbiAqL1xuY2xhc3MgVG9vbHRpcCB7XG4gICAgLyoqXG4gICAgICogVGhlIHRvb2x0aXAgZWxlbWVudCBpdHNlbGYuXG4gICAgICogQHR5cGUge0VsZW1lbnR9XG4gICAgICovXG4gICAgI2VsZW1lbnQ7XG5cbiAgICAvKipcbiAgICAgKiBUaGUgY29udGFpbmVyIG9mIHRoZSB0b29sdGlwIGFuZCB0aGUgdHJpZ2dlciBlbGVtZW50LlxuICAgICAqIEB0eXBlIHtFbGVtZW50fVxuICAgICAqL1xuICAgICNjb250YWluZXI7XG5cbiAgICAvKipcbiAgICAgKiBUaGUgSFRNTERvY3VtZW50IHRoaXMgYWxsIGV4aXN0cyBpbnNpZGUuXG4gICAgICogQHR5cGUge0hUTUxEb2N1bWVudH1cbiAgICAgKi9cbiAgICAjZG9jdW1lbnQ7XG5cbiAgICAvKipcbiAgICAgKiBUaGUgV2luZG93IHRocm91Z2ggd2hpY2ggd2Ugc2VlIHRoYXQgc3R1ZmYuXG4gICAgICogQHR5cGUge1dpbmRvd31cbiAgICAgKi9cbiAgICAjd2luZG93O1xuXG4gICAgLyoqXG4gICAgICogVGhpcyB3aWxsIGJlIHRoZSBcIm1haW5cIi1jb250YWluZXIgaWYgdGhlIHRvb2x0aXAgaXMgaW5zaWRlIG9uZS5cbiAgICAgKiBAdHlwZSB7P0VsZW1lbnR9XG4gICAgICovXG4gICAgI21haW47XG5cbiAgICBjb25zdHJ1Y3RvcihlbGVtZW50KSB7XG4gICAgICAgIHRoaXMuY29udGFpbmVyID0gZWxlbWVudC5wYXJlbnRFbGVtZW50O1xuICAgICAgICB0aGlzLmVsZW1lbnQgPSBlbGVtZW50O1xuICAgICAgICB0aGlzLmRvY3VtZW50ID0gZWxlbWVudC5vd25lckRvY3VtZW50O1xuICAgICAgICB0aGlzLndpbmRvdyA9IHRoaXMuZG9jdW1lbnQuZGVmYXVsdFZpZXcgfHwgdGhpcy5kb2N1bWVudC5wYXJlbnRXaW5kb3c7XG5cbiAgICAgICAgdmFyIHRvb2x0aXBfaWQgPSB0aGlzLmVsZW1lbnQuZ2V0QXR0cmlidXRlKFwiYXJpYS1kZXNjcmliZWRieVwiKTtcbiAgICAgICAgaWYgKHRvb2x0aXBfaWQgPT09IG51bGwpIHtcbiAgICAgICAgICAgIHRocm93IG5ldyBFcnJvcihcIkNvdWxkIG5vdCBmaW5kIGV4cGVjdGVkIGF0dHJpYnV0ZSBhcmlhLWRlc2NyaWJlZGJ5IGZvciBlbGVtZW50IHdpdGggdG9vbHRpcC5cIik7XG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLnRvb2x0aXAgPSB0aGlzLmRvY3VtZW50LmdldEVsZW1lbnRCeUlkKHRvb2x0aXBfaWQpO1xuICAgICAgICBpZiAodGhpcy50b29sdGlwID09PSBudWxsKSB7XG4gICAgICAgICAgICB0aHJvdyBuZXcgRXJyb3IoXCJUb29sdGlwIFwiICsgdG9vbHRpcF9pZCArIFwiIG5vdCBmb3VuZC5cIiwge2NhdXNlOiB0aGlzLmVsZW1lbnR9KTtcbiAgICAgICAgfVxuXG4gICAgICAgIHZhciBtYWluID0gdGhpcy5kb2N1bWVudC5nZXRFbGVtZW50c0J5VGFnTmFtZShcIm1haW5cIik7XG4gICAgICAgIGlmIChtYWluLmxlbmd0aCAhPT0gMSkge1xuICAgICAgICAgICAgY29uc29sZS5sb2coXCJUb29sdGlwIGNvdWxkIG5vdCBmaW5kIHNpbmdsZSBtYWluLWVsZW1lbnQgaW4gcGFnZS4gQXNzdW1pbmcgdGhhdCBpdCBkb2VzIG5vdCBtYXR0ZXIuLi5cIik7XG4gICAgICAgICAgICB0aGlzLm1haW4gPSBudWxsO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKG1haW5bMF0uY29udGFpbnModGhpcy50b29sdGlwKSkge1xuICAgICAgICAgICAgdGhpcy5tYWluID0gbWFpblswXTtcbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgIHRoaXMubWFpbiA9IG51bGw7XG4gICAgICAgIH1cblxuXG4gICAgICAgIHRoaXMuc2hvd1Rvb2x0aXAgPSB0aGlzLnNob3dUb29sdGlwLmJpbmQodGhpcyk7XG4gICAgICAgIHRoaXMuaGlkZVRvb2x0aXAgPSB0aGlzLmhpZGVUb29sdGlwLmJpbmQodGhpcyk7XG4gICAgICAgIHRoaXMub25LZXlEb3duID0gdGhpcy5vbktleURvd24uYmluZCh0aGlzKTtcbiAgICAgICAgdGhpcy5vblBvaW50ZXJEb3duID0gdGhpcy5vblBvaW50ZXJEb3duLmJpbmQodGhpcyk7XG5cbiAgICAgICAgdGhpcy5iaW5kRWxlbWVudEV2ZW50cygpO1xuICAgICAgICB0aGlzLmJpbmRDb250YWluZXJFdmVudHMoKTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBAcmV0dXJucyB7dW5kZWZpbmVkfVxuICAgICAqL1xuICAgIHNob3dUb29sdGlwKCkge1xuICAgICAgICB0aGlzLmNvbnRhaW5lci5jbGFzc0xpc3QuYWRkKFwiYy10b29sdGlwLS12aXNpYmxlXCIpO1xuICAgICAgICB0aGlzLmJpbmREb2N1bWVudEV2ZW50cygpO1xuXG4gICAgICAgIHRoaXMuY2hlY2tWZXJ0aWNhbEJvdW5kcygpO1xuICAgICAgICB0aGlzLmNoZWNrSG9yaXpvbnRhbEJvdW5kcygpO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEByZXR1cm5zIHt1bmRlZmluZWR9XG4gICAgICovXG4gICAgaGlkZVRvb2x0aXAoKSB7XG4gICAgICAgIHRoaXMuY29udGFpbmVyLmNsYXNzTGlzdC5yZW1vdmUoXCJjLXRvb2x0aXAtLXZpc2libGVcIik7XG4gICAgICAgIHRoaXMudW5iaW5kRG9jdW1lbnRFdmVudHMoKTtcblxuICAgICAgICB0aGlzLmNvbnRhaW5lci5jbGFzc0xpc3QucmVtb3ZlKFwiYy10b29sdGlwLS10b3BcIik7XG4gICAgICAgIHRoaXMudG9vbHRpcC5zdHlsZS50cmFuc2Zvcm0gPSBudWxsO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEByZXR1cm5zIHt1bmRlZmluZWR9XG4gICAgICovXG4gICAgYmluZEVsZW1lbnRFdmVudHMoKSB7XG4gICAgICAgIHRoaXMuZWxlbWVudC5hZGRFdmVudExpc3RlbmVyKFwiZm9jdXNcIiwgdGhpcy5zaG93VG9vbHRpcCk7XG4gICAgICAgIHRoaXMuZWxlbWVudC5hZGRFdmVudExpc3RlbmVyKFwiYmx1clwiLCB0aGlzLmhpZGVUb29sdGlwKTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBAcmV0dXJucyB7dW5kZWZpbmVkfVxuICAgICAqL1xuICAgIGJpbmRDb250YWluZXJFdmVudHMoKSB7XG4gICAgICAgIHRoaXMuY29udGFpbmVyLmFkZEV2ZW50TGlzdGVuZXIoXCJtb3VzZWVudGVyXCIsIHRoaXMuc2hvd1Rvb2x0aXApO1xuICAgICAgICB0aGlzLmNvbnRhaW5lci5hZGRFdmVudExpc3RlbmVyKFwidG91Y2hzdGFydFwiLCB0aGlzLnNob3dUb29sdGlwKTtcbiAgICAgICAgdGhpcy5jb250YWluZXIuYWRkRXZlbnRMaXN0ZW5lcihcIm1vdXNlbGVhdmVcIiwgdGhpcy5oaWRlVG9vbHRpcCk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQHJldHVybnMge3VuZGVmaW5lZH1cbiAgICAgKi9cbiAgICBiaW5kRG9jdW1lbnRFdmVudHMoKSB7XG4gICAgICAgIHRoaXMuZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcihcImtleWRvd25cIiwgdGhpcy5vbktleURvd24pXG4gICAgICAgIHRoaXMuZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcihcInBvaW50ZXJkb3duXCIsIHRoaXMub25Qb2ludGVyRG93bilcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBAcmV0dXJucyB7dW5kZWZpbmVkfVxuICAgICAqL1xuICAgIHVuYmluZERvY3VtZW50RXZlbnRzKCkge1xuICAgICAgICB0aGlzLmRvY3VtZW50LnJlbW92ZUV2ZW50TGlzdGVuZXIoXCJrZXlkb3duXCIsIHRoaXMub25LZXlEb3duKVxuICAgICAgICB0aGlzLmRvY3VtZW50LnJlbW92ZUV2ZW50TGlzdGVuZXIoXCJwb2ludGVyZG93blwiLCB0aGlzLm9uUG9pbnRlckRvd24pXG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQHJldHVybnMge3VuZGVmaW5lZH1cbiAgICAgKi9cbiAgICBvbktleURvd24oZXZlbnQpIHtcbiAgICAgICAgaWYgKGV2ZW50LmtleSA9PT0gXCJFc2NcIiB8fCBldmVudC5rZXkgPT09IFwiRXNjYXBlXCIpIHtcbiAgICAgICAgICAgIHRoaXMuaGlkZVRvb2x0aXAoKTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEByZXR1cm5zIHt1bmRlZmluZWR9XG4gICAgICovXG4gICAgb25Qb2ludGVyRG93bihldmVudCkge1xuICAgICAgICBpZihldmVudC50YXJnZXQgPT09IHRoaXMuZWxlbWVudCB8fCBldmVudC50YXJnZXQgPT09IHRoaXMudG9vbHRpcCkge1xuICAgICAgICAgICAgZXZlbnQucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgIHRoaXMuaGlkZVRvb2x0aXAoKTtcbiAgICAgICAgICAgIHRoaXMuZWxlbWVudC5ibHVyKCk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBAcmV0dXJucyB7dW5kZWZpbmVkfVxuICAgICAqL1xuICAgIGNoZWNrVmVydGljYWxCb3VuZHMoKSB7XG4gICAgICAgIHZhciB0dFJlY3QgPSB0aGlzLnRvb2x0aXAuZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCk7XG4gICAgICAgIHZhciBkcFJlY3QgPSB0aGlzLmdldERpc3BsYXlSZWN0KCk7XG5cbiAgICAgICAgaWYgKHR0UmVjdC5ib3R0b20gPiAoZHBSZWN0LnRvcCArIGRwUmVjdC5oZWlnaHQpKSB7XG4gICAgICAgICAgICB0aGlzLmNvbnRhaW5lci5jbGFzc0xpc3QuYWRkKFwiYy10b29sdGlwLS10b3BcIik7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBAcmV0dXJucyB7dW5kZWZpbmVkfVxuICAgICAqL1xuICAgIGNoZWNrSG9yaXpvbnRhbEJvdW5kcygpIHtcbiAgICAgICAgdmFyIHR0UmVjdCA9IHRoaXMudG9vbHRpcC5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKTtcbiAgICAgICAgdmFyIGRwUmVjdCA9IHRoaXMuZ2V0RGlzcGxheVJlY3QoKTtcblxuICAgICAgICBpZiAoKGRwUmVjdC53aWR0aCAtIGRwUmVjdC5sZWZ0KSA8IHR0UmVjdC5yaWdodCkge1xuICAgICAgICAgICAgdGhpcy50b29sdGlwLnN0eWxlLnRyYW5zZm9ybSA9IFwidHJhbnNsYXRlWChcIiArICgoZHBSZWN0LndpZHRoIC0gZHBSZWN0LmxlZnQpIC0gdHRSZWN0LnJpZ2h0KSArIFwicHgpXCI7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKHR0UmVjdC5sZWZ0IDwgZHBSZWN0LmxlZnQpIHtcbiAgICAgICAgICAgIHRoaXMudG9vbHRpcC5zdHlsZS50cmFuc2Zvcm0gPSBcInRyYW5zbGF0ZVgoXCIgKyAoKGRwUmVjdC5sZWZ0IC0gdHRSZWN0LmxlZnQpIC0gdHRSZWN0LndpZHRoLzIpICsgXCJweClcIjtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEByZXR1cm5zIHt7bGVmdDogbnVtYmVyLCB0b3A6IG51bWJlciwgd2lkdGg6IG51bWJlciwgaGVpZ2h0OiBudW1iZXJ9fVxuICAgICAqL1xuICAgIGdldERpc3BsYXlSZWN0KCkge1xuICAgICAgICBpZiAodGhpcy5tYWluICE9PSBudWxsKSB7XG4gICAgICAgICAgICByZXR1cm4gdGhpcy5tYWluLmdldEJvdW5kaW5nQ2xpZW50UmVjdCgpO1xuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgIGxlZnQ6IDAsXG4gICAgICAgICAgICB0b3A6IDAsXG4gICAgICAgICAgICB3aWR0aDogdGhpcy53aW5kb3cuaW5uZXJXaWR0aCxcbiAgICAgICAgICAgIGhlaWdodDogdGhpcy53aW5kb3cuaW5uZXJIZWlnaHRcbiAgICAgICAgfVxuICAgIH1cbn1cblxuZXhwb3J0IGRlZmF1bHQgVG9vbHRpcDtcbiIsImltcG9ydCByZXBsYWNlQ29udGVudCBmcm9tICcuL2NvcmUucmVwbGFjZUNvbnRlbnQuanMnXG5pbXBvcnQgVG9vbHRpcCBmcm9tICcuL2NvcmUuVG9vbHRpcC5qcydcblxuaWYgKHR5cGVvZiBpbCA9PT0gJ3VuZGVmaW5lZCcpIHtcbiAgICB2YXIgaWwgPSB7fVxufVxuaWwuVUkgPSBpbC5VSSB8fCB7fTtcbmlsLlVJLmNvcmUgPSBpbC5VSS5jb3JlIHx8IHt9O1xuXG5pbC5VSS5jb3JlLnJlcGxhY2VDb250ZW50ID0gcmVwbGFjZUNvbnRlbnQoJCk7XG5pbC5VSS5jb3JlLlRvb2x0aXAgPSBUb29sdGlwO1xuXG4iXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7O0lBQUE7SUFDQTtJQUNBO0lBQ0E7SUFDQTtJQUNBO0lBQ0E7SUFDQSxJQUFJLGNBQWMsR0FBRyxTQUFTLENBQUMsRUFBRTtJQUNqQyxJQUFJLE9BQU8sVUFBVSxFQUFFLEVBQUUsR0FBRyxFQUFFLE1BQU0sRUFBRTtJQUN0QztJQUNBLFFBQVEsQ0FBQyxDQUFDLElBQUksQ0FBQztJQUNmLFlBQVksR0FBRyxFQUFFLEdBQUc7SUFDcEIsWUFBWSxRQUFRLEVBQUUsTUFBTTtJQUM1QixTQUFTLENBQUMsQ0FBQyxJQUFJLENBQUMsU0FBUyxJQUFJLEVBQUU7SUFDL0IsWUFBWSxJQUFJLFlBQVksR0FBRyxDQUFDLENBQUMsT0FBTyxHQUFHLElBQUksR0FBRyxRQUFRLENBQUMsQ0FBQztJQUM1RCxZQUFZLElBQUksbUJBQW1CLEdBQUcsWUFBWSxDQUFDLElBQUksQ0FBQyx3QkFBd0IsR0FBRyxNQUFNLEdBQUcsSUFBSSxDQUFDLENBQUMsS0FBSyxFQUFFLENBQUM7QUFDMUc7SUFDQSxZQUFZLElBQUksbUJBQW1CLENBQUMsTUFBTSxJQUFJLENBQUMsRUFBRTtBQUNqRDtJQUNBO0lBQ0E7SUFDQSxnQkFBZ0IsQ0FBQyxDQUFDLEdBQUcsR0FBRyxFQUFFLEdBQUcseUJBQXlCLEdBQUcsTUFBTSxHQUFHLElBQUksQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQztBQUNuRjtJQUNBLGFBQWEsTUFBTTtBQUNuQjtJQUNBO0lBQ0E7SUFDQSxnQkFBZ0IsQ0FBQyxDQUFD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
