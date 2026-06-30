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

import sprintf from '../../Core/src/sprintf.js';

/**
 * @param {{ txt: function(string): string }}
 * @param {HTMLButtonElement} button
 * @param {HTMLLIElement[]} listItems
 * @param {number} maxItemCount
 */
function toggleListItems(
  language,
  button,
  listItems,
  maxItemCount,
) {
  const isExpanded = button.hasAttribute('aria-expanded')
    && button.getAttribute('aria-expanded') === 'true';

  listItems.forEach((item, index) => {
    if (index > (maxItemCount - 1)) {
      if (isExpanded) {
        item.classList.replace('visible', 'hidden');
      } else {
        item.classList.replace('hidden', 'visible');
      }
    }
  });
  if (isExpanded) {
    button.setAttribute('aria-expanded', 'false');
    button.textContent = sprintf(language.txt('show_x_items'), listItems.length - maxItemCount);
  } else {
    button.setAttribute('aria-expanded', 'true');
    button.textContent = sprintf(language.txt('hide_x_items'), listItems.length - maxItemCount);
  }
}

/**
 * @param {{ txt: function(string): string }}
 * @param {HTMLUListElement|HTMLOListElement} list
 */
export default function createExpandableList(language, list) {
  const button = list.parentElement.querySelector(`[aria-controls="${list.id}"]`);
  if (!button) {
    throw new Error('Could not find button associated with list.');
  }
  if (!list.hasAttribute('data-max-items')) {
    throw new Error('Could not find max items attribute.');
  }
  const maxItemCount = parseInt(list.getAttribute('data-max-items'), 10);
  const listItems = list.querySelectorAll('li');

  button.addEventListener('click', () => {
    toggleListItems(
      language,
      button,
      listItems,
      maxItemCount,
    );
  });
}
