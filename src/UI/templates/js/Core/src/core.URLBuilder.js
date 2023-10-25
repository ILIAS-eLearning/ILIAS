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

import URLBuilderToken from './core.URLBuilderToken';

const URLBuilderUrlMaxLength = 2048;
const URLBuilderSeparator = '_';

export default class URLBuilder {
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
