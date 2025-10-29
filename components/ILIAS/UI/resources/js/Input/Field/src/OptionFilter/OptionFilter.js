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
 * @author Ferdinand Engländer <ferdinand.englaender@concepts-and-training.de>
 */

import sprintf from '../../../../Core/src/sprintf.js';

/**
 * Screen readers announce result count with delay, so users aren't interrupted during typing.
 * Accessibility advisors recommend 300ms, but in tests Orca kept skipping live announcements.
 * At 500ms, announcements feel quick enough and are not skipped when expected.
 * @type {number}
 */
const A11Y_DEBOUNCE_DELAY = 500;

/**
 * Option Filter Context for inputs like MultiSelect, Radio etc.
 * JS features:
 *    - search bar input filters (hides) list items
 *    - button to clear the filter
 *    - expanding and collapsing the component (hiding and showing elements) with button triggers
 * SCSS features:
 *    - pushing checked items to the top of the list using flex-box order
 *    - component expanding animation
 *    - item switching position animation
 * @author Ferdinand Engländer <ferdinand.englaender@concepts-and-training.de>
 */
export default class OptionFilter {
  /**
   * @type {HTMLFieldSetElement}
   */
  #inputFieldContext;

  /**
   * @type {HTMLInputElement}
   */
  #searchbar;

  /**
   * @type {string}
   */
  #listType;

  /**
   * @type {HTMLElement}
   */
  #itemList;

  /**
   * @type {NodeList}
   */
  #items;

  /**
   * @type {HTMLButtonElement}
   */
  #engageDisengageToggle;

  /**
   * @type {HTMLSpanElement}
   */
  #toggleExpandText;

  /**
   * @type {HTMLSpanElement}
   */
  #toggleCollapseText;

  /**
   * @type {HTMLButtonElement}
   */
  #clearFilterButton;

  /**
   * @type {HTMLDivElement}
   */
  #scrollContainer;

  /**
   * @type {boolean}
   */
  #isFiltered;

  /**
   * @type {boolean}
   */
  #isEngaged;

  /**
   * @type {HTMLDivElement}
   */
  #messageNoMatch;

  /**
   * @type {HTMLDivElement}
   */
  #resultCountDisplay;

  /**
   * @type {string}
   */
  #resultCountTranslationString;

  /**
   * @type {null|number}
   */
  #timeoutId = null;

  /**
   *
   * @param {HTMLElement} inputFieldContext
   * @param {HTMLDivElement} scrollContainer
   * @param {HTMLInputElement} searchbar
   * @param {string} listType
   * @param {HTMLElement} itemList
   * @param {NodeList} items
   * @param {HTMLDivElement} messageNoMatch
   * @param {HTMLButtonElement} clearFilterButton
   * @param {HTMLButtonElement} engageDisengageToggle
   * @param {HTMLSpanElement} toggleExpandText
   * @param {HTMLSpanElement} toggleCollapseText
   * @param {HTMLDivElement} resultCountDisplay
   */
  constructor(
    inputFieldContext,
    scrollContainer,
    searchbar,
    listType,
    itemList,
    items,
    messageNoMatch,
    clearFilterButton,
    engageDisengageToggle,
    toggleExpandText,
    toggleCollapseText,
    resultCountDisplay,
  ) {
    /* DOM Elements */
    this.#inputFieldContext = inputFieldContext;
    this.#scrollContainer = scrollContainer;
    this.#searchbar = searchbar;
    this.#listType = listType;
    this.#itemList = itemList;
    this.#items = items;
    this.#messageNoMatch = messageNoMatch;
    this.#resultCountDisplay = resultCountDisplay;

    /* translation string from php render */
    this.#resultCountTranslationString = this.#resultCountDisplay.innerHTML;

    /* Buttons */
    this.#clearFilterButton = clearFilterButton;
    this.#engageDisengageToggle = engageDisengageToggle;
    this.#toggleExpandText = toggleExpandText;
    this.#toggleCollapseText = toggleCollapseText;

    /* Initialize states */
    this.#isEngaged = false;
    this.#isFiltered = false;

    /* Event Listeners */
    this.#searchbar.addEventListener('input', (event) => {
      this.filterItemsSearch(event);
    });
    this.#clearFilterButton.addEventListener('click', () => {
      this.setFiltered(false);
    });
    this.#engageDisengageToggle.addEventListener('click', () => {
      this.toggleVisibility();
    });
    if (this.#listType === 'radio-field-input') {
      this.#items.forEach((item) => {
        item.addEventListener('change', () => {
          this.scrollListToTop();
        });
      });
    }
  }

  /**
   * Getter for #isEngaged state
   * @returns {boolean}
   */
  isEngaged() {
    return this.#isEngaged;
  }

  /**
   * Getter for #isFiltered state
   * @returns {boolean}
   */
  isFiltered() {
    return this.#isFiltered;
  }

  /**
   * Setter for #isFiltered state
   * @param {boolean} value
   */
  setFiltered(value) {
    if (this.#isFiltered === value) return;
    this.#isFiltered = value;
    if (value) {
      this.#clearFilterButton.style.removeProperty('display');
      this.#resultCountDisplay.style.removeProperty('display');
    } else {
      this.#searchbar.value = '';
      this.#clearFilterButton.style.display = 'none';
      this.#resultCountDisplay.style.display = 'none';
      this.#messageNoMatch.style.display = 'none';
      this.#resetItemsDisplay();
    }
  }

  toggleVisibility() {
    if (this.isEngaged()) {
      this.#isEngaged = false;
      this.#inputFieldContext.classList.remove('engaged');
      this.setFiltered(false);
      this.#engageDisengageToggle.setAttribute('aria-expanded', 'false');
      this.#toggleExpandText.style.removeProperty('display');
      this.#toggleCollapseText.style.display = 'none';
    } else {
      this.#isEngaged = true;
      this.#inputFieldContext.classList.add('engaged');
      this.#engageDisengageToggle.setAttribute('aria-expanded', 'true');
      this.#toggleExpandText.style.display = 'none';
      this.#toggleCollapseText.style.removeProperty('display');
    }
  }

  /**
   * @param {string} text
   */
  #debouncedUpdateA11y(text) {
    this.#inputFieldContext.ownerDocument.defaultView.clearTimeout(this.#timeoutId);
    this.#timeoutId = this.#inputFieldContext.ownerDocument.defaultView.setTimeout(() => {
      this.#resultCountDisplay.textContent = '';
      this.#inputFieldContext.ownerDocument.defaultView.requestAnimationFrame(() => {
        this.#resultCountDisplay.textContent = text;
      });
    }, A11Y_DEBOUNCE_DELAY);
  }

  /**
   * @param {string} count
   */
  #updateA11yResultCount(count) {
    const resultText = sprintf(this.#resultCountTranslationString, count);
    this.#debouncedUpdateA11y(resultText);
  }

  /**
   * Filter items based on search input
   * @param {Event} event
   */
  filterItemsSearch(event) {
    const value = event.target.value.toLowerCase();
    this.setFiltered(!!value); // negates any search term input to false then flips it to true

    let resultCount = 0;
    let foundMatch = false;
    this.#items.forEach((item) => {
      const itemText = item.textContent.toLowerCase();
      const isMatch = itemText.includes(value);
      if (isMatch) {
        resultCount += 1;
        foundMatch = true;
        showItem(item);
      } else {
        hideItem(item);
      }
    });
    this.#updateA11yResultCount(resultCount.toString());
    if (value !== '' && foundMatch === false) {
      this.#messageNoMatch.style.removeProperty('display');
    } else if (value === '' || foundMatch) {
      this.#messageNoMatch.style.display = 'none';
    }
  }

  /**
   * Reset the display of all items
   */
  #resetItemsDisplay() {
    this.#items.forEach((item) => showItem(item));
  }

  scrollListToTop() {
    this.#scrollContainer.scrollTo({
      top: 0,
      behavior: 'smooth',
    });
  }
}

/**
 * Show a specific item
 * @param {HTMLElement} item
 */
function showItem(item) {
  item.style.removeProperty('display');
}

/**
 * Hide a specific item
 * @param {HTMLElement} item
 */
function hideItem(item) {
  item.style.display = 'none';
}
