var il = il || {};
(function (il) {
    'use strict';

    function _interopDefaultLegacy (e) { return e && typeof e === 'object' && 'default' in e ? e : { 'default': e }; }

    var il__default = /*#__PURE__*/_interopDefaultLegacy(il);

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

    class URLBuilderToken {
      /**
         * @type {number}
         */
      static TOKEN_LENGTH = 24;

      /**
       * @type {string}
       */
      static SEPARATOR = '_';

      /**
         * @type {string[]}
         */
      #namespace = [];

      /**
         * @type {string}
         */
      #name = '';

      /**
         * @type {string|null}
         */
      #token = null;

      /**
         * @param {string[]} namespace
         * @param {string} name
         * @param {string|null} token
         */
      constructor(namespace, name, token = null) {
        this.#namespace = namespace;
        this.#name = name;
        this.#token = token;
        if (this.#token === null) {
          this.#createToken();
        }
      }

      /**
         * @returns {string}
         */
      get token() {
        return this.#token;
      }

      /**
         * @returns {string}
         */
      getName() {
        return this.#namespace.join(URLBuilderToken.SEPARATOR) + URLBuilderToken.SEPARATOR + this.#name;
      }

      /**
         * @returns {void}
         */
      #createToken() {
        let token = '';
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        const charactersLength = characters.length;
        while (token.length < URLBuilderToken.TOKEN_LENGTH) {
          token += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        this.#token = token;
      }
    }

    class URLBuilder {
      /**
         * @type {number}
         */
      static URL_MAX_LENGTH = 2048;

      /**
         * @type {string}
         */
      static SEPARATOR = '_';

      /**
         * @type {URL}
         */
      #url = null;

      /**
         * @type {string}
         */
      #base_url = '';

      /**
         * @type {string}
         */
      #query = '';

      /**
         * @type {string}
         */
      #fragment = '';

      /**
         * @type {Map}
         */
      #parameters = new Map();

      /**
         * @type {Map}
         */
      #tokens;

      /**
         * New objects will usually be created by code rendered
         * from Data/URLBuilder on the PHP side.
         *
         * @param {string} url
         * @param {Map<URLBuilderToken>} tokens
         */
      constructor(url, tokens = new Map()) {
        this.#url = new URL(url);
        this.#tokens = tokens;
        this.#analyzeURL();
      }

      /**
         * @returns {void}
         */
      #analyzeURL() {
        const url = this.#url;
        this.#query = url.search.slice(1);
        this.#base_url = url.origin + url.pathname;
        const slices = this.#query.split('&');
        slices.forEach((slice) => {
          const parameter = slice.split('=');
          this.#parameters.set(parameter[0], parameter[1]);
        });
        this.#fragment = this.#url.hash.slice(1);
      }

      /**
         * Get the full URL including query string and fragment/hash
         *
         * @returns {string}
         */
      getUrl() {
        let url = this.#base_url;
        if (this.#parameters.size > 0) {
          url += '?';
          this.#parameters.forEach(
            (value, key) => {
              url += `${encodeURIComponent(key)}=${encodeURIComponent(value)}&`;
            },
          );
          url = url.slice(0, url.length - 1);
        }
        if (this.#fragment !== '') { url += `#${this.#fragment}`; }
        return url;
      }

      /**
         * Change the fragment/hash part of the URL
         *
         * @param {string} fragment
         */
      set fragment(fragment) {
        this.#fragment = fragment;
      }

      /**
         * Add a new parameter with a namespace
         * and get its token for subsequent changes.
         *
         * The namespace can consists of one or more levels
         * which are noted as an array. They will be joined
         * with the separator (see constant) and used as a
         * prefix for the name, e.g.
         * Namespace: ["ilOrgUnit","filter"]
         * Name: "name"
         * Resulting parameter: "ilOrgUnit_filter_name"
         *
         * @param {string[]} namespace
         * @param {string} name
         * @param {string|null} value
         * @returns {(URLBuilder|URLBuilderToken)[]}
         */
      acquireParameter(namespace, name, value = null) {
        if (name === '' || namespace.length === 0) {
          throw new Error('Parameter name or namespace not set');
        }

        const parameter = namespace.join(URLBuilder.SEPARATOR) + URLBuilder.SEPARATOR + name;
        if (this.#parameterExists(parameter)) {
          throw new Error(`Parameter '${parameter}' already exists in URL`);
        }

        const token = new URLBuilderToken(namespace, name);
        this.#parameters.set(parameter, value ?? '');
        this.#tokens.set(parameter, token);
        this.#checkLength();

        return [this, token];
      }

      /**
         * Delete a parameter if the supplied token is valid
         *
         * @param {URLBuilderToken} token
         * @returns {URLBuilder}
         */
      deleteParameter(token) {
        this.#checkToken(token);
        this.#parameters.delete(token.getName());
        this.#tokens.delete(token.getName());
        return this;
      }

      /**
         * Change a parameter's value if the supplied token is valid
         *
         * @param {URLBuilderToken} token
         * @param {string} value
         * @returns {URLBuilder}
         */
      writeParameter(token, value) {
        this.#checkToken(token);
        this.#parameters.set(token.getName(), value);
        this.#checkLength();
        return this;
      }

      /**
         * Check if parameter already exists
         *
         * @param {string} parameter
         * @returns {boolean}
         */
      #parameterExists(parameter) {
        return this.#parameters.has(parameter);
      }

      /**
         * Check if a token is valid
         *
         * @param {URLBuilderToken} token
         * @returns {void}
         * @throws Exception
         */
      #checkToken(token) {
        if ((token instanceof URLBuilderToken) !== true) {
          throw new Error('Token is not valid');
        }
        if (!this.#tokens.has(token.getName())
                || this.#tokens.get(token.getName()).token !== token.token) {
          throw new Error(`Token for '${token.getName()}' is not valid`);
        }
        if (!this.#parameters.has(token.getName())) {
          throw new Error(`Parameter '${token.getName()}' does not exist in URL`);
        }
      }

      /**
         * Check the full length of the URL against URL_MAX_LENGTH
         *
         * @returns {void}
         * @throws Exception
         */
      #checkLength() {
        if (!(this.getUrl().length <= URLBuilder.URL_MAX_LENGTH)) {
          throw new Error(`The final URL is longer than ${URLBuilder.URL_MAX_LENGTH} and will not be valid.`);
        }
      }
    }

    il__default["default"].UI = il__default["default"].UI || {};
    il__default["default"].UI.core = il__default["default"].UI.core || {};

    il__default["default"].UI.core.replaceContent = replaceContent($);
    il__default["default"].UI.core.Tooltip = Tooltip;
    il__default["default"].UI.core.URLBuilder = URLBuilder;
    il__default["default"].UI.core.URLBuilderToken = URLBuilderToken;

})(il);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidWkuanMiLCJzb3VyY2VzIjpbIi4uL3NyYy9jb3JlLnJlcGxhY2VDb250ZW50LmpzIiwiLi4vc3JjL2NvcmUuVG9vbHRpcC5qcyIsIi4uL3NyYy9jb3JlLlVSTEJ1aWxkZXJUb2tlbi5qcyIsIi4uL3NyYy9jb3JlLlVSTEJ1aWxkZXIuanMiLCIuLi9zcmMvY29yZS5qcyJdLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIFJlcGxhY2UgYSBjb21wb25lbnQgb3IgcGFydHMgb2YgYSBjb21wb25lbnQgdXNpbmcgYWpheCBjYWxsXG4gKlxuICogQHBhcmFtIGlkIGNvbXBvbmVudCBpZFxuICogQHBhcmFtIHVybCByZXBsYWNlbWVudCB1cmxcbiAqIEBwYXJhbSBtYXJrZXIgcmVwbGFjZW1lbnQgbWFya2VyIChcImNvbXBvbmVudFwiLCBcImNvbnRlbnRcIiwgXCJoZWFkZXJcIiwgLi4uKVxuICovXG52YXIgcmVwbGFjZUNvbnRlbnQgPSBmdW5jdGlvbigkKSB7XG4gICAgcmV0dXJuIGZ1bmN0aW9uIChpZCwgdXJsLCBtYXJrZXIpIHtcbiAgICAgICAgLy8gZ2V0IG5ldyBzdHVmZiB2aWEgYWpheFxuICAgICAgICAkLmFqYXgoe1xuICAgICAgICAgICAgdXJsOiB1cmwsXG4gICAgICAgICAgICBkYXRhVHlwZTogJ2h0bWwnXG4gICAgICAgIH0pLmRvbmUoZnVuY3Rpb24oaHRtbCkge1xuICAgICAgICAgICAgdmFyICRuZXdfY29udGVudCA9ICQoXCI8ZGl2PlwiICsgaHRtbCArIFwiPC9kaXY+XCIpO1xuICAgICAgICAgICAgdmFyICRtYXJrZWRfbmV3X2NvbnRlbnQgPSAkbmV3X2NvbnRlbnQuZmluZChcIltkYXRhLXJlcGxhY2UtbWFya2VyPSdcIiArIG1hcmtlciArIFwiJ11cIikuZmlyc3QoKTtcblxuICAgICAgICAgICAgaWYgKCRtYXJrZWRfbmV3X2NvbnRlbnQubGVuZ3RoID09IDApIHtcblxuICAgICAgICAgICAgICAgIC8vIGlmIG1hcmtlciBkb2VzIG5vdCBjb21lIHdpdGggdGhlIG5ldyBjb250ZW50LCB3ZSBwdXQgdGhlIG5ldyBjb250ZW50IGludG8gdGhlIGV4aXN0aW5nIG1hcmtlclxuICAgICAgICAgICAgICAgIC8vICh0aGlzIGluY2x1ZGVzIGFsbCBzY3JpcHQgdGFncyBhbHJlYWR5KVxuICAgICAgICAgICAgICAgICQoXCIjXCIgKyBpZCArIFwiIFtkYXRhLXJlcGxhY2UtbWFya2VyPSdcIiArIG1hcmtlciArIFwiJ11cIikuaHRtbChodG1sKTtcblxuICAgICAgICAgICAgfSBlbHNlIHtcblxuICAgICAgICAgICAgICAgIC8vIGlmIG1hcmtlciBpcyBpbiBuZXcgY29udGVudCwgd2UgcmVwbGFjZSB0aGUgY29tcGxldGUgb2xkIG5vZGUgd2l0aCB0aGUgbWFya2VyXG4gICAgICAgICAgICAgICAgLy8gd2l0aCB0aGUgbmV3IG1hcmtlZCBub2RlXG4gICAgICAgICAgICAgICAgJChcIiNcIiArIGlkICsgXCIgW2RhdGEtcmVwbGFjZS1tYXJrZXI9J1wiICsgbWFya2VyICsgXCInXVwiKS5maXJzdCgpXG4gICAgICAgICAgICAgICAgICAgIC5yZXBsYWNlV2l0aCgkbWFya2VkX25ld19jb250ZW50KTtcblxuICAgICAgICAgICAgICAgIC8vIGFwcGVuZCBpbmNsdWRlZCBzY3JpcHQgKHdoaWNoIHdpbGwgbm90IGJlIHBhcnQgb2YgdGhlIG1hcmtlZCBub2RlXG4gICAgICAgICAgICAgICAgJChcIiNcIiArIGlkICsgXCIgW2RhdGEtcmVwbGFjZS1tYXJrZXI9J1wiICsgbWFya2VyICsgXCInXVwiKS5maXJzdCgpXG4gICAgICAgICAgICAgICAgICAgIC5hZnRlcigkbmV3X2NvbnRlbnQuZmluZChcIltkYXRhLXJlcGxhY2UtbWFya2VyPSdzY3JpcHQnXVwiKSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuICAgIH1cbn07XG5cbmV4cG9ydCBkZWZhdWx0IHJlcGxhY2VDb250ZW50O1xuIiwiLyoqXG4gKiBUaGlzIHJlcHJlc2VudHMgb25lIHRvb2x0aXAgb24gdGhlIHBhZ2UuXG4gKi9cbmNsYXNzIFRvb2x0aXAge1xuICAgIC8qKlxuICAgICAqIEB0eXBlIHtIVE1MRWxlbWVudH1cbiAgICAgKi9cbiAgICAjdG9vbHRpcDtcblxuICAgIC8qKlxuICAgICAqIFRoZSB0b29sdGlwIGVsZW1lbnQgaXRzZWxmLlxuICAgICAqIEB0eXBlIHtFbGVtZW50fVxuICAgICAqL1xuICAgICNlbGVtZW50O1xuXG4gICAgLyoqXG4gICAgICogVGhlIGNvbnRhaW5lciBvZiB0aGUgdG9vbHRpcCBhbmQgdGhlIHRyaWdnZXIgZWxlbWVudC5cbiAgICAgKiBAdHlwZSB7RWxlbWVudH1cbiAgICAgKi9cbiAgICAjY29udGFpbmVyO1xuXG4gICAgLyoqXG4gICAgICogVGhlIEhUTUxEb2N1bWVudCB0aGlzIGFsbCBleGlzdHMgaW5zaWRlLlxuICAgICAqIEB0eXBlIHtIVE1MRG9jdW1lbnR9XG4gICAgICovXG4gICAgI2RvY3VtZW50O1xuXG4gICAgLyoqXG4gICAgICogVGhlIFdpbmRvdyB0aHJvdWdoIHdoaWNoIHdlIHNlZSB0aGF0IHN0dWZmLlxuICAgICAqIEB0eXBlIHtXaW5kb3d9XG4gICAgICovXG4gICAgI3dpbmRvdztcblxuICAgIC8qKlxuICAgICAqIFRoaXMgd2lsbCBiZSB0aGUgXCJtYWluXCItY29udGFpbmVyIGlmIHRoZSB0b29sdGlwIGlzIGluc2lkZSBvbmUuXG4gICAgICogQHR5cGUgez9FbGVtZW50fVxuICAgICAqL1xuICAgICNtYWluID0gbnVsbDtcblxuICAgIGNvbnN0cnVjdG9yKGVsZW1lbnQpIHtcbiAgICAgICAgdGhpcy4jY29udGFpbmVyID0gZWxlbWVudC5wYXJlbnRFbGVtZW50O1xuICAgICAgICB0aGlzLiNlbGVtZW50ID0gZWxlbWVudDtcbiAgICAgICAgdGhpcy4jZG9jdW1lbnQgPSBlbGVtZW50Lm93bmVyRG9jdW1lbnQ7XG4gICAgICAgIHRoaXMuI3dpbmRvdyA9IHRoaXMuI2RvY3VtZW50LmRlZmF1bHRWaWV3IHx8IHRoaXMuI2RvY3VtZW50LnBhcmVudFdpbmRvdztcblxuICAgICAgICB2YXIgdG9vbHRpcF9pZCA9IHRoaXMuI2VsZW1lbnQuZ2V0QXR0cmlidXRlKFwiYXJpYS1kZXNjcmliZWRieVwiKTtcbiAgICAgICAgaWYgKHRvb2x0aXBfaWQgPT09IG51bGwpIHtcbiAgICAgICAgICAgIHRocm93IG5ldyBFcnJvcihcIkNvdWxkIG5vdCBmaW5kIGV4cGVjdGVkIGF0dHJpYnV0ZSBhcmlhLWRlc2NyaWJlZGJ5IGZvciBlbGVtZW50IHdpdGggdG9vbHRpcC5cIik7XG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLiN0b29sdGlwID0gdGhpcy4jZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQodG9vbHRpcF9pZCk7XG4gICAgICAgIGlmICh0aGlzLiN0b29sdGlwID09PSBudWxsKSB7XG4gICAgICAgICAgICB0aHJvdyBuZXcgRXJyb3IoXCJUb29sdGlwIFwiICsgdG9vbHRpcF9pZCArIFwiIG5vdCBmb3VuZC5cIiwge2NhdXNlOiB0aGlzLiNlbGVtZW50fSk7XG4gICAgICAgIH1cblxuICAgICAgICBsZXQgbWFpbiA9IGdldFZpc2libGVNYWluRWxlbWVudCh0aGlzLiNkb2N1bWVudCk7XG4gICAgICAgIGlmIChudWxsICE9PSBtYWluICYmIG1haW4uY29udGFpbnModGhpcy4jdG9vbHRpcCkpIHtcbiAgICAgICAgICAgIHRoaXMuI21haW4gPSBtYWluO1xuICAgICAgICB9XG5cbiAgICAgICAgdGhpcy5zaG93VG9vbHRpcCA9IHRoaXMuc2hvd1Rvb2x0aXAuYmluZCh0aGlzKTtcbiAgICAgICAgdGhpcy5oaWRlVG9vbHRpcCA9IHRoaXMuaGlkZVRvb2x0aXAuYmluZCh0aGlzKTtcbiAgICAgICAgdGhpcy5vbktleURvd24gPSB0aGlzLm9uS2V5RG93bi5iaW5kKHRoaXMpO1xuICAgICAgICB0aGlzLm9uUG9pbnRlckRvd24gPSB0aGlzLm9uUG9pbnRlckRvd24uYmluZCh0aGlzKTtcblxuICAgICAgICB0aGlzLmJpbmRFbGVtZW50RXZlbnRzKCk7XG4gICAgICAgIHRoaXMuYmluZENvbnRhaW5lckV2ZW50cygpO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEByZXR1cm5zIHtIVE1MRWxlbWVudH1cbiAgICAgKi9cbiAgICBnZXQgdG9vbHRpcCgpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuI3Rvb2x0aXA7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQHJldHVybnMge3VuZGVmaW5lZH1cbiAgICAgKi9cbiAgICBzaG93VG9vbHRpcCgpIHtcbiAgICAgICAgdGhpcy4jY29udGFpbmVyLmNsYXNzTGlzdC5hZGQoXCJjLXRvb2x0aXAtLXZpc2libGVcIik7XG4gICAgICAgIHRoaXMuYmluZERvY3VtZW50RXZlbnRzKCk7XG5cbiAgICAgICAgdGhpcy5jaGVja1ZlcnRpY2FsQm91bmRzKCk7XG4gICAgICAgIHRoaXMuY2hlY2tIb3Jpem9udGFsQm91bmRzKCk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQHJldHVybnMge3VuZGVmaW5lZH1cbiAgICAgKi9cbiAgICBoaWRlVG9vbHRpcCgpIHtcbiAgICAgICAgdGhpcy4jY29udGFpbmVyLmNsYXNzTGlzdC5yZW1vdmUoXCJjLXRvb2x0aXAtLXZpc2libGVcIik7XG4gICAgICAgIHRoaXMudW5iaW5kRG9jdW1lbnRFdmVudHMoKTtcblxuICAgICAgICB0aGlzLiNjb250YWluZXIuY2xhc3NMaXN0LnJlbW92ZShcImMtdG9vbHRpcC0tdG9wXCIpO1xuICAgICAgICB0aGlzLiN0b29sdGlwLnN0eWxlLnRyYW5zZm9ybSA9IG51bGw7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQHJldHVybnMge3VuZGVmaW5lZH1cbiAgICAgKi9cbiAgICBiaW5kRWxlbWVudEV2ZW50cygpIHtcbiAgICAgICAgdGhpcy4jZWxlbWVudC5hZGRFdmVudExpc3RlbmVyKFwiZm9jdXNcIiwgdGhpcy5zaG93VG9vbHRpcCk7XG4gICAgICAgIHRoaXMuI2VsZW1lbnQuYWRkRXZlbnRMaXN0ZW5lcihcImJsdXJcIiwgdGhpcy5oaWRlVG9vbHRpcCk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQHJldHVybnMge3VuZGVmaW5lZH1cbiAgICAgKi9cbiAgICBiaW5kQ29udGFpbmVyRXZlbnRzKCkge1xuICAgICAgICB0aGlzLiNjb250YWluZXIuYWRkRXZlbnRMaXN0ZW5lcihcIm1vdXNlZW50ZXJcIiwgdGhpcy5zaG93VG9vbHRpcCk7XG4gICAgICAgIHRoaXMuI2NvbnRhaW5lci5hZGRFdmVudExpc3RlbmVyKFwidG91Y2hzdGFydFwiLCB0aGlzLnNob3dUb29sdGlwKTtcbiAgICAgICAgdGhpcy4jY29udGFpbmVyLmFkZEV2ZW50TGlzdGVuZXIoXCJtb3VzZWxlYXZlXCIsIHRoaXMuaGlkZVRvb2x0aXApO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEByZXR1cm5zIHt1bmRlZmluZWR9XG4gICAgICovXG4gICAgYmluZERvY3VtZW50RXZlbnRzKCkge1xuICAgICAgICB0aGlzLiNkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKFwia2V5ZG93blwiLCB0aGlzLm9uS2V5RG93bilcbiAgICAgICAgdGhpcy4jZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcihcInBvaW50ZXJkb3duXCIsIHRoaXMub25Qb2ludGVyRG93bilcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBAcmV0dXJucyB7dW5kZWZpbmVkfVxuICAgICAqL1xuICAgIHVuYmluZERvY3VtZW50RXZlbnRzKCkge1xuICAgICAgICB0aGlzLiNkb2N1bWVudC5yZW1vdmVFdmVudExpc3RlbmVyKFwia2V5ZG93blwiLCB0aGlzLm9uS2V5RG93bilcbiAgICAgICAgdGhpcy4jZG9jdW1lbnQucmVtb3ZlRXZlbnRMaXN0ZW5lcihcInBvaW50ZXJkb3duXCIsIHRoaXMub25Qb2ludGVyRG93bilcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBAcmV0dXJucyB7dW5kZWZpbmVkfVxuICAgICAqL1xuICAgIG9uS2V5RG93bihldmVudCkge1xuICAgICAgICBpZiAoZXZlbnQua2V5ID09PSBcIkVzY1wiIHx8IGV2ZW50LmtleSA9PT0gXCJFc2NhcGVcIikge1xuICAgICAgICAgICAgdGhpcy5oaWRlVG9vbHRpcCgpO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQHJldHVybnMge3VuZGVmaW5lZH1cbiAgICAgKi9cbiAgICBvblBvaW50ZXJEb3duKGV2ZW50KSB7XG4gICAgICAgIGlmKGV2ZW50LnRhcmdldCA9PT0gdGhpcy4jZWxlbWVudCB8fCBldmVudC50YXJnZXQgPT09IHRoaXMuI3Rvb2x0aXApIHtcbiAgICAgICAgICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgICB0aGlzLmhpZGVUb29sdGlwKCk7XG4gICAgICAgICAgICB0aGlzLiNlbGVtZW50LmJsdXIoKTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEByZXR1cm5zIHt1bmRlZmluZWR9XG4gICAgICovXG4gICAgY2hlY2tWZXJ0aWNhbEJvdW5kcygpIHtcbiAgICAgICAgdmFyIHR0UmVjdCA9IHRoaXMuI3Rvb2x0aXAuZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCk7XG4gICAgICAgIHZhciBkcFJlY3QgPSB0aGlzLmdldERpc3BsYXlSZWN0KCk7XG5cbiAgICAgICAgaWYgKHR0UmVjdC5ib3R0b20gPiAoZHBSZWN0LnRvcCArIGRwUmVjdC5oZWlnaHQpKSB7XG4gICAgICAgICAgICB0aGlzLiNjb250YWluZXIuY2xhc3NMaXN0LmFkZChcImMtdG9vbHRpcC0tdG9wXCIpO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQHJldHVybnMge3VuZGVmaW5lZH1cbiAgICAgKi9cbiAgICBjaGVja0hvcml6b250YWxCb3VuZHMoKSB7XG4gICAgICAgIHZhciB0dFJlY3QgPSB0aGlzLiN0b29sdGlwLmdldEJvdW5kaW5nQ2xpZW50UmVjdCgpO1xuICAgICAgICB2YXIgZHBSZWN0ID0gdGhpcy5nZXREaXNwbGF5UmVjdCgpO1xuXG4gICAgICAgIGlmICgoZHBSZWN0LndpZHRoIC0gZHBSZWN0LmxlZnQpIDwgdHRSZWN0LnJpZ2h0KSB7XG4gICAgICAgICAgICB0aGlzLiN0b29sdGlwLnN0eWxlLnRyYW5zZm9ybSA9IFwidHJhbnNsYXRlWChcIiArICgoZHBSZWN0LndpZHRoIC0gZHBSZWN0LmxlZnQpIC0gdHRSZWN0LnJpZ2h0KSArIFwicHgpXCI7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKHR0UmVjdC5sZWZ0IDwgZHBSZWN0LmxlZnQpIHtcbiAgICAgICAgICAgIHRoaXMuI3Rvb2x0aXAuc3R5bGUudHJhbnNmb3JtID0gXCJ0cmFuc2xhdGVYKFwiICsgKChkcFJlY3QubGVmdCAtIHR0UmVjdC5sZWZ0KSAtIHR0UmVjdC53aWR0aC8yKSArIFwicHgpXCI7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBAcmV0dXJucyB7e2xlZnQ6IG51bWJlciwgdG9wOiBudW1iZXIsIHdpZHRoOiBudW1iZXIsIGhlaWdodDogbnVtYmVyfX1cbiAgICAgKi9cbiAgICBnZXREaXNwbGF5UmVjdCgpIHtcbiAgICAgICAgaWYgKHRoaXMuI21haW4gIT09IG51bGwpIHtcbiAgICAgICAgICAgIHJldHVybiB0aGlzLiNtYWluLmdldEJvdW5kaW5nQ2xpZW50UmVjdCgpO1xuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgIGxlZnQ6IDAsXG4gICAgICAgICAgICB0b3A6IDAsXG4gICAgICAgICAgICB3aWR0aDogdGhpcy4jd2luZG93LmlubmVyV2lkdGgsXG4gICAgICAgICAgICBoZWlnaHQ6IHRoaXMuI3dpbmRvdy5pbm5lckhlaWdodFxuICAgICAgICB9XG4gICAgfVxufVxuXG4vKipcbiAqIFJldHVybnMgdGhlIHZpc2libGUgbWFpbi1lbGVtZW50IG9mIHRoZSBnaXZlbiBkb2N1bWVudC5cbiAqXG4gKiBBIGRvY3VtZW50IG1heSBjb250YWluIG11bHRpcGxlIG1haW4tZWxlbWV0cywgb25seSBvbmUgbXVzdCBiZSB2aXNpYmxlXG4gKiAobm90IGhhdmUgYSBoaWRkZW4tYXR0cmlidXRlKS5cbiAqXG4gKiBAcGFyYW0ge0hUTUxEb2N1bWVudH0gZG9jdW1lbnRcbiAqIEByZXR1cm5zIHtIVE1MRWxlbWVudHxudWxsfVxuICogQHNlZSBodHRwczovL2h0bWwuc3BlYy53aGF0d2cub3JnL211bHRpcGFnZS9ncm91cGluZy1jb250ZW50Lmh0bWwjdGhlLW1haW4tZWxlbWVudFxuICovXG5mdW5jdGlvbiBnZXRWaXNpYmxlTWFpbkVsZW1lbnQoZG9jdW1lbnQpIHtcbiAgICBjb25zdCBtYWluX2VsZW1lbnRzID0gZG9jdW1lbnQuZ2V0RWxlbWVudHNCeVRhZ05hbWUoXCJtYWluXCIpO1xuICAgIGNvbnN0IHZpc2libGVfbWFpbiA9IG1haW5fZWxlbWVudHMuZmluZCgoZWxlbWVudCkgPT4gIWVsZW1lbnQuaGFzT3duUHJvcGVydHkoJ2hpZGRlbicpKTtcblxuICAgIHJldHVybiAodW5kZWZpbmVkICE9PSB2aXNpYmxlX21haW4pID8gdmlzaWJsZV9tYWluIDogbnVsbDtcbn1cblxuZXhwb3J0IGRlZmF1bHQgVG9vbHRpcDtcbiIsImV4cG9ydCBkZWZhdWx0IGNsYXNzIFVSTEJ1aWxkZXJUb2tlbiB7XG4gIC8qKlxuICAgICAqIEB0eXBlIHtudW1iZXJ9XG4gICAgICovXG4gIHN0YXRpYyBUT0tFTl9MRU5HVEggPSAyNDtcblxuICAvKipcbiAgICogQHR5cGUge3N0cmluZ31cbiAgICovXG4gIHN0YXRpYyBTRVBBUkFUT1IgPSAnXyc7XG5cbiAgLyoqXG4gICAgICogQHR5cGUge3N0cmluZ1tdfVxuICAgICAqL1xuICAjbmFtZXNwYWNlID0gW107XG5cbiAgLyoqXG4gICAgICogQHR5cGUge3N0cmluZ31cbiAgICAgKi9cbiAgI25hbWUgPSAnJztcblxuICAvKipcbiAgICAgKiBAdHlwZSB7c3RyaW5nfG51bGx9XG4gICAgICovXG4gICN0b2tlbiA9IG51bGw7XG5cbiAgLyoqXG4gICAgICogQHBhcmFtIHtzdHJpbmdbXX0gbmFtZXNwYWNlXG4gICAgICogQHBhcmFtIHtzdHJpbmd9IG5hbWVcbiAgICAgKiBAcGFyYW0ge3N0cmluZ3xudWxsfSB0b2tlblxuICAgICAqL1xuICBjb25zdHJ1Y3RvcihuYW1lc3BhY2UsIG5hbWUsIHRva2VuID0gbnVsbCkge1xuICAgIHRoaXMuI25hbWVzcGFjZSA9IG5hbWVzcGFjZTtcbiAgICB0aGlzLiNuYW1lID0gbmFtZTtcbiAgICB0aGlzLiN0b2tlbiA9IHRva2VuO1xuICAgIGlmICh0aGlzLiN0b2tlbiA9PT0gbnVsbCkge1xuICAgICAgdGhpcy4jY3JlYXRlVG9rZW4oKTtcbiAgICB9XG4gIH1cblxuICAvKipcbiAgICAgKiBAcmV0dXJucyB7c3RyaW5nfVxuICAgICAqL1xuICBnZXQgdG9rZW4oKSB7XG4gICAgcmV0dXJuIHRoaXMuI3Rva2VuO1xuICB9XG5cbiAgLyoqXG4gICAgICogQHJldHVybnMge3N0cmluZ31cbiAgICAgKi9cbiAgZ2V0TmFtZSgpIHtcbiAgICByZXR1cm4gdGhpcy4jbmFtZXNwYWNlLmpvaW4oVVJMQnVpbGRlclRva2VuLlNFUEFSQVRPUikgKyBVUkxCdWlsZGVyVG9rZW4uU0VQQVJBVE9SICsgdGhpcy4jbmFtZTtcbiAgfVxuXG4gIC8qKlxuICAgICAqIEByZXR1cm5zIHt2b2lkfVxuICAgICAqL1xuICAjY3JlYXRlVG9rZW4oKSB7XG4gICAgbGV0IHRva2VuID0gJyc7XG4gICAgY29uc3QgY2hhcmFjdGVycyA9ICdBQkNERUZHSElKS0xNTk9QUVJTVFVWV1hZWmFiY2RlZmdoaWprbG1ub3BxcnN0dXZ3eHl6MDEyMzQ1Njc4OSc7XG4gICAgY29uc3QgY2hhcmFjdGVyc0xlbmd0aCA9IGNoYXJhY3RlcnMubGVuZ3RoO1xuICAgIHdoaWxlICh0b2tlbi5sZW5ndGggPCBVUkxCdWlsZGVyVG9rZW4uVE9LRU5fTEVOR1RIKSB7XG4gICAgICB0b2tlbiArPSBjaGFyYWN0ZXJzLmNoYXJBdChNYXRoLmZsb29yKE1hdGgucmFuZG9tKCkgKiBjaGFyYWN0ZXJzTGVuZ3RoKSk7XG4gICAgfVxuICAgIHRoaXMuI3Rva2VuID0gdG9rZW47XG4gIH1cbn1cbiIsImltcG9ydCBVUkxCdWlsZGVyVG9rZW4gZnJvbSAnLi9jb3JlLlVSTEJ1aWxkZXJUb2tlbic7XG5cbmV4cG9ydCBkZWZhdWx0IGNsYXNzIFVSTEJ1aWxkZXIge1xuICAvKipcbiAgICAgKiBAdHlwZSB7bnVtYmVyfVxuICAgICAqL1xuICBzdGF0aWMgVVJMX01BWF9MRU5HVEggPSAyMDQ4O1xuXG4gIC8qKlxuICAgICAqIEB0eXBlIHtzdHJpbmd9XG4gICAgICovXG4gIHN0YXRpYyBTRVBBUkFUT1IgPSAnXyc7XG5cbiAgLyoqXG4gICAgICogQHR5cGUge1VSTH1cbiAgICAgKi9cbiAgI3VybCA9IG51bGw7XG5cbiAgLyoqXG4gICAgICogQHR5cGUge3N0cmluZ31cbiAgICAgKi9cbiAgI2Jhc2VfdXJsID0gJyc7XG5cbiAgLyoqXG4gICAgICogQHR5cGUge3N0cmluZ31cbiAgICAgKi9cbiAgI3F1ZXJ5ID0gJyc7XG5cbiAgLyoqXG4gICAgICogQHR5cGUge3N0cmluZ31cbiAgICAgKi9cbiAgI2ZyYWdtZW50ID0gJyc7XG5cbiAgLyoqXG4gICAgICogQHR5cGUge01hcH1cbiAgICAgKi9cbiAgI3BhcmFtZXRlcnMgPSBuZXcgTWFwKCk7XG5cbiAgLyoqXG4gICAgICogQHR5cGUge01hcH1cbiAgICAgKi9cbiAgI3Rva2VucztcblxuICAvKipcbiAgICAgKiBOZXcgb2JqZWN0cyB3aWxsIHVzdWFsbHkgYmUgY3JlYXRlZCBieSBjb2RlIHJlbmRlcmVkXG4gICAgICogZnJvbSBEYXRhL1VSTEJ1aWxkZXIgb24gdGhlIFBIUCBzaWRlLlxuICAgICAqXG4gICAgICogQHBhcmFtIHtzdHJpbmd9IHVybFxuICAgICAqIEBwYXJhbSB7TWFwPFVSTEJ1aWxkZXJUb2tlbj59IHRva2Vuc1xuICAgICAqL1xuICBjb25zdHJ1Y3Rvcih1cmwsIHRva2VucyA9IG5ldyBNYXAoKSkge1xuICAgIHRoaXMuI3VybCA9IG5ldyBVUkwodXJsKTtcbiAgICB0aGlzLiN0b2tlbnMgPSB0b2tlbnM7XG4gICAgdGhpcy4jYW5hbHl6ZVVSTCgpO1xuICB9XG5cbiAgLyoqXG4gICAgICogQHJldHVybnMge3ZvaWR9XG4gICAgICovXG4gICNhbmFseXplVVJMKCkge1xuICAgIGNvbnN0IHVybCA9IHRoaXMuI3VybDtcbiAgICB0aGlzLiNxdWVyeSA9IHVybC5zZWFyY2guc2xpY2UoMSk7XG4gICAgdGhpcy4jYmFzZV91cmwgPSB1cmwub3JpZ2luICsgdXJsLnBhdGhuYW1lO1xuICAgIGNvbnN0IHNsaWNlcyA9IHRoaXMuI3F1ZXJ5LnNwbGl0KCcmJyk7XG4gICAgc2xpY2VzLmZvckVhY2goKHNsaWNlKSA9PiB7XG4gICAgICBjb25zdCBwYXJhbWV0ZXIgPSBzbGljZS5zcGxpdCgnPScpO1xuICAgICAgdGhpcy4jcGFyYW1ldGVycy5zZXQocGFyYW1ldGVyWzBdLCBwYXJhbWV0ZXJbMV0pO1xuICAgIH0pO1xuICAgIHRoaXMuI2ZyYWdtZW50ID0gdGhpcy4jdXJsLmhhc2guc2xpY2UoMSk7XG4gIH1cblxuICAvKipcbiAgICAgKiBHZXQgdGhlIGZ1bGwgVVJMIGluY2x1ZGluZyBxdWVyeSBzdHJpbmcgYW5kIGZyYWdtZW50L2hhc2hcbiAgICAgKlxuICAgICAqIEByZXR1cm5zIHtzdHJpbmd9XG4gICAgICovXG4gIGdldFVybCgpIHtcbiAgICBsZXQgdXJsID0gdGhpcy4jYmFzZV91cmw7XG4gICAgaWYgKHRoaXMuI3BhcmFtZXRlcnMuc2l6ZSA+IDApIHtcbiAgICAgIHVybCArPSAnPyc7XG4gICAgICB0aGlzLiNwYXJhbWV0ZXJzLmZvckVhY2goXG4gICAgICAgICh2YWx1ZSwga2V5KSA9PiB7XG4gICAgICAgICAgdXJsICs9IGAke2VuY29kZVVSSUNvbXBvbmVudChrZXkpfT0ke2VuY29kZVVSSUNvbXBvbmVudCh2YWx1ZSl9JmA7XG4gICAgICAgIH0sXG4gICAgICApO1xuICAgICAgdXJsID0gdXJsLnNsaWNlKDAsIHVybC5sZW5ndGggLSAxKTtcbiAgICB9XG4gICAgaWYgKHRoaXMuI2ZyYWdtZW50ICE9PSAnJykgeyB1cmwgKz0gYCMke3RoaXMuI2ZyYWdtZW50fWA7IH1cbiAgICByZXR1cm4gdXJsO1xuICB9XG5cbiAgLyoqXG4gICAgICogQ2hhbmdlIHRoZSBmcmFnbWVudC9oYXNoIHBhcnQgb2YgdGhlIFVSTFxuICAgICAqXG4gICAgICogQHBhcmFtIHtzdHJpbmd9IGZyYWdtZW50XG4gICAgICovXG4gIHNldCBmcmFnbWVudChmcmFnbWVudCkge1xuICAgIHRoaXMuI2ZyYWdtZW50ID0gZnJhZ21lbnQ7XG4gIH1cblxuICAvKipcbiAgICAgKiBBZGQgYSBuZXcgcGFyYW1ldGVyIHdpdGggYSBuYW1lc3BhY2VcbiAgICAgKiBhbmQgZ2V0IGl0cyB0b2tlbiBmb3Igc3Vic2VxdWVudCBjaGFuZ2VzLlxuICAgICAqXG4gICAgICogVGhlIG5hbWVzcGFjZSBjYW4gY29uc2lzdHMgb2Ygb25lIG9yIG1vcmUgbGV2ZWxzXG4gICAgICogd2hpY2ggYXJlIG5vdGVkIGFzIGFuIGFycmF5LiBUaGV5IHdpbGwgYmUgam9pbmVkXG4gICAgICogd2l0aCB0aGUgc2VwYXJhdG9yIChzZWUgY29uc3RhbnQpIGFuZCB1c2VkIGFzIGFcbiAgICAgKiBwcmVmaXggZm9yIHRoZSBuYW1lLCBlLmcuXG4gICAgICogTmFtZXNwYWNlOiBbXCJpbE9yZ1VuaXRcIixcImZpbHRlclwiXVxuICAgICAqIE5hbWU6IFwibmFtZVwiXG4gICAgICogUmVzdWx0aW5nIHBhcmFtZXRlcjogXCJpbE9yZ1VuaXRfZmlsdGVyX25hbWVcIlxuICAgICAqXG4gICAgICogQHBhcmFtIHtzdHJpbmdbXX0gbmFtZXNwYWNlXG4gICAgICogQHBhcmFtIHtzdHJpbmd9IG5hbWVcbiAgICAgKiBAcGFyYW0ge3N0cmluZ3xudWxsfSB2YWx1ZVxuICAgICAqIEByZXR1cm5zIHsoVVJMQnVpbGRlcnxVUkxCdWlsZGVyVG9rZW4pW119XG4gICAgICovXG4gIGFjcXVpcmVQYXJhbWV0ZXIobmFtZXNwYWNlLCBuYW1lLCB2YWx1ZSA9IG51bGwpIHtcbiAgICBpZiAobmFtZSA9PT0gJycgfHwgbmFtZXNwYWNlLmxlbmd0aCA9PT0gMCkge1xuICAgICAgdGhyb3cgbmV3IEVycm9yKCdQYXJhbWV0ZXIgbmFtZSBvciBuYW1lc3BhY2Ugbm90IHNldCcpO1xuICAgIH1cblxuICAgIGNvbnN0IHBhcmFtZXRlciA9IG5hbWVzcGFjZS5qb2luKFVSTEJ1aWxkZXIuU0VQQVJBVE9SKSArIFVSTEJ1aWxkZXIuU0VQQVJBVE9SICsgbmFtZTtcbiAgICBpZiAodGhpcy4jcGFyYW1ldGVyRXhpc3RzKHBhcmFtZXRlcikpIHtcbiAgICAgIHRocm93IG5ldyBFcnJvcihgUGFyYW1ldGVyICcke3BhcmFtZXRlcn0nIGFscmVhZHkgZXhpc3RzIGluIFVSTGApO1xuICAgIH1cblxuICAgIGNvbnN0IHRva2VuID0gbmV3IFVSTEJ1aWxkZXJUb2tlbihuYW1lc3BhY2UsIG5hbWUpO1xuICAgIHRoaXMuI3BhcmFtZXRlcnMuc2V0KHBhcmFtZXRlciwgdmFsdWUgPz8gJycpO1xuICAgIHRoaXMuI3Rva2Vucy5zZXQocGFyYW1ldGVyLCB0b2tlbik7XG4gICAgdGhpcy4jY2hlY2tMZW5ndGgoKTtcblxuICAgIHJldHVybiBbdGhpcywgdG9rZW5dO1xuICB9XG5cbiAgLyoqXG4gICAgICogRGVsZXRlIGEgcGFyYW1ldGVyIGlmIHRoZSBzdXBwbGllZCB0b2tlbiBpcyB2YWxpZFxuICAgICAqXG4gICAgICogQHBhcmFtIHtVUkxCdWlsZGVyVG9rZW59IHRva2VuXG4gICAgICogQHJldHVybnMge1VSTEJ1aWxkZXJ9XG4gICAgICovXG4gIGRlbGV0ZVBhcmFtZXRlcih0b2tlbikge1xuICAgIHRoaXMuI2NoZWNrVG9rZW4odG9rZW4pO1xuICAgIHRoaXMuI3BhcmFtZXRlcnMuZGVsZXRlKHRva2VuLmdldE5hbWUoKSk7XG4gICAgdGhpcy4jdG9rZW5zLmRlbGV0ZSh0b2tlbi5nZXROYW1lKCkpO1xuICAgIHJldHVybiB0aGlzO1xuICB9XG5cbiAgLyoqXG4gICAgICogQ2hhbmdlIGEgcGFyYW1ldGVyJ3MgdmFsdWUgaWYgdGhlIHN1cHBsaWVkIHRva2VuIGlzIHZhbGlkXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge1VSTEJ1aWxkZXJUb2tlbn0gdG9rZW5cbiAgICAgKiBAcGFyYW0ge3N0cmluZ30gdmFsdWVcbiAgICAgKiBAcmV0dXJucyB7VVJMQnVpbGRlcn1cbiAgICAgKi9cbiAgd3JpdGVQYXJhbWV0ZXIodG9rZW4sIHZhbHVlKSB7XG4gICAgdGhpcy4jY2hlY2tUb2tlbih0b2tlbik7XG4gICAgdGhpcy4jcGFyYW1ldGVycy5zZXQodG9rZW4uZ2V0TmFtZSgpLCB2YWx1ZSk7XG4gICAgdGhpcy4jY2hlY2tMZW5ndGgoKTtcbiAgICByZXR1cm4gdGhpcztcbiAgfVxuXG4gIC8qKlxuICAgICAqIENoZWNrIGlmIHBhcmFtZXRlciBhbHJlYWR5IGV4aXN0c1xuICAgICAqXG4gICAgICogQHBhcmFtIHtzdHJpbmd9IHBhcmFtZXRlclxuICAgICAqIEByZXR1cm5zIHtib29sZWFufVxuICAgICAqL1xuICAjcGFyYW1ldGVyRXhpc3RzKHBhcmFtZXRlcikge1xuICAgIHJldHVybiB0aGlzLiNwYXJhbWV0ZXJzLmhhcyhwYXJhbWV0ZXIpO1xuICB9XG5cbiAgLyoqXG4gICAgICogQ2hlY2sgaWYgYSB0b2tlbiBpcyB2YWxpZFxuICAgICAqXG4gICAgICogQHBhcmFtIHtVUkxCdWlsZGVyVG9rZW59IHRva2VuXG4gICAgICogQHJldHVybnMge3ZvaWR9XG4gICAgICogQHRocm93cyBFeGNlcHRpb25cbiAgICAgKi9cbiAgI2NoZWNrVG9rZW4odG9rZW4pIHtcbiAgICBpZiAoKHRva2VuIGluc3RhbmNlb2YgVVJMQnVpbGRlclRva2VuKSAhPT0gdHJ1ZSkge1xuICAgICAgdGhyb3cgbmV3IEVycm9yKCdUb2tlbiBpcyBub3QgdmFsaWQnKTtcbiAgICB9XG4gICAgaWYgKCF0aGlzLiN0b2tlbnMuaGFzKHRva2VuLmdldE5hbWUoKSlcbiAgICAgICAgICAgIHx8IHRoaXMuI3Rva2Vucy5nZXQodG9rZW4uZ2V0TmFtZSgpKS50b2tlbiAhPT0gdG9rZW4udG9rZW4pIHtcbiAgICAgIHRocm93IG5ldyBFcnJvcihgVG9rZW4gZm9yICcke3Rva2VuLmdldE5hbWUoKX0nIGlzIG5vdCB2YWxpZGApO1xuICAgIH1cbiAgICBpZiAoIXRoaXMuI3BhcmFtZXRlcnMuaGFzKHRva2VuLmdldE5hbWUoKSkpIHtcbiAgICAgIHRocm93IG5ldyBFcnJvcihgUGFyYW1ldGVyICcke3Rva2VuLmdldE5hbWUoKX0nIGRvZXMgbm90IGV4aXN0IGluIFVSTGApO1xuICAgIH1cbiAgfVxuXG4gIC8qKlxuICAgICAqIENoZWNrIHRoZSBmdWxsIGxlbmd0aCBvZiB0aGUgVVJMIGFnYWluc3QgVVJMX01BWF9MRU5HVEhcbiAgICAgKlxuICAgICAqIEByZXR1cm5zIHt2b2lkfVxuICAgICAqIEB0aHJvd3MgRXhjZXB0aW9uXG4gICAgICovXG4gICNjaGVja0xlbmd0aCgpIHtcbiAgICBpZiAoISh0aGlzLmdldFVybCgpLmxlbmd0aCA8PSBVUkxCdWlsZGVyLlVSTF9NQVhfTEVOR1RIKSkge1xuICAgICAgdGhyb3cgbmV3IEVycm9yKGBUaGUgZmluYWwgVVJMIGlzIGxvbmdlciB0aGFuICR7VVJMQnVpbGRlci5VUkxfTUFYX0xFTkdUSH0gYW5kIHdpbGwgbm90IGJlIHZhbGlkLmApO1xuICAgIH1cbiAgfVxufVxuIiwiaW1wb3J0IGlsIGZyb20gJ2lsJztcbmltcG9ydCByZXBsYWNlQ29udGVudCBmcm9tICcuL2NvcmUucmVwbGFjZUNvbnRlbnQnO1xuaW1wb3J0IFRvb2x0aXAgZnJvbSAnLi9jb3JlLlRvb2x0aXAnO1xuaW1wb3J0IFVSTEJ1aWxkZXIgZnJvbSAnLi9jb3JlLlVSTEJ1aWxkZXInO1xuaW1wb3J0IFVSTEJ1aWxkZXJUb2tlbiBmcm9tICcuL2NvcmUuVVJMQnVpbGRlclRva2VuJztcblxuaWwuVUkgPSBpbC5VSSB8fCB7fTtcbmlsLlVJLmNvcmUgPSBpbC5VSS5jb3JlIHx8IHt9O1xuXG5pbC5VSS5jb3JlLnJlcGxhY2VDb250ZW50ID0gcmVwbGFjZUNvbnRlbnQoJCk7XG5pbC5VSS5jb3JlLlRvb2x0aXAgPSBUb29sdGlwO1xuaWwuVUkuY29yZS5VUkxCdWlsZGVyID0gVVJMQnVpbGRlcjtcbmlsLlVJLmNvcmUuVVJMQnVpbGRlclRva2VuID0gVVJMQnVpbGRlclRva2VuO1xuIl0sIm5hbWVzIjpbImlsIl0sIm1hcHBpbmdzIjoiOzs7Ozs7OztJQUFBO0lBQ0E7SUFDQTtJQUNBO0lBQ0E7SUFDQTtJQUNBO0lBQ0EsSUFBSSxjQUFjLEdBQUcsU0FBUyxDQUFDLEVBQUU7SUFDakMsSUFBSSxPQUFPLFVBQVUsRUFBRSxFQUFFLEdBQUcsRUFBRSxNQUFNLEVBQUU7SUFDdEM7SUFDQSxRQUFRLENBQUMsQ0FBQyxJQUFJLENBQUM7SUFDZixZQUFZLEdBQUcsRUFBRSxHQUFHO0lBQ3BCLFlBQVksUUFBUSxFQUFFLE1BQU07SUFDNUIsU0FBUyxDQUFDLENBQUMsSUFBSSxDQUFDLFNBQVMsSUFBSSxFQUFFO0lBQy9CLFlBQVksSUFBSSxZQUFZLEdBQUcsQ0FBQyxDQUFDLE9BQU8sR0FBRyxJQUFJLEdBQUcsUUFBUSxDQUFDLENBQUM7SUFDNUQsWUFBWSxJQUFJLG1CQUFtQixHQUFHLFlBQVksQ0FBQyxJQUFJLENBQUMsd0JBQXdCLEdBQUcsTUFBTSxHQUFHLElBQUksQ0FBQyxDQUFDLEtBQUssRUFBRSxDQUFDO0FBQzFHO0lBQ0EsWUFBWSxJQUFJLG1CQUFtQixDQUFDLE1BQU0sSUFBSSxDQUFDLEVBQUU7QUFDakQ7SUFDQTtJQUNBO0lBQ0EsZ0JBQWdCLENBQUMsQ0FBQyxHQUFHLEdBQUcsRUFBRSxHQUFHLHlCQUF5QixHQUFHLE1BQU0sR0FBRyxJQUFJLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUM7QUFDbkY7SUFDQSxhQUFhLE1BQU07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
