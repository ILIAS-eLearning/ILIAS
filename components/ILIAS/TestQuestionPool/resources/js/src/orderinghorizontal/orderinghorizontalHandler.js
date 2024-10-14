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

function setup() {
  const answers = parentElement.querySelectorAll(`.${answerElementClass}`);
  let elementWidth = 0;
  answers.forEach((elem) => { elementWidth += elem.offsetWidth; });
  parentElement.querySelectorAll(`.${placeholderClass}`).forEach(
    (elem) => {
      elem.style.width = `${elementWidth / answers.length}px`;
      elem.style.height = `${answers.item(0).offsetHeight}px`;
    }
  );
}

function changeHandler() {
  const currentAnswer = [];
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

  parentElement.querySelectorAll(`.${answerElementClass} > div > span`).forEach(
    (elem) => { currentAnswer.push(elem.textContent); },
  );
  parentElement.nextElementSibling.value = currentAnswer.join(answerSeparator);
}

export default function orderingHorizontalHandler(parentElementParam, makeDraggable) {
  parentElement = parentElementParam;
  setup();
  makeDraggable(parentElement, answerElementClass, placeholderClass, changeHandler);
}
