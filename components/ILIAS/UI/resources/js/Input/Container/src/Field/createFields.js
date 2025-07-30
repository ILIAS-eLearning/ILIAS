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

import Field from './Field.js';

/** @type {string} Default scope used by query selector for searching fields. */
const DEFAULT_SCOPE = ':scope';

/** @type {string} Scope used by query selector for searching nested fields. */
const FIELD_SCOPE = `${DEFAULT_SCOPE} > .c-input__field`;

/** @type {string} Data attribute holding the UI components canonical name. */
const CANONICAL_NAME = 'data-il-ui-component';

/** @type {string} Data attribute holding the Field input-name value. */
const INPUT_NAME = 'data-il-ui-input-name';

/** @type {string} Class name of field elements. */
const FIELD_CLASS = 'c-input';

/** @type {string} Query selector for searching fields. */
const FIELD_SEARCH = `.${FIELD_CLASS}`;

/**
 * @param {HTMLElement} element
 * @returns {string}
 * @throws {Error} if the canonical name cannot be found.
 */
function getCanonicalName(element) {
  if (!element.hasAttribute(CANONICAL_NAME)) {
    throw new Error(`Cannot find '${CANONICAL_NAME}' attribute.`);
  }
  return element.getAttribute(CANONICAL_NAME);
}

/**
 * @param {HTMLElement} element
 * @returns {string}
 * @throws {Error} if the canonical name cannot be found.
 */
function getInputName(element) {
  if (!element.hasAttribute(INPUT_NAME)) {
    throw new Error(`Cannot find '${INPUT_NAME}' attribute.`);
  }
  return element.getAttribute(INPUT_NAME);
}

/**
 * @param {HTMLElement} element
 * @returns {string|null}
 * @throws {Error} if the canonical name cannot be found.
 */
function getLabel(element) {
  const label = element.querySelector(`${DEFAULT_SCOPE} > label`)?.textContent;
  if (!label || label === '') {
    return null;
  }
  return label;
}

/**
 * Returns all elements which are associated to the given name, including the given
 * element itself. Its possible that multiple inputs are associated to the same name,
 * like for radio inputs or checkboxes with a name like 'form/input_1[]'.
 *
 * @param {HTMLElement} element
 * @param {string} name
 * @returns {Array<HTMLInputElement|HTMLSelectElement|HTMLTextAreaElement>}
 */
function getInputsWithNameLike(element, name) {
  return Array
    .from(element.parentElement.querySelectorAll(`[name^="${name}"]`))
    .filter((input) => input.name === name || input.name === `${name}[]`);
}

/**
 * @param {HTMLElement} element
 * @returns {string}
 */
function getScope(element) {
  if (element.classList.contains(FIELD_CLASS)) {
    return FIELD_SCOPE;
  }
  return DEFAULT_SCOPE;
}

/**
 * Recursively creates Field instances for descendants of the given container.
 * This ultimately results in a composite tree, where Field instances contain
 * further Field instances as their children. Each array entry returned here
 * can be thought of as a root-node of such a composite tree.
 *
 * @param {HTMLFormElement|HTMLFieldSetElement} container
 * @returns {Field[]}
 */
export default function createFields(container) {
  return Array
    .from(container.querySelectorAll(`${getScope(container)} > ${FIELD_SEARCH}`))
    .map((element) => {
      const inputName = getInputName(element);
      return new Field(
        getCanonicalName(element),
        inputName,
        getLabel(element),
        getInputsWithNameLike(element, inputName),
        element,
        createFields(element),
      );
    });
}
