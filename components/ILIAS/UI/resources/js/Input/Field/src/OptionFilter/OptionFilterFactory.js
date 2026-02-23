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

import OptionFilter from './OptionFilter.js';

export default class OptionFilterFactory {
  /** @type {Map<string, OptionFIlter>} */
  #instances = new Map();

  /**
     * @param {HTMLElement} inputFieldWithOptionFilter
     * @return {OptionFilter}
     * @throws {Error} if the input was already initialized.
     */
  init(inputFieldWithOptionFilter) {
    if (inputFieldWithOptionFilter === undefined) {
      throw new TypeError('During init of an InputHasOptionFilter an undefined element was passed to the factory.');
    }
    if (this.#instances.has(inputFieldWithOptionFilter.id)) {
      throw new Error(`A InputHasOptionFilter with id '${inputFieldWithOptionFilter.id}' has already been initialized.`);
    }

    /* DOM Elements */
    const inputFieldContext = inputFieldWithOptionFilter;
    const scrollContainer = inputFieldContext.querySelector('.c-input--has-option-filter__field');
    const searchbar = inputFieldContext.querySelector('.c-input--has-option-filter__search-input input');
    const listType = inputFieldContext.getAttribute('data-il-ui-component');
    const itemList = inputFieldContext.querySelector('.c-field--has-option-filter__list');
    const items = itemList.querySelectorAll('.c-field--has-option-filter__item');
    const messageNoMatch = inputFieldContext.querySelector('.message-no-match');
    const resultCountDisplay = inputFieldContext.querySelector('.c-input--has-option-filter__synopsis [role="status"]');

    /* Buttons */
    const clearFilterButton = inputFieldContext.querySelector('.c-input--has-option-filter__clear-search');
    const engageDisengageToggle = inputFieldContext.querySelector('.c-input--has-option-filter__visibility-toggle');
    const toggleExpandText = engageDisengageToggle.querySelector('.text-expand');
    const toggleCollapseText = engageDisengageToggle.querySelector('.text-collapse');

    const instance = new OptionFilter(
      inputFieldWithOptionFilter,
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
    );

    this.#instances.set(inputFieldWithOptionFilter.id, instance);

    return instance;
  }

  /**
   * @param {string} inputID
   * @return {OptionFilter|null}
   */
  get(inputID) {
    if (this.#instances.has(inputID)) {
      return this.#instances.get(inputID);
    }
    return null;
  }
}
