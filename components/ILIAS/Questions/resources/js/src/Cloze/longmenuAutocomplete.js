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
 */

/**
 * @param {number} autocompleteLength
 * @param {array} answerOptions
 * @param {Event} e
 */
function keyHandler(autocompleteLength, answerOptions, e) {
  if (e.key === 'Enter' && e.target.nodeName === 'LI') {
    e.stopImmediatePropagation();
    e.preventDefault();
    onSelectHandler(e);
    return;
  }

  if (e.key === 'ArrowDown') {
    e.stopImmediatePropagation();
    e.preventDefault();
    if (e.target.nextElementSibling?.nodeName === 'UL') {
      e.target.nextElementSibling.firstElementChild.focus();
    }

    if (e.target.nodeName === 'LI' && e.target.nextElementSibling !== null) {
      e.target.nextElementSibling.focus();
    }
    return;
  }

  if (e.key === 'ArrowUp' && e.target.nodeName === 'LI') {
    e.stopImmediatePropagation();
    e.preventDefault();
    if (e.target.previousElementSibling === null) {
      e.target.parentElement.previousElementSibling.focus();
    } else {
      e.target.previousElementSibling.focus();
    }
    return;
  }

  onChangeHandler(autocompleteLength, answerOptions, e);
}

/**
 * @param {number} autocompleteLength
 * @param {array} answerOptions
 * @param {Event} e
 */
function onChangeHandler(autocompleteLength, answerOptions, e) {
  if (e.target.nextElementSibling?.nodeName === 'UL') {
    e.target.nextElementSibling.remove();
  }

  if (e.key === 'Tab' || e.target.value.length < autocompleteLength) {
    return;
  }

  const matchingAnswers = answerOptions.filter(
    (answer) => answer.toLowerCase().includes(e.target.value.toLowerCase()),
  );

  if (matchingAnswers.length === 0) {
    return;
  }

  const list = document.createElement('ul');
  matchingAnswers.forEach((answer) => {
    const listElement = document.createElement('li');
    listElement.tabIndex = 0;
    listElement.textContent = answer;
    list.appendChild(listElement);
  });
  list.addEventListener('click', onSelectHandler);
  list.addEventListener('keyup', onSelectHandler);
  e.target.parentNode.appendChild(list);
}

function onSelectHandler(e) {
  if (e.type === 'keydown' && e.key !== 'Enter') {
    return;
  }
  e.target.parentNode.previousElementSibling.value = e.target.textContent;
  e.target.parentNode.previousElementSibling.focus();
  e.target.parentNode.remove();
}

/**
 * @param {HTMLElement} input
 * @param {number} autocompleteLength
 * @param {array} answerOptions
 */
export default function longmenuAutocomplete(
  input,
  autocompleteLength,
  answerOptions,
) {
  if (input.nodeName === 'INPUT') {
    input.setAttribute(
      'size',
      answerOptions.reduce(
        (a, b) => {
          if (a.length > b.length) {
            return a.length;
          }
          return b.length;
        },
      ),
    );
    input.addEventListener(
      'keyup',
      (e) => keyHandler(autocompleteLength, answerOptions, e),
    );
  }
}
