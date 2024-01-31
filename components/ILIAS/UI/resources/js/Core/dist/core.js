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
 */
(function (il, $) {
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

        const tooltipId = this.#element.getAttribute('aria-describedby');
        if (tooltipId === null) {
          throw new Error('Could not find expected attribute aria-describedby for element with tooltip.');
        }

        this.#tooltip = this.#document.getElementById(tooltipId);
        if (this.#tooltip === null) {
          throw new Error(`Tooltip ${tooltipId} not found.`, { cause: this.#element });
        }

        const main = getVisibleMainElement(this.#document);
        if (main !== null && main.contains(this.#tooltip)) {
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
        this.#container.classList.add('c-tooltip--visible');
        this.bindDocumentEvents();

        this.checkVerticalBounds();
        this.checkHorizontalBounds();
      }

      /**
       * @returns {undefined}
       */
      hideTooltip() {
        this.#container.classList.remove('c-tooltip--visible');
        this.unbindDocumentEvents();

        this.#container.classList.remove('c-tooltip--top');
        this.#tooltip.style.transform = null;
      }

      /**
       * @returns {undefined}
       */
      bindElementEvents() {
        this.#element.addEventListener('focus', this.showTooltip);
        this.#element.addEventListener('blur', this.hideTooltip);
      }

      /**
       * @returns {undefined}
       */
      bindContainerEvents() {
        this.#container.addEventListener('mouseenter', this.showTooltip);
        this.#container.addEventListener('touchstart', this.showTooltip);
        this.#container.addEventListener('mouseleave', this.hideTooltip);
      }

      /**
       * @returns {undefined}
       */
      bindDocumentEvents() {
        this.#document.addEventListener('keydown', this.onKeyDown);
        this.#document.addEventListener('pointerdown', this.onPointerDown);
      }

      /**
       * @returns {undefined}
       */
      unbindDocumentEvents() {
        this.#document.removeEventListener('keydown', this.onKeyDown);
        this.#document.removeEventListener('pointerdown', this.onPointerDown);
      }

      /**
       * @returns {undefined}
       */
      onKeyDown(event) {
        if (event.key === 'Esc' || event.key === 'Escape') {
          this.hideTooltip();
        }
      }

      /**
       * @returns {undefined}
       */
      onPointerDown(event) {
        if (event.target === this.#element || event.target === this.#tooltip) {
          event.preventDefault();
        } else {
          this.hideTooltip();
          this.#element.blur();
        }
      }

      /**
       * @returns {undefined}
       */
      checkVerticalBounds() {
        const ttRect = this.#tooltip.getBoundingClientRect();
        const dpRect = this.getDisplayRect();

        if (ttRect.bottom > (dpRect.top + dpRect.height)) {
          this.#container.classList.add('c-tooltip--top');
        }
      }

      /**
       * @returns {undefined}
       */
      checkHorizontalBounds() {
        const ttRect = this.#tooltip.getBoundingClientRect();
        const dpRect = this.getDisplayRect();

        if ((dpRect.width - dpRect.left) < ttRect.right) {
          this.#tooltip.style.transform = `translateX(${(dpRect.width - dpRect.left) - ttRect.right}px)`;
        }
        if (ttRect.left < dpRect.left) {
          this.#tooltip.style.transform = `translateX(${(dpRect.left - ttRect.left) - ttRect.width / 2}px)`;
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
          height: this.#window.innerHeight,
        };
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
      const mainElements = document.getElementsByTagName('main');
      const visibleMain = Array.from(mainElements).find(
        (element) => Object.prototype.hasOwnProperty.call(element, 'hidden') === false,
      );

      return (undefined !== visibleMain) ? visibleMain : null;
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
              if (Array.isArray(value)) {
                value.forEach(
                  (v) => {
                    url += `${encodeURIComponent(`${key}`)}%5B%5D=${encodeURIComponent(v)}&`;
                  },
                );
              } else {
                url += `${encodeURIComponent(key)}=${encodeURIComponent(value)}&`;
              }
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

        return [this, newToken];
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


    il.UI = il.UI || {};
    il.UI.core = il.UI.core || {};

    il.UI.core.replaceContent = replaceContent($);
    il.UI.core.Tooltip = Tooltip;
    il.UI.core.URLBuilder = URLBuilder;
    il.UI.core.URLBuilderToken = URLBuilderToken;

})(il, $);
