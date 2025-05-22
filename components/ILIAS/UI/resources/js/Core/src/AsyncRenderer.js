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

import createDocumentFragment from './createDocumentFragment';

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class AsyncRenderer {
  /** @type {Document} */
  #document;

  /**
   * @param {Document} document
   */
  constructor(document) {
    this.#document = document;
  }

  /**
   * Fetches HTML content from the given URL and returns it as a DocumentFragment.
   * The fragment can be queried or appended directly in the DOM.
   *
   * Usage Example (append):
   *   AsyncRenderer.loadContent('https://example.com').then((fragment) => {
   *     myElement.append(...fragment.children);
   *   });
   *
   * Usage example (query):
   *   const newElement = await AsyncRenderer.loadContent('https://example.com').then((fragment) => {
   *     return fragment.querySelector('section');
   *   });
   *
   * Note: always work with the entire HTMLCollection, as content may not have a single
   * root element.
   *
   * @param {URL|string} url
   * @returns {Promise<DocumentFragment>}
   * @throws {Error} if the request with fetch() failed.
   */
  loadContent(url) {
    return fetch(url.toString())
      .then((response) => response.text())
      .then((html) => this.#createElements(html))
      .then((elements) => createDocumentFragment(this.#document, elements))
      .catch((error) => {
        throw new Error(`Could not render element(s) from '${url}': ${error.message}`);
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
}
