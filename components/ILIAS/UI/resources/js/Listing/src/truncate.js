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

function addEntries(
  listingElement,
  additionalItems,
) {
  const document = listingElement.ownerDocument;
  additionalItems.forEach((element) => {
    const entry = document.createElement('li');
    entry.innerHTML = element;
    listingElement.appendChild(entry);
  });
}

function removeEntries(
  listingElement,
  additionalItems,
) {
  additionalItems.forEach(() => {
    listingElement.removeChild(listingElement.lastChild);
  });
}

function showAllEntries(
  listingElement,
  additionalItems,
  langVarMore,
  langVarLess,
) {
  addEntries(listingElement, additionalItems);
  const showLessButton = document.createElement('button');
  showLessButton.classList.add('btn-link');
  showLessButton.appendChild(document.createTextNode(`[${langVarLess}]`));
  showLessButton.addEventListener('click', (event) => {
    event.target.remove();
    removeEntries(listingElement, additionalItems);
    initTruncation(listingElement, additionalItems,langVarMore, langVarLess);
  });
  listingElement.insertAdjacentElement('afterend', showLessButton);
}

export default function initTruncation(
  listingElement,
  additionalItems,
  langVarMore,
  langVarLess,
) {
  const document = listingElement.ownerDocument;
  const showMoreButton = document.createElement('button');
  showMoreButton.classList.add('btn-link');
  showMoreButton.appendChild(document.createTextNode(`[${langVarMore}]`));
  showMoreButton.addEventListener('click', (event) => {
    event.target.remove();
    showAllEntries(listingElement, additionalItems,langVarMore, langVarLess);
  });
  listingElement.insertAdjacentElement('afterend', showMoreButton);
}
