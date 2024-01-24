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

import hydrateComponents from './Hydration/hydrateComponents';

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class AsyncRenderer {
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
   * HTMLElement with it.
   *
   * @param {URL|string} renderUrl
   * @param {HTMLElement} element
   * @returns {Promise}
   */
  replaceElement(renderUrl, element) {
    return this.#fetchElements(renderUrl).then((elements) => {
      element.replaceWith(...elements);
      hydrateComponents(this.#hydrationRegistry, element);
    });
  }

  /**
   * Renders an HTMLCollection from the given endpoint and replaces all children
   * of the given HTMLElement with it.
   *
   * @param {URL|string} renderUrl
   * @param {HTMLElement} element
   * @return {Promise}
   */
  replaceChildren(renderUrl, element) {
    return this.#fetchElements(renderUrl).then((elements) => {
      element.replaceChildren(...elements);
      hydrateComponents(this.#hydrationRegistry, element);
    });
  }

  /**
   * Renders an HTMLCollection from the given endpoint and appends it to
   * the given HTMLElement (at the bottom).
   *
   * @param {URL|string} renderUrl
   * @param {HTMLElement} element
   * @return {Promise}
   */
  appendElements(renderUrl, element) {
    return this.#fetchElements(renderUrl).then((elements) => {
      element.append(...elements);
      hydrateComponents(this.#hydrationRegistry, element);
    });
  }

  /**
   * Renders an HTMLCollection from the given endpoint and attaches them
   * to the DOM by using the provided function.
   *
   * The function will receive said HTMLCollection and is expected to
   * attach it somewhere in the DOM. It MUST return the parent HTMLElement
   * of the attached HTMLCollection.
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
        throw new Error('attachElements() must return the parent HTMLElement of attached elements.');
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
