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

    /**
     * This file is part of ILIAS, a powerful learning management system
     * published by ILIAS open source e-Learning e.V.
     *
     * ILIAS is licensed with the GPL-3.0,
     * see https://www.gnu.org/licenses/gpl-3.0.en.html
     * You should have received a copy of said license along with the
     * source code, too.
     *
     * If this is not the case or you just want to try ILIAS, you'll find
     * us at:
     * https://www.ilias.de
     * https://github.com/ILIAS-eLearning
     *
     ********************************************************************
     */

    const URLBuilderTokenSeparator = '_';
    const URLBuilderTokenLength = 24;

    class URLBuilderToken {
      /**
         * @type {string[]}
         */
      #namespace = [];

      /**
         * @type {string}
         */
      #parameterName = '';

      /**
         * @type {string|null}
         */
      #token = null;

      /**
       * @type {string}
       */
      #name = '';

      /**
         * @param {string[]} namespace
         * @param {string} parameterName
         * @param {string|null} token
         */
      constructor(namespace, parameterName, token = null) {
        this.#namespace = namespace;
        this.#parameterName = parameterName;
        this.#token = token;
        if (this.#token === null) {
          this.#token = URLBuilderToken.createToken();
        }
        this.#name = this.#namespace.join(URLBuilderTokenSeparator) + URLBuilderTokenSeparator;
        this.#name += this.#parameterName;
      }

      /**
         * @returns {string|null}
         */
      getToken() {
        return this.#token;
      }

      /**
         * @returns {string}
         */
      getName() {
        return this.#name;
      }

      /**
         * @returns {string}
         */
      static createToken() {
        let token = '';
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        const charactersLength = characters.length;
        while (token.length < URLBuilderTokenLength) {
          token += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return token;
      }
    }

    /**
     * This file is part of ILIAS, a powerful learning management system
     * published by ILIAS open source e-Learning e.V.
     *
     * ILIAS is licensed with the GPL-3.0,
     * see https://www.gnu.org/licenses/gpl-3.0.en.html
     * You should have received a copy of said license along with the
     * source code, too.
     *
     * If this is not the case or you just want to try ILIAS, you'll find
     * us at:
     * https://www.ilias.de
     * https://github.com/ILIAS-eLearning
     *
     ********************************************************************
     */

    const URLBuilderUrlMaxLength = 2048;
    const URLBuilderSeparator = '_';

    class URLBuilder {
      /**
         * @type {URL}
         */
      #url = null;

      /**
         * @type {string}
         */
      #baseUrl = '';

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
         * @param {URL} url
         * @param {Map<URLBuilderToken>} tokens
         */
      constructor(url, tokens = new Map()) {
        this.#url = url;
        this.#fragment = this.#url.hash.slice(1);
        this.#tokens = tokens;

        const baseParameters = URLBuilder.getQueryParameters(url.search.slice(1));
        tokens.forEach(
          (value, key) => {
            if (baseParameters.has(key)) {
              this.#parameters.set(key, baseParameters.get(key));
            } else {
              this.#parameters.set(key, '');
            }
          },
        );
      }

      /**
       * Extract parameters from the query part of an URL
       *
       * @param {string} query
       * @returns {Map}
       */
      static getQueryParameters(query) {
        const slices = query.split('&');
        const parameters = new Map();
        slices.forEach((slice) => {
          const parameter = slice.split('=');
          parameters.set(parameter[0], parameter[1]);
        });
        return parameters;
      }

      /**
       * Check the full length of an URL against URLBuilderUrlMaxLength
       *
       * @param {string} url
       * @returns {boolean}
       */
      static checkLength(url) {
        return (url.length <= URLBuilderUrlMaxLength);
      }

      /**
         * Get the full URL including query string and fragment/hash
         * Acquired parameters always get precedence over parameters
         * existing in the base URL (via Map merge).
         *
         * @returns {URL}
         * @throws {Error}
         */
      getUrl() {
        let url = this.#url.origin + this.#url.pathname;
        const baseParameters = URLBuilder.getQueryParameters(this.#url.search.slice(1));
        const parameters = new Map([...baseParameters, ...this.#parameters]);

        if (parameters.size > 0) {
          url += '?';
          parameters.forEach(
            (value, key) => {
              url += `${encodeURIComponent(key)}=${encodeURIComponent(value)}&`;
            },
          );
          url = url.slice(0, url.length - 1);
        }
        if (this.#fragment !== '') { url += `#${this.#fragment}`; }

        if (!URLBuilder.checkLength(url)) {
          throw new Error(`The final URL is longer than ${URLBuilderUrlMaxLength} and will not be valid.`);
        }
        return new URL(url);
      }

      /**
         * Change the fragment/hash part of the URL
         *
         * @param {string} fragment
         */
      setFragment(fragment) {
        this.#fragment = fragment;
      }

      /**
       * @typedef {Object} URLBuilderReturn
       * @property {URLBuilder} url
       * @property {URLBuilderToken} token
       */

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
         * The return value is an object containing both the
         * changed URLBuilder as well as the token for any
         * subsequent changes to the acquired parameter.
         *
         * @param {string[]} namespace
         * @param {string} name
         * @param {string|null} value
         * @returns {URLBuilderReturn}
         * @throws {Error}
         */
      acquireParameter(namespace, name, value = null) {
        if (name === '' || namespace.length === 0) {
          throw new Error('Parameter name or namespace not set');
        }

        const parameter = namespace.join(URLBuilderSeparator) + URLBuilderSeparator + name;
        if (this.#parameterExists(parameter)) {
          throw new Error(`Parameter '${parameter}' has already been acquired`);
        }

        const newToken = new URLBuilderToken(namespace, name);
        this.#parameters.set(parameter, value ?? '');
        this.#tokens.set(parameter, newToken);

        return {
          url: this,
          token: newToken,
        };
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
        return this;
      }

      /**
         * Check if parameter was already acquired
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
         * @throws {Error}
         */
      #checkToken(token) {
        if ((token instanceof URLBuilderToken) !== true) {
          throw new Error('Token is not valid');
        }
        if (!this.#tokens.has(token.getName())
                || this.#tokens.get(token.getName()).getToken() !== token.getToken()) {
          throw new Error(`Token for '${token.getName()}' is not valid`);
        }
        if (!this.#parameters.has(token.getName())) {
          throw new Error(`Parameter '${token.getName()}' does not exist in URL`);
        }
      }
    }

    /**
     * This file is part of ILIAS, a powerful learning management system
     * published by ILIAS open source e-Learning e.V.
     *
     * ILIAS is licensed with the GPL-3.0,
     * see https://www.gnu.org/licenses/gpl-3.0.en.html
     * You should have received a copy of said license along with the
     * source code, too.
     *
     * If this is not the case or you just want to try ILIAS, you'll find
     * us at:
     * https://www.ilias.de
     * https://github.com/ILIAS-eLearning
     *
     ********************************************************************
     */

    il__default["default"].UI = il__default["default"].UI || {};
    il__default["default"].UI.core = il__default["default"].UI.core || {};

    il__default["default"].UI.core.replaceContent = replaceContent($);
    il__default["default"].UI.core.Tooltip = Tooltip;
    il__default["default"].UI.core.URLBuilder = URLBuilder;
    il__default["default"].UI.core.URLBuilderToken = URLBuilderToken;

})(il);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidWkuanMiLCJzb3VyY2VzIjpbIi4uL3NyYy9jb3JlLnJlcGxhY2VDb250ZW50LmpzIiwiLi4vc3JjL2NvcmUuVG9vbHRpcC5qcyIsIi4uL3NyYy9jb3JlLlVSTEJ1aWxkZXJUb2tlbi5qcyIsIi4uL3NyYy9jb3JlLlVSTEJ1aWxkZXIuanMiLCIuLi9zcmMvY29yZS5qcyJdLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIFJlcGxhY2UgYSBjb21wb25lbnQgb3IgcGFydHMgb2YgYSBjb21wb25lbnQgdXNpbmcgYWpheCBjYWxsXG4gKlxuICogQHBhcmFtIGlkIGNvbXBvbmVudCBpZFxuICogQHBhcmFtIHVybCByZXBsYWNlbWVudCB1cmxcbiAqIEBwYXJhbSBtYXJrZXIgcmVwbGFjZW1lbnQgbWFya2VyIChcImNvbXBvbmVudFwiLCBcImNvbnRlbnRcIiwgXCJoZWFkZXJcIiwgLi4uKVxuICovXG52YXIgcmVwbGFjZUNvbnRlbnQgPSBmdW5jdGlvbigkKSB7XG4gICAgcmV0dXJuIGZ1bmN0aW9uIChpZCwgdXJsLCBtYXJrZXIpIHtcbiAgICAgICAgLy8gZ2V0IG5ldyBzdHVmZiB2aWEgYWpheFxuICAgICAgICAkLmFqYXgoe1xuICAgICAgICAgICAgdXJsOiB1cmwsXG4gICAgICAgICAgICBkYXRhVHlwZTogJ2h0bWwnXG4gICAgICAgIH0pLmRvbmUoZnVuY3Rpb24oaHRtbCkge1xuICAgICAgICAgICAgdmFyICRuZXdfY29udGVudCA9ICQoXCI8ZGl2PlwiICsgaHRtbCArIFwiPC9kaXY+XCIpO1xuICAgICAgICAgICAgdmFyICRtYXJrZWRfbmV3X2NvbnRlbnQgPSAkbmV3X2NvbnRlbnQuZmluZChcIltkYXRhLXJlcGxhY2UtbWFya2VyPSdcIiArIG1hcmtlciArIFwiJ11cIikuZmlyc3QoKTtcblxuICAgICAgICAgICAgaWYgKCRtYXJrZWRfbmV3X2NvbnRlbnQubGVuZ3RoID09IDApIHtcblxuICAgICAgICAgICAgICAgIC8vIGlmIG1hcmtlciBkb2VzIG5vdCBjb21lIHdpdGggdGhlIG5ldyBjb250ZW50LCB3ZSBwdXQgdGhlIG5ldyBjb250ZW50IGludG8gdGhlIGV4aXN0aW5nIG1hcmtlclxuICAgICAgICAgICAgICAgIC8vICh0aGlzIGluY2x1ZGVzIGFsbCBzY3JpcHQgdGFncyBhbHJlYWR5KVxuICAgICAgICAgICAgICAgICQoXCIjXCIgKyBpZCArIFwiIFtkYXRhLXJlcGxhY2UtbWFya2VyPSdcIiArIG1hcmtlciArIFwiJ11cIikuaHRtbChodG1sKTtcblxuICAgICAgICAgICAgfSBlbHNlIHtcblxuICAgICAgICAgICAgICAgIC8vIGlmIG1hcmtlciBpcyBpbiBuZXcgY29udGVudCwgd2UgcmVwbGFjZSB0aGUgY29tcGxldGUgb2xkIG5vZGUgd2l0aCB0aGUgbWFya2VyXG4gICAgICAgICAgICAgICAgLy8gd2l0aCB0aGUgbmV3IG1hcmtlZCBub2RlXG4gICAgICAgICAgICAgICAgJChcIiNcIiArIGlkICsgXCIgW2RhdGEtcmVwbGFjZS1tYXJrZXI9J1wiICsgbWFya2VyICsgXCInXVwiKS5maXJzdCgpXG4gICAgICAgICAgICAgICAgICAgIC5yZXBsYWNlV2l0aCgkbWFya2VkX25ld19jb250ZW50KTtcblxuICAgICAgICAgICAgICAgIC8vIGFwcGVuZCBpbmNsdWRlZCBzY3JpcHQgKHdoaWNoIHdpbGwgbm90IGJlIHBhcnQgb2YgdGhlIG1hcmtlZCBub2RlXG4gICAgICAgICAgICAgICAgJChcIiNcIiArIGlkICsgXCIgW2RhdGEtcmVwbGFjZS1tYXJrZXI9J1wiICsgbWFya2VyICsgXCInXVwiKS5maXJzdCgpXG4gICAgICAgICAgICAgICAgICAgIC5hZnRlcigkbmV3X2NvbnRlbnQuZmluZChcIltkYXRhLXJlcGxhY2UtbWFya2VyPSdzY3JpcHQnXVwiKSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuICAgIH1cbn07XG5cbmV4cG9ydCBkZWZhdWx0IHJlcGxhY2VDb250ZW50O1xuIiwiLyoqXG4gKiBUaGlzIHJlcHJlc2VudHMgb25lIHRvb2x0aXAgb24gdGhlIHBhZ2UuXG4gKi9cbmNsYXNzIFRvb2x0aXAge1xuICAgIC8qKlxuICAgICAqIEB0eXBlIHtIVE1MRWxlbWVudH1cbiAgICAgKi9cbiAgICAjdG9vbHRpcDtcblxuICAgIC8qKlxuICAgICAqIFRoZSB0b29sdGlwIGVsZW1lbnQgaXRzZWxmLlxuICAgICAqIEB0eXBlIHtFbGVtZW50fVxuICAgICAqL1xuICAgICNlbGVtZW50O1xuXG4gICAgLyoqXG4gICAgICogVGhlIGNvbnRhaW5lciBvZiB0aGUgdG9vbHRpcCBhbmQgdGhlIHRyaWdnZXIgZWxlbWVudC5cbiAgICAgKiBAdHlwZSB7RWxlbWVudH1cbiAgICAgKi9cbiAgICAjY29udGFpbmVyO1xuXG4gICAgLyoqXG4gICAgICogVGhlIEhUTUxEb2N1bWVudCB0aGlzIGFsbCBleGlzdHMgaW5zaWRlLlxuICAgICAqIEB0eXBlIHtIVE1MRG9jdW1lbnR9XG4gICAgICovXG4gICAgI2RvY3VtZW50O1xuXG4gICAgLyoqXG4gICAgICogVGhlIFdpbmRvdyB0aHJvdWdoIHdoaWNoIHdlIHNlZSB0aGF0IHN0dWZmLlxuICAgICAqIEB0eXBlIHtXaW5kb3d9XG4gICAgICovXG4gICAgI3dpbmRvdztcblxuICAgIC8qKlxuICAgICAqIFRoaXMgd2lsbCBiZSB0aGUgXCJtYWluXCItY29udGFpbmVyIGlmIHRoZSB0b29sdGlwIGlzIGluc2lkZSBvbmUuXG4gICAgICogQHR5cGUgez9FbGVtZW50fVxuICAgICAqL1xuICAgICNtYWluID0gbnVsbDtcblxuICAgIGNvbnN0cnVjdG9yKGVsZW1lbnQpIHtcbiAgICAgICAgdGhpcy4jY29udGFpbmVyID0gZWxlbWVudC5wYXJlbnRFbGVtZW50O1xuICAgICAgICB0aGlzLiNlbGVtZW50ID0gZWxlbWVudDtcbiAgICAgICAgdGhpcy4jZG9jdW1lbnQgPSBlbGVtZW50Lm93bmVyRG9jdW1lbnQ7XG4gICAgICAgIHRoaXMuI3dpbmRvdyA9IHRoaXMuI2RvY3VtZW50LmRlZmF1bHRWaWV3IHx8IHRoaXMuI2RvY3VtZW50LnBhcmVudFdpbmRvdztcblxuICAgICAgICB2YXIgdG9vbHRpcF9pZCA9IHRoaXMuI2VsZW1lbnQuZ2V0QXR0cmlidXRlKFwiYXJpYS1kZXNjcmliZWRieVwiKTtcbiAgICAgICAgaWYgKHRvb2x0aXBfaWQgPT09IG51bGwpIHtcbiAgICAgICAgICAgIHRocm93IG5ldyBFcnJvcihcIkNvdWxkIG5vdCBmaW5kIGV4cGVjdGVkIGF0dHJpYnV0ZSBhcmlhLWRlc2NyaWJlZGJ5IGZvciBlbGVtZW50IHdpdGggdG9vbHRpcC5cIik7XG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLiN0b29sdGlwID0gdGhpcy4jZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQodG9vbHRpcF9pZCk7XG4gICAgICAgIGlmICh0aGlzLiN0b29sdGlwID09PSBudWxsKSB7XG4gICAgICAgICAgICB0aHJvdyBuZXcgRXJyb3IoXCJUb29sdGlwIFwiICsgdG9vbHRpcF9pZCArIFwiIG5vdCBmb3VuZC5cIiwge2NhdXNlOiB0aGlzLiNlbGVtZW50fSk7XG4gICAgICAgIH1cblxuICAgICAgICBsZXQgbWFpbiA9IGdldFZpc2libGVNYWluRWxlbWVudCh0aGlzLiNkb2N1bWVudCk7XG4gICAgICAgIGlmIChudWxsICE9PSBtYWluICYmIG1haW4uY29udGFpbnModGhpcy4jdG9vbHRpcCkpIHtcbiAgICAgICAgICAgIHRoaXMuI21haW4gPSBtYWluO1xuICAgICAgICB9XG5cbiAgICAgICAgdGhpcy5zaG93VG9vbHRpcCA9IHRoaXMuc2hvd1Rvb2x0aXAuYmluZCh0aGlzKTtcbiAgICAgICAgdGhpcy5oaWRlVG9vbHRpcCA9IHRoaXMuaGlkZVRvb2x0aXAuYmluZCh0aGlzKTtcbiAgICAgICAgdGhpcy5vbktleURvd24gPSB0aGlzLm9uS2V5RG93bi5iaW5kKHRoaXMpO1xuICAgICAgICB0aGlzLm9uUG9pbnRlckRvd24gPSB0aGlzLm9uUG9pbnRlckRvd24uYmluZCh0aGlzKTtcblxuICAgICAgICB0aGlzLmJpbmRFbGVtZW50RXZlbnRzKCk7XG4gICAgICAgIHRoaXMuYmluZENvbnRhaW5lckV2ZW50cygpO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEByZXR1cm5zIHtIVE1MRWxlbWVudH1cbiAgICAgKi9cbiAgICBnZXQgdG9vbHRpcCgpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuI3Rvb2x0aXA7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQHJldHVybnMge3VuZGVmaW5lZH1cbiAgICAgKi9cbiAgICBzaG93VG9vbHRpcCgpIHtcbiAgICAgICAgdGhpcy4jY29udGFpbmVyLmNsYXNzTGlzdC5hZGQoXCJjLXRvb2x0aXAtLXZpc2libGVcIik7XG4gICAgICAgIHRoaXMuYmluZERvY3VtZW50RXZlbnRzKCk7XG5cbiAgICAgICAgdGhpcy5jaGVja1ZlcnRpY2FsQm91bmRzKCk7XG4gICAgICAgIHRoaXMuY2hlY2tIb3Jpem9udGFsQm91bmRzKCk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQHJldHVybnMge3VuZGVmaW5lZH1cbiAgICAgKi9cbiAgICBoaWRlVG9vbHRpcCgpIHtcbiAgICAgICAgdGhpcy4jY29udGFpbmVyLmNsYXNzTGlzdC5yZW1vdmUoXCJjLXRvb2x0aXAtLXZpc2libGVcIik7XG4gICAgICAgIHRoaXMudW5iaW5kRG9jdW1lbnRFdmVudHMoKTtcblxuICAgICAgICB0aGlzLiNjb250YWluZXIuY2xhc3NMaXN0LnJlbW92ZShcImMtdG9vbHRpcC0tdG9wXCIpO1xuICAgICAgICB0aGlzLiN0b29sdGlwLnN0eWxlLnRyYW5zZm9ybSA9IG51bGw7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQHJldHVybnMge3VuZGVmaW5lZH1cbiAgICAgKi9cbiAgICBiaW5kRWxlbWVudEV2ZW50cygpIHtcbiAgICAgICAgdGhpcy4jZWxlbWVudC5hZGRFdmVudExpc3RlbmVyKFwiZm9jdXNcIiwgdGhpcy5zaG93VG9vbHRpcCk7XG4gICAgICAgIHRoaXMuI2VsZW1lbnQuYWRkRXZlbnRMaXN0ZW5lcihcImJsdXJcIiwgdGhpcy5oaWRlVG9vbHRpcCk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQHJldHVybnMge3VuZGVmaW5lZH1cbiAgICAgKi9cbiAgICBiaW5kQ29udGFpbmVyRXZlbnRzKCkge1xuICAgICAgICB0aGlzLiNjb250YWluZXIuYWRkRXZlbnRMaXN0ZW5lcihcIm1vdXNlZW50ZXJcIiwgdGhpcy5zaG93VG9vbHRpcCk7XG4gICAgICAgIHRoaXMuI2NvbnRhaW5lci5hZGRFdmVudExpc3RlbmVyKFwidG91Y2hzdGFydFwiLCB0aGlzLnNob3dUb29sdGlwKTtcbiAgICAgICAgdGhpcy4jY29udGFpbmVyLmFkZEV2ZW50TGlzdGVuZXIoXCJtb3VzZWxlYXZlXCIsIHRoaXMuaGlkZVRvb2x0aXApO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEByZXR1cm5zIHt1bmRlZmluZWR9XG4gICAgICovXG4gICAgYmluZERvY3VtZW50RXZlbnRzKCkge1xuICAgICAgICB0aGlzLiNkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKFwia2V5ZG93blwiLCB0aGlzLm9uS2V5RG93bilcbiAgICAgICAgdGhpcy4jZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcihcInBvaW50ZXJkb3duXCIsIHRoaXMub25Qb2ludGVyRG93bilcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBAcmV0dXJucyB7dW5kZWZpbmVkfVxuICAgICAqL1xuICAgIHVuYmluZERvY3VtZW50RXZlbnRzKCkge1xuICAgICAgICB0aGlzLiNkb2N1bWVudC5yZW1vdmVFdmVudExpc3RlbmVyKFwia2V5ZG93blwiLCB0aGlzLm9uS2V5RG93bilcbiAgICAgICAgdGhpcy4jZG9jdW1lbnQucmVtb3ZlRXZlbnRMaXN0ZW5lcihcInBvaW50ZXJkb3duXCIsIHRoaXMub25Qb2ludGVyRG93bilcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBAcmV0dXJucyB7dW5kZWZpbmVkfVxuICAgICAqL1xuICAgIG9uS2V5RG93bihldmVudCkge1xuICAgICAgICBpZiAoZXZlbnQua2V5ID09PSBcIkVzY1wiIHx8IGV2ZW50LmtleSA9PT0gXCJFc2NhcGVcIikge1xuICAgICAgICAgICAgdGhpcy5oaWRlVG9vbHRpcCgpO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQHJldHVybnMge3VuZGVmaW5lZH1cbiAgICAgKi9cbiAgICBvblBvaW50ZXJEb3duKGV2ZW50KSB7XG4gICAgICAgIGlmKGV2ZW50LnRhcmdldCA9PT0gdGhpcy4jZWxlbWVudCB8fCBldmVudC50YXJnZXQgPT09IHRoaXMuI3Rvb2x0aXApIHtcbiAgICAgICAgICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgICB0aGlzLmhpZGVUb29sdGlwKCk7XG4gICAgICAgICAgICB0aGlzLiNlbGVtZW50LmJsdXIoKTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEByZXR1cm5zIHt1bmRlZmluZWR9XG4gICAgICovXG4gICAgY2hlY2tWZXJ0aWNhbEJvdW5kcygpIHtcbiAgICAgICAgdmFyIHR0UmVjdCA9IHRoaXMuI3Rvb2x0aXAuZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCk7XG4gICAgICAgIHZhciBkcFJlY3QgPSB0aGlzLmdldERpc3BsYXlSZWN0KCk7XG5cbiAgICAgICAgaWYgKHR0UmVjdC5ib3R0b20gPiAoZHBSZWN0LnRvcCArIGRwUmVjdC5oZWlnaHQpKSB7XG4gICAgICAgICAgICB0aGlzLiNjb250YWluZXIuY2xhc3NMaXN0LmFkZChcImMtdG9vbHRpcC0tdG9wXCIpO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQHJldHVybnMge3VuZGVmaW5lZH1cbiAgICAgKi9cbiAgICBjaGVja0hvcml6b250YWxCb3VuZHMoKSB7XG4gICAgICAgIHZhciB0dFJlY3QgPSB0aGlzLiN0b29sdGlwLmdldEJvdW5kaW5nQ2xpZW50UmVjdCgpO1xuICAgICAgICB2YXIgZHBSZWN0ID0gdGhpcy5nZXREaXNwbGF5UmVjdCgpO1xuXG4gICAgICAgIGlmICgoZHBSZWN0LndpZHRoIC0gZHBSZWN0LmxlZnQpIDwgdHRSZWN0LnJpZ2h0KSB7XG4gICAgICAgICAgICB0aGlzLiN0b29sdGlwLnN0eWxlLnRyYW5zZm9ybSA9IFwidHJhbnNsYXRlWChcIiArICgoZHBSZWN0LndpZHRoIC0gZHBSZWN0LmxlZnQpIC0gdHRSZWN0LnJpZ2h0KSArIFwicHgpXCI7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKHR0UmVjdC5sZWZ0IDwgZHBSZWN0LmxlZnQpIHtcbiAgICAgICAgICAgIHRoaXMuI3Rvb2x0aXAuc3R5bGUudHJhbnNmb3JtID0gXCJ0cmFuc2xhdGVYKFwiICsgKChkcFJlY3QubGVmdCAtIHR0UmVjdC5sZWZ0KSAtIHR0UmVjdC53aWR0aC8yKSArIFwicHgpXCI7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBAcmV0dXJucyB7e2xlZnQ6IG51bWJlciwgdG9wOiBudW1iZXIsIHdpZHRoOiBudW1iZXIsIGhlaWdodDogbnVtYmVyfX1cbiAgICAgKi9cbiAgICBnZXREaXNwbGF5UmVjdCgpIHtcbiAgICAgICAgaWYgKHRoaXMuI21haW4gIT09IG51bGwpIHtcbiAgICAgICAgICAgIHJldHVybiB0aGlzLiNtYWluLmdldEJvdW5kaW5nQ2xpZW50UmVjdCgpO1xuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgIGxlZnQ6IDAsXG4gICAgICAgICAgICB0b3A6IDAsXG4gICAgICAgICAgICB3aWR0aDogdGhpcy4jd2luZG93LmlubmVyV2lkdGgsXG4gICAgICAgICAgICBoZWlnaHQ6IHRoaXMuI3dpbmRvdy5pbm5lckhlaWdodFxuICAgICAgICB9XG4gICAgfVxufVxuXG4vKipcbiAqIFJldHVybnMgdGhlIHZpc2libGUgbWFpbi1lbGVtZW50IG9mIHRoZSBnaXZlbiBkb2N1bWVudC5cbiAqXG4gKiBBIGRvY3VtZW50IG1heSBjb250YWluIG11bHRpcGxlIG1haW4tZWxlbWV0cywgb25seSBvbmUgbXVzdCBiZSB2aXNpYmxlXG4gKiAobm90IGhhdmUgYSBoaWRkZW4tYXR0cmlidXRlKS5cbiAqXG4gKiBAcGFyYW0ge0hUTUxEb2N1bWVudH0gZG9jdW1lbnRcbiAqIEByZXR1cm5zIHtIVE1MRWxlbWVudHxudWxsfVxuICogQHNlZSBodHRwczovL2h0bWwuc3BlYy53aGF0d2cub3JnL211bHRpcGFnZS9ncm91cGluZy1jb250ZW50Lmh0bWwjdGhlLW1haW4tZWxlbWVudFxuICovXG5mdW5jdGlvbiBnZXRWaXNpYmxlTWFpbkVsZW1lbnQoZG9jdW1lbnQpIHtcbiAgICBjb25zdCBtYWluX2VsZW1lbnRzID0gZG9jdW1lbnQuZ2V0RWxlbWVudHNCeVRhZ05hbWUoXCJtYWluXCIpO1xuICAgIGNvbnN0IHZpc2libGVfbWFpbiA9IG1haW5fZWxlbWVudHMuZmluZCgoZWxlbWVudCkgPT4gIWVsZW1lbnQuaGFzT3duUHJvcGVydHkoJ2hpZGRlbicpKTtcblxuICAgIHJldHVybiAodW5kZWZpbmVkICE9PSB2aXNpYmxlX21haW4pID8gdmlzaWJsZV9tYWluIDogbnVsbDtcbn1cblxuZXhwb3J0IGRlZmF1bHQgVG9vbHRpcDtcbiIsIi8qKlxuICogVGhpcyBmaWxlIGlzIHBhcnQgb2YgSUxJQVMsIGEgcG93ZXJmdWwgbGVhcm5pbmcgbWFuYWdlbWVudCBzeXN0ZW1cbiAqIHB1Ymxpc2hlZCBieSBJTElBUyBvcGVuIHNvdXJjZSBlLUxlYXJuaW5nIGUuVi5cbiAqXG4gKiBJTElBUyBpcyBsaWNlbnNlZCB3aXRoIHRoZSBHUEwtMy4wLFxuICogc2VlIGh0dHBzOi8vd3d3LmdudS5vcmcvbGljZW5zZXMvZ3BsLTMuMC5lbi5odG1sXG4gKiBZb3Ugc2hvdWxkIGhhdmUgcmVjZWl2ZWQgYSBjb3B5IG9mIHNhaWQgbGljZW5zZSBhbG9uZyB3aXRoIHRoZVxuICogc291cmNlIGNvZGUsIHRvby5cbiAqXG4gKiBJZiB0aGlzIGlzIG5vdCB0aGUgY2FzZSBvciB5b3UganVzdCB3YW50IHRvIHRyeSBJTElBUywgeW91J2xsIGZpbmRcbiAqIHVzIGF0OlxuICogaHR0cHM6Ly93d3cuaWxpYXMuZGVcbiAqIGh0dHBzOi8vZ2l0aHViLmNvbS9JTElBUy1lTGVhcm5pbmdcbiAqXG4gKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKipcbiAqL1xuXG5jb25zdCBVUkxCdWlsZGVyVG9rZW5TZXBhcmF0b3IgPSAnXyc7XG5jb25zdCBVUkxCdWlsZGVyVG9rZW5MZW5ndGggPSAyNDtcblxuZXhwb3J0IGRlZmF1bHQgY2xhc3MgVVJMQnVpbGRlclRva2VuIHtcbiAgLyoqXG4gICAgICogQHR5cGUge3N0cmluZ1tdfVxuICAgICAqL1xuICAjbmFtZXNwYWNlID0gW107XG5cbiAgLyoqXG4gICAgICogQHR5cGUge3N0cmluZ31cbiAgICAgKi9cbiAgI3BhcmFtZXRlck5hbWUgPSAnJztcblxuICAvKipcbiAgICAgKiBAdHlwZSB7c3RyaW5nfG51bGx9XG4gICAgICovXG4gICN0b2tlbiA9IG51bGw7XG5cbiAgLyoqXG4gICAqIEB0eXBlIHtzdHJpbmd9XG4gICAqL1xuICAjbmFtZSA9ICcnO1xuXG4gIC8qKlxuICAgICAqIEBwYXJhbSB7c3RyaW5nW119IG5hbWVzcGFjZVxuICAgICAqIEBwYXJhbSB7c3RyaW5nfSBwYXJhbWV0ZXJOYW1lXG4gICAgICogQHBhcmFtIHtzdHJpbmd8bnVsbH0gdG9rZW5cbiAgICAgKi9cbiAgY29uc3RydWN0b3IobmFtZXNwYWNlLCBwYXJhbWV0ZXJOYW1lLCB0b2tlbiA9IG51bGwpIHtcbiAgICB0aGlzLiNuYW1lc3BhY2UgPSBuYW1lc3BhY2U7XG4gICAgdGhpcy4jcGFyYW1ldGVyTmFtZSA9IHBhcmFtZXRlck5hbWU7XG4gICAgdGhpcy4jdG9rZW4gPSB0b2tlbjtcbiAgICBpZiAodGhpcy4jdG9rZW4gPT09IG51bGwpIHtcbiAgICAgIHRoaXMuI3Rva2VuID0gVVJMQnVpbGRlclRva2VuLmNyZWF0ZVRva2VuKCk7XG4gICAgfVxuICAgIHRoaXMuI25hbWUgPSB0aGlzLiNuYW1lc3BhY2Uuam9pbihVUkxCdWlsZGVyVG9rZW5TZXBhcmF0b3IpICsgVVJMQnVpbGRlclRva2VuU2VwYXJhdG9yO1xuICAgIHRoaXMuI25hbWUgKz0gdGhpcy4jcGFyYW1ldGVyTmFtZTtcbiAgfVxuXG4gIC8qKlxuICAgICAqIEByZXR1cm5zIHtzdHJpbmd8bnVsbH1cbiAgICAgKi9cbiAgZ2V0VG9rZW4oKSB7XG4gICAgcmV0dXJuIHRoaXMuI3Rva2VuO1xuICB9XG5cbiAgLyoqXG4gICAgICogQHJldHVybnMge3N0cmluZ31cbiAgICAgKi9cbiAgZ2V0TmFtZSgpIHtcbiAgICByZXR1cm4gdGhpcy4jbmFtZTtcbiAgfVxuXG4gIC8qKlxuICAgICAqIEByZXR1cm5zIHtzdHJpbmd9XG4gICAgICovXG4gIHN0YXRpYyBjcmVhdGVUb2tlbigpIHtcbiAgICBsZXQgdG9rZW4gPSAnJztcbiAgICBjb25zdCBjaGFyYWN0ZXJzID0gJ0FCQ0RFRkdISUpLTE1OT1BRUlNUVVZXWFlaYWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXowMTIzNDU2Nzg5JztcbiAgICBjb25zdCBjaGFyYWN0ZXJzTGVuZ3RoID0gY2hhcmFjdGVycy5sZW5ndGg7XG4gICAgd2hpbGUgKHRva2VuLmxlbmd0aCA8IFVSTEJ1aWxkZXJUb2tlbkxlbmd0aCkge1xuICAgICAgdG9rZW4gKz0gY2hhcmFjdGVycy5jaGFyQXQoTWF0aC5mbG9vcihNYXRoLnJhbmRvbSgpICogY2hhcmFjdGVyc0xlbmd0aCkpO1xuICAgIH1cbiAgICByZXR1cm4gdG9rZW47XG4gIH1cbn1cbiIsIi8qKlxuICogVGhpcyBmaWxlIGlzIHBhcnQgb2YgSUxJQVMsIGEgcG93ZXJmdWwgbGVhcm5pbmcgbWFuYWdlbWVudCBzeXN0ZW1cbiAqIHB1Ymxpc2hlZCBieSBJTElBUyBvcGVuIHNvdXJjZSBlLUxlYXJuaW5nIGUuVi5cbiAqXG4gKiBJTElBUyBpcyBsaWNlbnNlZCB3aXRoIHRoZSBHUEwtMy4wLFxuICogc2VlIGh0dHBzOi8vd3d3LmdudS5vcmcvbGljZW5zZXMvZ3BsLTMuMC5lbi5odG1sXG4gKiBZb3Ugc2hvdWxkIGhhdmUgcmVjZWl2ZWQgYSBjb3B5IG9mIHNhaWQgbGljZW5zZSBhbG9uZyB3aXRoIHRoZVxuICogc291cmNlIGNvZGUsIHRvby5cbiAqXG4gKiBJZiB0aGlzIGlzIG5vdCB0aGUgY2FzZSBvciB5b3UganVzdCB3YW50IHRvIHRyeSBJTElBUywgeW91J2xsIGZpbmRcbiAqIHVzIGF0OlxuICogaHR0cHM6Ly93d3cuaWxpYXMuZGVcbiAqIGh0dHBzOi8vZ2l0aHViLmNvbS9JTElBUy1lTGVhcm5pbmdcbiAqXG4gKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKipcbiAqL1xuXG5pbXBvcnQgVVJMQnVpbGRlclRva2VuIGZyb20gJy4vY29yZS5VUkxCdWlsZGVyVG9rZW4nO1xuXG5jb25zdCBVUkxCdWlsZGVyVXJsTWF4TGVuZ3RoID0gMjA0ODtcbmNvbnN0IFVSTEJ1aWxkZXJTZXBhcmF0b3IgPSAnXyc7XG5cbmV4cG9ydCBkZWZhdWx0IGNsYXNzIFVSTEJ1aWxkZXIge1xuICAvKipcbiAgICAgKiBAdHlwZSB7VVJMfVxuICAgICAqL1xuICAjdXJsID0gbnVsbDtcblxuICAvKipcbiAgICAgKiBAdHlwZSB7c3RyaW5nfVxuICAgICAqL1xuICAjYmFzZVVybCA9ICcnO1xuXG4gIC8qKlxuICAgICAqIEB0eXBlIHtzdHJpbmd9XG4gICAgICovXG4gICNxdWVyeSA9ICcnO1xuXG4gIC8qKlxuICAgICAqIEB0eXBlIHtzdHJpbmd9XG4gICAgICovXG4gICNmcmFnbWVudCA9ICcnO1xuXG4gIC8qKlxuICAgICAqIEB0eXBlIHtNYXB9XG4gICAgICovXG4gICNwYXJhbWV0ZXJzID0gbmV3IE1hcCgpO1xuXG4gIC8qKlxuICAgICAqIEB0eXBlIHtNYXB9XG4gICAgICovXG4gICN0b2tlbnM7XG5cbiAgLyoqXG4gICAgICogTmV3IG9iamVjdHMgd2lsbCB1c3VhbGx5IGJlIGNyZWF0ZWQgYnkgY29kZSByZW5kZXJlZFxuICAgICAqIGZyb20gRGF0YS9VUkxCdWlsZGVyIG9uIHRoZSBQSFAgc2lkZS5cbiAgICAgKlxuICAgICAqIEBwYXJhbSB7VVJMfSB1cmxcbiAgICAgKiBAcGFyYW0ge01hcDxVUkxCdWlsZGVyVG9rZW4+fSB0b2tlbnNcbiAgICAgKi9cbiAgY29uc3RydWN0b3IodXJsLCB0b2tlbnMgPSBuZXcgTWFwKCkpIHtcbiAgICB0aGlzLiN1cmwgPSB1cmw7XG4gICAgdGhpcy4jZnJhZ21lbnQgPSB0aGlzLiN1cmwuaGFzaC5zbGljZSgxKTtcbiAgICB0aGlzLiN0b2tlbnMgPSB0b2tlbnM7XG5cbiAgICBjb25zdCBiYXNlUGFyYW1ldGVycyA9IFVSTEJ1aWxkZXIuZ2V0UXVlcnlQYXJhbWV0ZXJzKHVybC5zZWFyY2guc2xpY2UoMSkpO1xuICAgIHRva2Vucy5mb3JFYWNoKFxuICAgICAgKHZhbHVlLCBrZXkpID0+IHtcbiAgICAgICAgaWYgKGJhc2VQYXJhbWV0ZXJzLmhhcyhrZXkpKSB7XG4gICAgICAgICAgdGhpcy4jcGFyYW1ldGVycy5zZXQoa2V5LCBiYXNlUGFyYW1ldGVycy5nZXQoa2V5KSk7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgdGhpcy4jcGFyYW1ldGVycy5zZXQoa2V5LCAnJyk7XG4gICAgICAgIH1cbiAgICAgIH0sXG4gICAgKTtcbiAgfVxuXG4gIC8qKlxuICAgKiBFeHRyYWN0IHBhcmFtZXRlcnMgZnJvbSB0aGUgcXVlcnkgcGFydCBvZiBhbiBVUkxcbiAgICpcbiAgICogQHBhcmFtIHtzdHJpbmd9IHF1ZXJ5XG4gICAqIEByZXR1cm5zIHtNYXB9XG4gICAqL1xuICBzdGF0aWMgZ2V0UXVlcnlQYXJhbWV0ZXJzKHF1ZXJ5KSB7XG4gICAgY29uc3Qgc2xpY2VzID0gcXVlcnkuc3BsaXQoJyYnKTtcbiAgICBjb25zdCBwYXJhbWV0ZXJzID0gbmV3IE1hcCgpO1xuICAgIHNsaWNlcy5mb3JFYWNoKChzbGljZSkgPT4ge1xuICAgICAgY29uc3QgcGFyYW1ldGVyID0gc2xpY2Uuc3BsaXQoJz0nKTtcbiAgICAgIHBhcmFtZXRlcnMuc2V0KHBhcmFtZXRlclswXSwgcGFyYW1ldGVyWzFdKTtcbiAgICB9KTtcbiAgICByZXR1cm4gcGFyYW1ldGVycztcbiAgfVxuXG4gIC8qKlxuICAgKiBDaGVjayB0aGUgZnVsbCBsZW5ndGggb2YgYW4gVVJMIGFnYWluc3QgVVJMQnVpbGRlclVybE1heExlbmd0aFxuICAgKlxuICAgKiBAcGFyYW0ge3N0cmluZ30gdXJsXG4gICAqIEByZXR1cm5zIHtib29sZWFufVxuICAgKi9cbiAgc3RhdGljIGNoZWNrTGVuZ3RoKHVybCkge1xuICAgIHJldHVybiAodXJsLmxlbmd0aCA8PSBVUkxCdWlsZGVyVXJsTWF4TGVuZ3RoKTtcbiAgfVxuXG4gIC8qKlxuICAgICAqIEdldCB0aGUgZnVsbCBVUkwgaW5jbHVkaW5nIHF1ZXJ5IHN0cmluZyBhbmQgZnJhZ21lbnQvaGFzaFxuICAgICAqIEFjcXVpcmVkIHBhcmFtZXRlcnMgYWx3YXlzIGdldCBwcmVjZWRlbmNlIG92ZXIgcGFyYW1ldGVyc1xuICAgICAqIGV4aXN0aW5nIGluIHRoZSBiYXNlIFVSTCAodmlhIE1hcCBtZXJnZSkuXG4gICAgICpcbiAgICAgKiBAcmV0dXJucyB7VVJMfVxuICAgICAqIEB0aHJvd3Mge0Vycm9yfVxuICAgICAqL1xuICBnZXRVcmwoKSB7XG4gICAgbGV0IHVybCA9IHRoaXMuI3VybC5vcmlnaW4gKyB0aGlzLiN1cmwucGF0aG5hbWU7XG4gICAgY29uc3QgYmFzZVBhcmFtZXRlcnMgPSBVUkxCdWlsZGVyLmdldFF1ZXJ5UGFyYW1ldGVycyh0aGlzLiN1cmwuc2VhcmNoLnNsaWNlKDEpKTtcbiAgICBjb25zdCBwYXJhbWV0ZXJzID0gbmV3IE1hcChbLi4uYmFzZVBhcmFtZXRlcnMsIC4uLnRoaXMuI3BhcmFtZXRlcnNdKTtcblxuICAgIGlmIChwYXJhbWV0ZXJzLnNpemUgPiAwKSB7XG4gICAgICB1cmwgKz0gJz8nO1xuICAgICAgcGFyYW1ldGVycy5mb3JFYWNoKFxuICAgICAgICAodmFsdWUsIGtleSkgPT4ge1xuICAgICAgICAgIHVybCArPSBgJHtlbmNvZGVVUklDb21wb25lbnQoa2V5KX09JHtlbmNvZGVVUklDb21wb25lbnQodmFsdWUpfSZgO1xuICAgICAgICB9LFxuICAgICAgKTtcbiAgICAgIHVybCA9IHVybC5zbGljZSgwLCB1cmwubGVuZ3RoIC0gMSk7XG4gICAgfVxuICAgIGlmICh0aGlzLiNmcmFnbWVudCAhPT0gJycpIHsgdXJsICs9IGAjJHt0aGlzLiNmcmFnbWVudH1gOyB9XG5cbiAgICBpZiAoIVVSTEJ1aWxkZXIuY2hlY2tMZW5ndGgodXJsKSkge1xuICAgICAgdGhyb3cgbmV3IEVycm9yKGBUaGUgZmluYWwgVVJMIGlzIGxvbmdlciB0aGFuICR7VVJMQnVpbGRlclVybE1heExlbmd0aH0gYW5kIHdpbGwgbm90IGJlIHZhbGlkLmApO1xuICAgIH1cbiAgICByZXR1cm4gbmV3IFVSTCh1cmwpO1xuICB9XG5cbiAgLyoqXG4gICAgICogQ2hhbmdlIHRoZSBmcmFnbWVudC9oYXNoIHBhcnQgb2YgdGhlIFVSTFxuICAgICAqXG4gICAgICogQHBhcmFtIHtzdHJpbmd9IGZyYWdtZW50XG4gICAgICovXG4gIHNldEZyYWdtZW50KGZyYWdtZW50KSB7XG4gICAgdGhpcy4jZnJhZ21lbnQgPSBmcmFnbWVudDtcbiAgfVxuXG4gIC8qKlxuICAgKiBAdHlwZWRlZiB7T2JqZWN0fSBVUkxCdWlsZGVyUmV0dXJuXG4gICAqIEBwcm9wZXJ0eSB7VVJMQnVpbGRlcn0gdXJsXG4gICAqIEBwcm9wZXJ0eSB7VVJMQnVpbGRlclRva2VufSB0b2tlblxuICAgKi9cblxuICAvKipcbiAgICAgKiBBZGQgYSBuZXcgcGFyYW1ldGVyIHdpdGggYSBuYW1lc3BhY2VcbiAgICAgKiBhbmQgZ2V0IGl0cyB0b2tlbiBmb3Igc3Vic2VxdWVudCBjaGFuZ2VzLlxuICAgICAqXG4gICAgICogVGhlIG5hbWVzcGFjZSBjYW4gY29uc2lzdHMgb2Ygb25lIG9yIG1vcmUgbGV2ZWxzXG4gICAgICogd2hpY2ggYXJlIG5vdGVkIGFzIGFuIGFycmF5LiBUaGV5IHdpbGwgYmUgam9pbmVkXG4gICAgICogd2l0aCB0aGUgc2VwYXJhdG9yIChzZWUgY29uc3RhbnQpIGFuZCB1c2VkIGFzIGFcbiAgICAgKiBwcmVmaXggZm9yIHRoZSBuYW1lLCBlLmcuXG4gICAgICogTmFtZXNwYWNlOiBbXCJpbE9yZ1VuaXRcIixcImZpbHRlclwiXVxuICAgICAqIE5hbWU6IFwibmFtZVwiXG4gICAgICogUmVzdWx0aW5nIHBhcmFtZXRlcjogXCJpbE9yZ1VuaXRfZmlsdGVyX25hbWVcIlxuICAgICAqXG4gICAgICogVGhlIHJldHVybiB2YWx1ZSBpcyBhbiBvYmplY3QgY29udGFpbmluZyBib3RoIHRoZVxuICAgICAqIGNoYW5nZWQgVVJMQnVpbGRlciBhcyB3ZWxsIGFzIHRoZSB0b2tlbiBmb3IgYW55XG4gICAgICogc3Vic2VxdWVudCBjaGFuZ2VzIHRvIHRoZSBhY3F1aXJlZCBwYXJhbWV0ZXIuXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge3N0cmluZ1tdfSBuYW1lc3BhY2VcbiAgICAgKiBAcGFyYW0ge3N0cmluZ30gbmFtZVxuICAgICAqIEBwYXJhbSB7c3RyaW5nfG51bGx9IHZhbHVlXG4gICAgICogQHJldHVybnMge1VSTEJ1aWxkZXJSZXR1cm59XG4gICAgICogQHRocm93cyB7RXJyb3J9XG4gICAgICovXG4gIGFjcXVpcmVQYXJhbWV0ZXIobmFtZXNwYWNlLCBuYW1lLCB2YWx1ZSA9IG51bGwpIHtcbiAgICBpZiAobmFtZSA9PT0gJycgfHwgbmFtZXNwYWNlLmxlbmd0aCA9PT0gMCkge1xuICAgICAgdGhyb3cgbmV3IEVycm9yKCdQYXJhbWV0ZXIgbmFtZSBvciBuYW1lc3BhY2Ugbm90IHNldCcpO1xuICAgIH1cblxuICAgIGNvbnN0IHBhcmFtZXRlciA9IG5hbWVzcGFjZS5qb2luKFVSTEJ1aWxkZXJTZXBhcmF0b3IpICsgVVJMQnVpbGRlclNlcGFyYXRvciArIG5hbWU7XG4gICAgaWYgKHRoaXMuI3BhcmFtZXRlckV4aXN0cyhwYXJhbWV0ZXIpKSB7XG4gICAgICB0aHJvdyBuZXcgRXJyb3IoYFBhcmFtZXRlciAnJHtwYXJhbWV0ZXJ9JyBoYXMgYWxyZWFkeSBiZWVuIGFjcXVpcmVkYCk7XG4gICAgfVxuXG4gICAgY29uc3QgbmV3VG9rZW4gPSBuZXcgVVJMQnVpbGRlclRva2VuKG5hbWVzcGFjZSwgbmFtZSk7XG4gICAgdGhpcy4jcGFyYW1ldGVycy5zZXQocGFyYW1ldGVyLCB2YWx1ZSA/PyAnJyk7XG4gICAgdGhpcy4jdG9rZW5zLnNldChwYXJhbWV0ZXIsIG5ld1Rva2VuKTtcblxuICAgIHJldHVybiB7XG4gICAgICB1cmw6IHRoaXMsXG4gICAgICB0b2tlbjogbmV3VG9rZW4sXG4gICAgfTtcbiAgfVxuXG4gIC8qKlxuICAgICAqIERlbGV0ZSBhIHBhcmFtZXRlciBpZiB0aGUgc3VwcGxpZWQgdG9rZW4gaXMgdmFsaWRcbiAgICAgKlxuICAgICAqIEBwYXJhbSB7VVJMQnVpbGRlclRva2VufSB0b2tlblxuICAgICAqIEByZXR1cm5zIHtVUkxCdWlsZGVyfVxuICAgICAqL1xuICBkZWxldGVQYXJhbWV0ZXIodG9rZW4pIHtcbiAgICB0aGlzLiNjaGVja1Rva2VuKHRva2VuKTtcbiAgICB0aGlzLiNwYXJhbWV0ZXJzLmRlbGV0ZSh0b2tlbi5nZXROYW1lKCkpO1xuICAgIHRoaXMuI3Rva2Vucy5kZWxldGUodG9rZW4uZ2V0TmFtZSgpKTtcbiAgICByZXR1cm4gdGhpcztcbiAgfVxuXG4gIC8qKlxuICAgICAqIENoYW5nZSBhIHBhcmFtZXRlcidzIHZhbHVlIGlmIHRoZSBzdXBwbGllZCB0b2tlbiBpcyB2YWxpZFxuICAgICAqXG4gICAgICogQHBhcmFtIHtVUkxCdWlsZGVyVG9rZW59IHRva2VuXG4gICAgICogQHBhcmFtIHtzdHJpbmd9IHZhbHVlXG4gICAgICogQHJldHVybnMge1VSTEJ1aWxkZXJ9XG4gICAgICovXG4gIHdyaXRlUGFyYW1ldGVyKHRva2VuLCB2YWx1ZSkge1xuICAgIHRoaXMuI2NoZWNrVG9rZW4odG9rZW4pO1xuICAgIHRoaXMuI3BhcmFtZXRlcnMuc2V0KHRva2VuLmdldE5hbWUoKSwgdmFsdWUpO1xuICAgIHJldHVybiB0aGlzO1xuICB9XG5cbiAgLyoqXG4gICAgICogQ2hlY2sgaWYgcGFyYW1ldGVyIHdhcyBhbHJlYWR5IGFjcXVpcmVkXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge3N0cmluZ30gcGFyYW1ldGVyXG4gICAgICogQHJldHVybnMge2Jvb2xlYW59XG4gICAgICovXG4gICNwYXJhbWV0ZXJFeGlzdHMocGFyYW1ldGVyKSB7XG4gICAgcmV0dXJuIHRoaXMuI3BhcmFtZXRlcnMuaGFzKHBhcmFtZXRlcik7XG4gIH1cblxuICAvKipcbiAgICAgKiBDaGVjayBpZiBhIHRva2VuIGlzIHZhbGlkXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge1VSTEJ1aWxkZXJUb2tlbn0gdG9rZW5cbiAgICAgKiBAcmV0dXJucyB7dm9pZH1cbiAgICAgKiBAdGhyb3dzIHtFcnJvcn1cbiAgICAgKi9cbiAgI2NoZWNrVG9rZW4odG9rZW4pIHtcbiAgICBpZiAoKHRva2VuIGluc3RhbmNlb2YgVVJMQnVpbGRlclRva2VuKSAhPT0gdHJ1ZSkge1xuICAgICAgdGhyb3cgbmV3IEVycm9yKCdUb2tlbiBpcyBub3QgdmFsaWQnKTtcbiAgICB9XG4gICAgaWYgKCF0aGlzLiN0b2tlbnMuaGFzKHRva2VuLmdldE5hbWUoKSlcbiAgICAgICAgICAgIHx8IHRoaXMuI3Rva2Vucy5nZXQodG9rZW4uZ2V0TmFtZSgpKS5nZXRUb2tlbigpICE9PSB0b2tlbi5nZXRUb2tlbigpKSB7XG4gICAgICB0aHJvdyBuZXcgRXJyb3IoYFRva2VuIGZvciAnJHt0b2tlbi5nZXROYW1lKCl9JyBpcyBub3QgdmFsaWRgKTtcbiAgICB9XG4gICAgaWYgKCF0aGlzLiNwYXJhbWV0ZXJzLmhhcyh0b2tlbi5nZXROYW1lKCkpKSB7XG4gICAgICB0aHJvdyBuZXcgRXJyb3IoYFBhcmFtZXRlciAnJHt0b2tlbi5nZXROYW1lKCl9JyBkb2VzIG5vdCBleGlzdCBpbiBVUkxgKTtcbiAgICB9XG4gIH1cbn1cbiIsIi8qKlxuICogVGhpcyBmaWxlIGlzIHBhcnQgb2YgSUxJQVMsIGEgcG93ZXJmdWwgbGVhcm5pbmcgbWFuYWdlbWVudCBzeXN0ZW1cbiAqIHB1Ymxpc2hlZCBieSBJTElBUyBvcGVuIHNvdXJjZSBlLUxlYXJuaW5nIGUuVi5cbiAqXG4gKiBJTElBUyBpcyBsaWNlbnNlZCB3aXRoIHRoZSBHUEwtMy4wLFxuICogc2VlIGh0dHBzOi8vd3d3LmdudS5vcmcvbGljZW5zZXMvZ3BsLTMuMC5lbi5odG1sXG4gKiBZb3Ugc2hvdWxkIGhhdmUgcmVjZWl2ZWQgYSBjb3B5IG9mIHNhaWQgbGljZW5zZSBhbG9uZyB3aXRoIHRoZVxuICogc291cmNlIGNvZGUsIHRvby5cbiAqXG4gKiBJZiB0aGlzIGlzIG5vdCB0aGUgY2FzZSBvciB5b3UganVzdCB3YW50IHRvIHRyeSBJTElBUywgeW91J2xsIGZpbmRcbiAqIHVzIGF0OlxuICogaHR0cHM6Ly93d3cuaWxpYXMuZGVcbiAqIGh0dHBzOi8vZ2l0aHViLmNvbS9JTElBUy1lTGVhcm5pbmdcbiAqXG4gKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKipcbiAqL1xuXG5pbXBvcnQgaWwgZnJvbSAnaWwnO1xuaW1wb3J0IHJlcGxhY2VDb250ZW50IGZyb20gJy4vY29yZS5yZXBsYWNlQ29udGVudCc7XG5pbXBvcnQgVG9vbHRpcCBmcm9tICcuL2NvcmUuVG9vbHRpcCc7XG5pbXBvcnQgVVJMQnVpbGRlciBmcm9tICcuL2NvcmUuVVJMQnVpbGRlcic7XG5pbXBvcnQgVVJMQnVpbGRlclRva2VuIGZyb20gJy4vY29yZS5VUkxCdWlsZGVyVG9rZW4nO1xuXG5pbC5VSSA9IGlsLlVJIHx8IHt9O1xuaWwuVUkuY29yZSA9IGlsLlVJLmNvcmUgfHwge307XG5cbmlsLlVJLmNvcmUucmVwbGFjZUNvbnRlbnQgPSByZXBsYWNlQ29udGVudCgkKTtcbmlsLlVJLmNvcmUuVG9vbHRpcCA9IFRvb2x0aXA7XG5pbC5VSS5jb3JlLlVSTEJ1aWxkZXIgPSBVUkxCdWlsZGVyO1xuaWwuVUkuY29yZS5VUkxCdWlsZGVyVG9rZW4gPSBVUkxCdWlsZGVyVG9rZW47XG4iXSwibmFtZXMiOlsiaWwiXSwibWFwcGluZ3MiOiI7Ozs7Ozs7O0lBQUE7SUFDQTtJQUNBO0lBQ0E7SUFDQTtJQUNBO0lBQ0E7SUFDQSxJQUFJLGNBQWMsR0FBRyxTQUFTLENBQUMsRUFBRTtJQUNqQyxJQUFJLE9BQU8sVUFBVSxFQUFFLEVBQUUsR0FBRyxFQUFFLE1BQU0sRUFBRTtJQUN0QztJQUNBLFFBQVEsQ0FBQyxDQUFDLElBQUksQ0FBQztJQUNmLFlBQVksR0FBRyxFQUFFLEdBQUc7SUFDcEIsWUFBWSxRQUFRLEVBQUUsTUFBTTtJQUM1QixTQUFTLENBQUMsQ0FBQyxJQUFJLENBQUMsU0FBUyxJQUFJLEVBQUU7SUFDL0IsWUFBWSxJQUFJLFlBQVksR0FBRyxDQUFDLENBQUMsT0FBTyxHQUFHLElBQUksR0FBRyxRQUFRLENBQUMsQ0FBQztJQUM1RCxZQUFZLElBQUksbUJBQW1CLEdBQUcsWUFBWSxDQUFDLElBQUksQ0FBQyx3QkFBd0IsR0FBRyxNQUFNLEdBQUcsSUFBSSxDQUFDLENBQUMsS0FBSyxFQUFFLENBQUM7QUFDMUc7SUFDQSxZQUFZLElBQUksbUJBQW1CLENBQUMsTUFBTSxJQUFJLENBQUMsRUFBRTtBQUNqRDtJQUNBO0lBQ0E7SUFDQSxnQkFBZ0IsQ0FBQyxDQUFDLEdBQUcsR0FBRyxFQUFFLEdBQUcseUJBQXlCLEdBQUcsTUFBTSxHQUFHLElBQUksQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQztBQUNuRjtJQUNBLGFBQWEsTUFBTTtBQUNuQjtJQUNBO0lBQ0E7SUFDQSxnQkFB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
