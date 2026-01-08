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

import TemplateRenderer from '../../../Core/src/TemplateRenderer.js';

/**
 * Updates all string values of the given attributeName and replaces the last [] or [n] array-
 * postfix with a concrete newIndex (i.e. input_1[input_2][] becomes input_1[input_2][0])
 *
 * @param {HTMLElement} parentElement
 * @param {number} newIndex
 * @param {string} attributeName
 */
function replaceAllAttributeValueIndices(parentElement, newIndex, attributeName) {
  parentElement.querySelectorAll(`[${attributeName}]`).forEach((child) => {
    child.setAttribute(
      attributeName,
      replaceSingleAttributeValueIndex(child.getAttribute(attributeName), newIndex),
    );
  });
}

/**
 * @param {string} name
 * @param {number} index
 */
function replaceSingleAttributeValueIndex(name, index) {
  return name.replace(/\[(\d*)\]$/, `[${index}]`);
}

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class HasDynamicInputsTemplateRenderer extends TemplateRenderer {
  /** @type {number} */
  #currentInputGroupIndex;

  /**
   * @param {Document} document
   * @param {number} currentInputGroupIndex
   */
  constructor(document, currentInputGroupIndex) {
    super(document);
    this.#currentInputGroupIndex = currentInputGroupIndex;
  }

  /**
   * @inheritDoc
   */
  createContent(template) {
    const newInputFragment = super.createContent(template);

    replaceAllAttributeValueIndices(newInputFragment, this.#currentInputGroupIndex, 'data-il-ui-input-name');
    replaceAllAttributeValueIndices(newInputFragment, this.#currentInputGroupIndex, 'name');
    this.#currentInputGroupIndex += 1;

    return newInputFragment;
  }
}
