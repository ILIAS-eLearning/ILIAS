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

const OPTIONS_SOURCE_TRIGGER_TIMEOUT = 200;

const INPUT_TYPE = {
  radio: 'radio-field-input',
  multiSelect: 'multi-select-field-input',
};

/**
 * @typedef {Object} DataSourceResponseData
 * @property {string} value
 * @property {string} display
 * @property {string} searchBy
 */

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
  #messageAsyncStartSearch;

  /**
   * @type {HTMLSpanElement}
   */
  #loaderAnimation;

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
   * @type {undefined|null|string}
   */
  #optionsDataSource;

  /**
   * @type {string}
   */
  #optionsDataSourceToken;

  /**
   * @type {string}
   */
  #optionsDataSourceDisplayValueToken;

  /**
   * @type {number}
   */
  #optionsDataSourceSuggestionStart;

  /**
   * @type {string|Array<string>|null}
   */
  #selectedValue;

  /**
   * @type {null|number}
   */
  #optionsDataSourceTimeoutId = null;

  /**
   * @type {null|AbortController}
   */
  #optionsDataSourceAbortController = null;

  /**
   *
   * @param {HTMLElement} inputFieldContext
   * @param {null|string} optionsDataSource
   * @param {string} optionsDataSourceToken
   * @param {string} optionsDataSourceDisplayValueToken
   * @param {number} optionsDataSourceSuggestionStart
   * @param {string|Array<string>|null} selectedValue
   * @param {HTMLDivElement} scrollContainer
   * @param {HTMLInputElement} searchbar
   * @param {string} listType
   * @param {HTMLElement} itemList
   * @param {NodeList} items
   * @param {HTMLDivElement} messageNoMatch
   * @param {HTMLDivElement} messageAsyncStartSearch
   * @param {HTMLSpanElement} loaderAnimation
   * @param {HTMLButtonElement} clearFilterButton
   * @param {HTMLButtonElement} engageDisengageToggle
   * @param {HTMLSpanElement} toggleExpandText
   * @param {HTMLSpanElement} toggleCollapseText
   * @param {HTMLDivElement} resultCountDisplay
   */
  constructor(
    inputFieldContext,
    optionsDataSource,
    optionsDataSourceToken,
    optionsDataSourceDisplayValueToken,
    optionsDataSourceSuggestionStart,
    selectedValue,
    scrollContainer,
    searchbar,
    listType,
    itemList,
    items,
    messageNoMatch,
    messageAsyncStartSearch,
    loaderAnimation,
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
    this.#messageAsyncStartSearch = messageAsyncStartSearch;
    this.#loaderAnimation = loaderAnimation;
    this.#resultCountDisplay = resultCountDisplay;

    /* Data source related */
    this.#optionsDataSource = optionsDataSource;
    this.#optionsDataSourceToken = optionsDataSourceToken;
    this.#optionsDataSourceDisplayValueToken = optionsDataSourceDisplayValueToken;
    this.#optionsDataSourceSuggestionStart = optionsDataSourceSuggestionStart;
    this.#selectedValue = selectedValue;

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

    if (this.isAsync()) {
      this.clearOptionElements();
      this.loadOptionsDataSourceValues().then(() => {
        this.#loaderAnimation.style.display = 'none';
      });
    }

    /* Event Listeners */
    this.#searchbar.addEventListener('input', (event) => {
      if (this.isAsync()) {
        this.handleOptionsDataSource(event.target).then(() => {
          this.filterItemsSearch(event);
        });
      } else {
        this.filterItemsSearch(event);
      }
    });
    this.#clearFilterButton.addEventListener('click', () => {
      this.setFiltered(false);
    });
    this.#engageDisengageToggle.addEventListener('click', () => {
      if (this.isAsync()) {
        this.#searchbar.value = '';
        this.clearOptionElements();
      }
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

  isAsync() {
    return this.#optionsDataSource !== null && this.#optionsDataSource !== undefined;
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
      this.#messageAsyncStartSearch.classList.add('hidden');
    } else {
      this.#isEngaged = true;
      this.#inputFieldContext.classList.add('engaged');
      this.#engageDisengageToggle.setAttribute('aria-expanded', 'true');
      this.#toggleExpandText.style.display = 'none';
      this.#toggleCollapseText.style.removeProperty('display');
      if (this.isAsync()) {
        this.#messageAsyncStartSearch.classList.remove('hidden');
      }
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
   * @returns {Promise}
   */
  loadOptionsDataSourceValues() {
    return new Promise((resolve, reject) => {
      if (typeof this.#selectedValue === 'string' || this.#selectedValue instanceof String || this.#selectedValue instanceof Array) {
        this.fetchDataSource(
          new Map([[this.#optionsDataSourceDisplayValueToken, this.#selectedValue]]),
        ).then((data) => {
          if (!data || !(data instanceof Array)) {
            reject();
            return;
          }

          data.forEach((responseData) => {
            this.#itemList.append(this.optionsDataSourceDataToElement(responseData, true));
          });

          this.#items = this.#itemList.querySelectorAll('.c-field--has-option-filter__item');
          resolve();
        });
      } else {
        resolve();
      }
    }).catch((error) => {
      if (error instanceof Error) {
        throw error;
      }
    });
  }

  /**
   * @param {HTMLInputElement} input
   * @return {Promise}
   */
  handleOptionsDataSource(input) {
    if (this.#optionsDataSourceAbortController instanceof AbortController) {
      this.#optionsDataSourceAbortController.abort();
    }
    this.#optionsDataSourceAbortController = new AbortController();

    if (this.#optionsDataSourceTimeoutId !== undefined) {
      clearTimeout(this.#optionsDataSourceTimeoutId);
      this.#optionsDataSourceTimeoutId = undefined;
    }

    return new Promise((resolve, reject) => {
      if (input.value.length < this.#optionsDataSourceSuggestionStart) {
        this.#messageAsyncStartSearch.classList.remove('hidden');
        this.clearOptionElements();
        return;
      }

      this.#loaderAnimation.style.removeProperty('display');

      this.#messageAsyncStartSearch.classList.add('hidden');
      this.#optionsDataSourceTimeoutId = setTimeout(
        () => {
          const searchTerm = input.value;

          return this.fetchDataSource(
            new Map([[this.#optionsDataSourceToken, searchTerm]]),
            this.#optionsDataSourceAbortController.signal,
          )
            .then((data) => {
              if (!data || !(data instanceof Array)) {
                reject(new Error('Invalid data received from data source fetch'));
                return;
              }

              this.clearOptionElements();

              data.forEach((responseData) => {
                const element = this.optionsDataSourceDataToElement(responseData);
                const existingElement = this.#itemList.querySelector(
                  `[data-value='${element.dataset.value}'][data-display='${element.dataset.display}']`,
                );

                if (!existingElement) {
                  this.#itemList.append(element);
                }
              });

              this.#items = this.#itemList.querySelectorAll('.c-field--has-option-filter__item');
              resolve();
              this.#loaderAnimation.style.display = 'none';
            });
        },
        OPTIONS_SOURCE_TRIGGER_TIMEOUT,
      );
    }).catch((error) => {
      if (error instanceof Error) {
        throw error;
      }
    });
  }

  /**
   * @param {Map<string, string|Array<string>>} queryParameters
   * @param {AbortSignal} abortController
   * @returns {Promise<Array<DataSourceResponseData>>}
   */
  fetchDataSource(queryParameters, abortController = null) {
    const url = new URL(this.#optionsDataSource, document.location);

    queryParameters.forEach((value, key) => {
      if (value instanceof Array) {
        value.forEach((arrayValue) => {
          url.searchParams.append(`${key}[]`, arrayValue);
        });
      } else {
        url.searchParams.set(key, value);
      }
    });

    const init = {};

    if (abortController instanceof AbortController) {
      init.signal = abortController.signal;
    }

    return fetch(
      url,
      init,
    )
      .then((response) => response.json())
      .catch(() => {
      });
  }

  clearOptionElements(removeSelected = false) {
    Array.from(this.#itemList.children).forEach((child) => {
      if (removeSelected) {
        this.#itemList.removeChild(child);
      } else {
        const input = child.querySelector('input');
        if (!input || !input.checked) {
          this.#itemList.removeChild(child);
        }
      }
    });

    this.#items = this.#itemList.querySelectorAll('.c-field--has-option-filter__item');
  }

  /**
   * @param {DataSourceResponseData} data
   * @param {boolean} selected
   * @return {HTMLDivElement|HTMLLIElement}
   */
  optionsDataSourceDataToElement(data, selected = false) {
    const id = Math.random().toString(16).slice(2);

    const input = document.createElement('input');
    input.id = id;
    input.value = data.value;

    const label = document.createElement('label');
    label.htmlFor = id;

    let element;

    switch (this.#listType) {
      case INPUT_TYPE.radio:
        element = document.createElement('div');
        element.className = 'c-field-radio__item c-field--has-option-filter__item';

        input.type = 'radio';
        input.name = this.#inputFieldContext.dataset.ilUiInputName;

        label.innerText = data.display;

        element.append(input);
        element.append(label);
        break;
      case INPUT_TYPE.multiSelect:
        element = document.createElement('li');
        element.className = 'c-field--has-option-filter__item';

        input.type = 'checkbox';
        input.name = `${this.#inputFieldContext.dataset.ilUiInputName}[]`;

        const labelText = document.createElement('span');
        labelText.className = 'c-field-multiselect__label-text';
        labelText.innerText = data.display;

        label.append(input);
        label.append(labelText);
        element.append(label);
        break;
      default:
        throw new Error(`Unsupported list type '${this.#listType}' received`);
    }

    input.checked = selected;

    element.dataset.value = data.value;
    element.dataset.display = data.display;
    element.dataset.searchBy = data.searchBy;

    return element;
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
