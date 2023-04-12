import URLBuilderToken from './core.URLBuilderToken';

export default class URLBuilder {
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
