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
(function (il, document) {
  'use strict';

  function _interopDefaultLegacy(e) {
    return e && typeof e === 'object' && 'default' in e ? e : { 'default': e };
  }

  var il__default = /*#__PURE__*/_interopDefaultLegacy(il);
  var document__default = /*#__PURE__*/_interopDefaultLegacy(document);

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
        throw new Error(
          'Could not find expected attribute aria-describedby for element with tooltip.');
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
      if (this.#fragment !== '') {
        url += `#${this.#fragment}`;
      }

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
   */

  /**
   * @author Thibeau Fuhrer <thibeau@sr.solutions>
   */
  class HydrationRegistry {
    /** @type {Map<string, {function(HTMLElement)}>} */
    #functions = new Map();

    /** @type {Map<string, number>} */
    #order = new Map();

    /**
     * @param {string} id
     * @param {function(HTMLElement)} fn
     * @throws {Error} if the id already exists
     */
    addFunction(id, fn) {
      if (this.#functions.has(id)) {
        throw new Error(`Function with id "${id}" already exists.`);
      }

      // keeps track of the initialisation order, starting from 0.
      this.#order.set(id, this.#functions.size);
      this.#functions.set(id, fn);
    }

    /**
     * @param {string} id
     * @returns {function(HTMLElement)|null}
     */
    getFunction(id) {
      if (this.#functions.has(id)) {
        return this.#functions.get(id);
      }

      return null;
    }

    /**
     * Returns the order in which the hydrators have been provided for initialisation.
     *
     * @returns {Map<string, number>}
     */
    getOrder() {
      return this.#order;
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
   * @author Thibeau Fuhrer <thibeau@sr.solutions>
   */

  /** @type {string} */
  const IS_HYDRATED_ATTRIBUTE = 'data-is-hydrated';

  /** @type {string} */
  const HYDRATED_BY_ATTRIBUTE = 'data-hydrated-by';

  /**
   * Hydrates an element by calling all registered javascript functions mapped
   * to the javascript ids of the element.
   *
   * @param {HydrationRegistry} registry
   * @param {HTMLElement} element
   */
  function hydrateElement(registry, element) {
    // abort if the element is already hydrated or needs no hydration.
    if (!element.hasAttribute(HYDRATED_BY_ATTRIBUTE)
      || !element.hasAttribute(IS_HYDRATED_ATTRIBUTE)
      || element.getAttribute(IS_HYDRATED_ATTRIBUTE) === 'true'
    ) {
      return;
    }

    const hydratorId = element.getAttribute(HYDRATED_BY_ATTRIBUTE);
    const hydrator = registry.getFunction(hydratorId.trim());
    if (hydrator !== null) {
      hydrator(element);
    }

    element.setAttribute(IS_HYDRATED_ATTRIBUTE, 'true');
  }

  /**
   * Returns the list of components ordered by the registration of their hydrator.
   *
   * This is important due to nested components which may depend on their parents
   * business logic to be initialised, or vice-versa. Since our SSR will render
   * nested components inside-out, we need to reorder the collected DOM elements
   * to match the order of rendering.
   *
   * @param {HydrationRegistry} registry
   * @param {HTMLElement[]} components
   * @returns {HTMLElement[]}
   */
  function orderComponentsByRegistration(registry, components) {
    const order = registry.getOrder();
    return components.sort((elementA, elementB) => {
      const positionA = order.get(elementA.getAttribute(HYDRATED_BY_ATTRIBUTE));
      const positionB = order.get(elementB.getAttribute(HYDRATED_BY_ATTRIBUTE));
      return (positionA - positionB);
    });
  }

  /**
   * Returns all components which need to by hydrated in the order of initialisation.
   *
   * @param {HydrationRegistry} registry
   * @param {HTMLElement} element
   * @returns {HTMLElement[]}
   */
  function getHydratableComponentsOfElement(registry, element) {
    const componentNodeList = element.querySelectorAll(`[${IS_HYDRATED_ATTRIBUTE}="false"]`);
    return orderComponentsByRegistration(registry, Array.from(componentNodeList));
  }

  /**
   * Hydrates all children of the given element recursively and the element itself,
   * if the element needs hydration.
   *
   * @param {HydrationRegistry} registry
   * @param {HTMLElement} element
   */
  function hydrateComponents(registry, element) {
    if (element.hasAttribute(HYDRATED_BY_ATTRIBUTE)) {
      hydrateElement(registry, element);
    }

    const components = getHydratableComponentsOfElement(registry, element);
    for (let i = 0; i < components.length; i += 1) {
      hydrateElement(registry, components[i]);
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
   */

  /**
   * @author Thibeau Fuhrer <thibeau@sr.solutions>
   */
  class AsyncRenderer {
    /** @type {HydrationRegistry} */
    #hydrationRegistry;

    /** @type {HTMLDocument} */
    #document;

    /**
     * @param {HydrationRegistry} hydrationRegistry
     * @param {HTMLDocument} document
     */
    constructor(hydrationRegistry, document) {
      this.#hydrationRegistry = hydrationRegistry;
      this.#document = document;
    }

    /**
     * Renders an HTMLCollection from the given endpoint and replaces the given
     * HTMLElement with it. A Promise is returned to act on the results of this
     * process.
     *
     * @param {URL|string} renderUrl
     * @param {HTMLElement} element
     * @returns {Promise}
     */
    replaceElement(renderUrl, element) {
      return this.#fetchElements(renderUrl).then((elements) => {
        element.replaceWith(elements);
        hydrateComponents(this.#hydrationRegistry, element);
      });
    }

    /**
     * Renders an HTMLCollection from the given endpoint and replaces all children
     * of the given HTMLElement with it. A Promise is returned to act on the results
     * of this process.
     *
     * @param {URL|string} renderUrl
     * @param {HTMLElement} element
     * @return {Promise}
     */
    replaceChildren(renderUrl, element) {
      return this.#fetchElements(renderUrl).then((elements) => {
        element.replaceChildren(elements);
        hydrateComponents(this.#hydrationRegistry, element);
      });
    }

    /**
     * Renders an HTMLCollection from the given endpoint and appends it to
     * the given HTMLElement (at the bottom). A Promise is returned to act
     * on the results of this process.
     *
     * @param {URL|string} renderUrl
     * @param {HTMLElement} element
     * @return {Promise}
     */
    appendElements(renderUrl, element) {
      return this.#fetchElements(renderUrl).then((elements) => {
        element.append(elements);
        hydrateComponents(this.#hydrationRegistry, element);
      });
    }

    /**
     * Renders an HTMLCollection from the given endpoint and attaches them
     * to the DOM by using the provided function.
     *
     * The function will receive said HTMLCollection and is expected to
     * attach it somewhere in the DOM. It MUST return the parent HTMLElement
     * of said collection.
     *
     * @deprecated this method has been introduced to cover all legacy usages
     *             of il.UI.core.replaceContent(), which needed to tamper with
     *             the HTMLCollection before attaching it.
     *
     * @param {URL|string} renderUrl
     * @param {function(HTMLCollection): HTMLElement} attachElements
     * @returns {Promise}
     */
    attachElements(renderUrl, attachElements) {
      return this.#fetchElements(renderUrl).then((elements) => {
        const parent = attachElements(elements);
        if (parent === null) {
          throw new Error(
            'attachElements() must return the parent HTMLElement of attached elements.');
        }

        hydrateComponents(this.#hydrationRegistry, parent);
      });
    }

    /**
     * Asynchronously rendered <script> tags must be restored in order to be
     * executed when added to the DOM by e.g. HTMLElement.appendChild().
     *
     * This method only preserves a <script> tags 'src' and 'type' attributes,
     * along with the scripts content. All other attributes are discarded.
     *
     * @param {HTMLScriptElement} script
     * @returns {HTMLScriptElement}
     */
    #restoreScript(script) {
      const newScript = this.#document.createElement('script');

      if (script.hasAttribute('type')) {
        newScript.setAttribute('type', script.getAttribute('type'));
      }
      if (script.hasAttribute('src')) {
        newScript.setAttribute('src', script.getAttribute('src'));
      }
      if (script.textContent.length > 0) {
        newScript.textContent = script.textContent;
      }

      return newScript;
    }

    /**
     * @param {string} html
     * @returns {HTMLCollection}
     */
    #createElements(html) {
      const newElement = this.#document.createElement('div');
      newElement.innerHTML = html.trim();

      // restore possible <script> tags in the new element.
      newElement.querySelectorAll('script').forEach((oldScript) => {
        const newScript = this.#restoreScript(oldScript);
        oldScript.replaceWith(newScript);
      });

      return newElement.children;
    }

    /**
     * @param {URL|string} url
     * @returns {Promise<HTMLCollection>}
     * @throws {Error} if the request with fetch() failed.
     */
    #fetchElements(url) {
      return fetch(url.toString())
        .then((response) => response.text())
        .then((html) => this.#createElements(html))
        .catch((error) => {
          throw new Error(`Could not create element from '${url}': ${error.message}`);
        });
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

  il__default["default"].UI.core.Tooltip = Tooltip;
  il__default["default"].UI.core.URLBuilder = URLBuilder;
  il__default["default"].UI.core.URLBuilderToken = URLBuilderToken;

  il__default["default"].UI.core.HydrationRegistry = new HydrationRegistry();
  il__default["default"].UI.core.AsyncRenderer = new AsyncRenderer(il__default["default"].UI.core.HydrationRegistry,
    document__default["default"]);
  il__default["default"].UI.core.hydrateComponents = (element) => {
    hydrateComponents(il__default["default"].UI.core.HydrationRegistry, element);
  };

})(il, document);
