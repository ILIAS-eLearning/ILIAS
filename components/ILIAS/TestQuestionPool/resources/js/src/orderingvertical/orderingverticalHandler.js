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
 * @type {String}
 */
const positionInputQuery = '[name*="[position]"';

/**
 * @type {String}
 */
const indentationInputQuery = '[name*="[indentation]"';

/**
 * @type {String}
 */
const answerElementClass = 'dd-item';

/**
 * @type {String}
 */
const placeholderClass = 'c-test__dropzone';

/**
 * @type {DOMElement}
 */
let parentElement;

function setup() {
  const answers = parentElement.querySelectorAll(`.${answerElementClass}`);
  let elementHeight = 0;
  answers.forEach(
    (elem) => {
      if (elem.offsetHeight < elementHeight) {
        elementHeight = elem.offsetHeight;
      }
    }
  );
  parentElement.querySelectorAll(`.${placeholderClass}`).forEach(
    (elem) => {
      elem.style.height = `${answers.item(0).offsetHeight}px`;
    }
  );
}

function updatePlaceholders() {
  const placeholderElement = parentElement.querySelector(`.${placeholderClass}`);
  parentElement.querySelectorAll(`.${answerElementClass}`).forEach(
    (elem) => {
      if (!elem.previousElementSibling?.classList.contains(placeholderClass)) {
        elem.parentNode.insertBefore(placeholderElement.cloneNode(), elem);
      }

      if (!elem.nextElementSibling?.classList.contains(placeholderClass)) {
        elem.parentNode.insertBefore(placeholderElement.cloneNode(), elem.nextElementSibling);
      }
    },
  );

  parentElement.querySelectorAll(`.${placeholderClass} + .${placeholderClass}`).forEach(
    (elem) => {
      elem.remove();
    },
  );
}

function updateIndentationInputs(draggedElement, target) {
  let i = 0;
  let root = target.parentElement.parentElement;
  while (root !== parentElement) {
    root = root.parentElement.parentElement;
    i++;
  }
  draggedElement.querySelector(indentationInputQuery).value = i;

  draggedElement.querySelectorAll(`.${answerElementClass}`).forEach(
    (elem) => {
      i++;
      elem.querySelector(indentationInputQuery).value = i;
    }
  );
}

function updatePositionInputs() {
  let p = 0;
  parentElement.querySelectorAll(`.${answerElementClass}`).forEach(
    (elem) => {
      elem.querySelector(positionInputQuery).value = p;
      p++;
    }
  );
}

function changeHandler(draggedElement, target) {
  updatePlaceholders();
  updateIndentationInputs(draggedElement, target);
  updatePositionInputs()
}

export default function orderingVerticalHandler(parentElementParam, makeDraggable) {
  parentElement = parentElementParam;
  setup();
  makeDraggable(parentElement, answerElementClass, placeholderClass, changeHandler);
}