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
import createRandomString from './createRandomString';

/**
 * Updates all attribute values from an old element id to a new one.
 *
 * @param {HTMLElement} parentElement
 * @param {Map<string, string>} elementIdMapping (oldId => newId)
 * @param {string} attributeName
 * @throws {Error} if an id is not found in elementIdMapping.
 */
function mapAttributeElementIds(parentElement, elementIdMapping, attributeName) {
  parentElement.querySelectorAll(`[${attributeName}]`).forEach((child) => {
    const originalId = child.getAttribute(attributeName);
    if (!elementIdMapping.has(originalId)) {
      throw new Error(`Element references '${originalId}' which does not exist.`);
    }
    child.setAttribute(attributeName, elementIdMapping.get(originalId));
  });
}

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class TemplateRenderer {
  /** @var {Document} */
  #document;

  /**
   * @param {Document} document
   */
  constructor(document) {
    this.#document = document;
  }

  /**
   * Clones HTMLElement's of the given HTMLTemplateElement and returns them as a
   * DocumentFragment. The fragment can be queried or appended directly in the DOM.
   *
   * Usage Example (append):
   *   const fragment = TemplateRenderer.createContent(template);
   *   myElement.append(...fragment.children);
   *
   * Usage example (query):
   *   const fragment = TemplateRenderer.createContent(template);
   *   const newSection = fragment.querySelector('section');
   *   myElement.append(...fragment.children);
   *
   * Note: always work with the entire HTMLCollection, as content may not have a single
   * root element.
   *
   * @param {HTMLTemplateElement} template
   * @returns {DocumentFragment}
   */
  createContent(template) {
    const newElement = template.content.cloneNode(true);
    const elementIdMapping = new Map();

    newElement.querySelectorAll('[id]').forEach((element) => {
      const newId = createRandomString('il_ui_fw_');
      elementIdMapping.set(element.id, newId);
      element.id = newId;
    });

    // for attribute needs special care because we need to use htmlFor.
    newElement.querySelectorAll('[for]').forEach((element) => {
      element.htmlFor = elementIdMapping.get(element.htmlFor);
    });

    mapAttributeElementIds(newElement, elementIdMapping, 'aria-describedby');
    mapAttributeElementIds(newElement, elementIdMapping, 'aria-labelledby');
    mapAttributeElementIds(newElement, elementIdMapping, 'aria-controls');
    mapAttributeElementIds(newElement, elementIdMapping, 'aria-owns');

    return createDocumentFragment(this.#document, newElement.children);
  }
}
