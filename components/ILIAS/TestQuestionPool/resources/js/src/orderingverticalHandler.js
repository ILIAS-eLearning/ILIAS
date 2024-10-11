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
const answerSeparator = '{::}';

/**
 * @type {String}
 */
const answerElementClass = 'answers';

/**
 * @type {String}
 */
const placeholderClass = 'c-test__dropzone';

/**
 * @type {DOMElement}
 */
let parentElement;

function changeHandler() {
  const currentAnswer = [];
  const placeholderElement = parentElement.querySelector(`.${placeholderClass}`);

  if (parentElement.firstElementChild.classList.contains('answers')) {
    parentElement.prepend(placeholderElement.cloneNode());
  }

  if (parentElement.lastElementChild.classList.contains('answers')) {
    parentElement.append(placeholderElement.cloneNode());
  }

  parentElement.querySelectorAll(`.${answerElementClass} + .${answerElementClass}`).forEach(
    (elem) => {
      elem.parentNode.insertBefore(placeholderElement.cloneNode(), elem);
    },
  );

  parentElement.querySelectorAll(`.${placeholderClass} + .${placeholderClass}`).forEach(
    (elem) => {
      elem.remove();
    },
  );

  parentElement.querySelectorAll(`.${answerElementClass} > div > span`).forEach(
    (elem) => { currentAnswer.push(elem.textContent); },
  );
  parentElement.nextElementSibling.value = currentAnswer.join(answerSeparator);
}

export default function orderingHorizontalHandler(parentElementParam, makeDraggable) {
  parentElement = parentElementParam;
  makeDraggable(parentElement, answerElementClass, placeholderClass, changeHandler);
}
