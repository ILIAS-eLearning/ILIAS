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

var il = il || {};
(function (il, $) {
    'use strict';

    function _interopDefaultLegacy (e) { return e && typeof e === 'object' && 'default' in e ? e : { 'default': e }; }

    var il__default = /*#__PURE__*/_interopDefaultLegacy(il);
    var $__default = /*#__PURE__*/_interopDefaultLegacy($);

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

    il__default["default"].UI.core.replaceContent = replaceContent($__default["default"]);
    il__default["default"].UI.core.URLBuilder = URLBuilder;
    il__default["default"].UI.core.URLBuilderToken = URLBuilderToken;

})(il, $);
