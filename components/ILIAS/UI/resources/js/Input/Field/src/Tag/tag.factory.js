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

import Tagify from '../../../../../../../../../node_modules/@yaireo/tagify/src/tagify.js';

export default class TagFactory {
  /**
     * @type {Array<string, Tag>}
     */
  instances = [];

  /**
     * @param {string} componentId
     * @param {array} config
     * @param {array} value
     * @return {void}
     * @throws {Error} if the input was already initialized.
     */
  init(componentId, config, value) {
    if (undefined !== this.instances[componentId]) {
      throw new Error(`Tag with input-id '${componentId}' has already been initialized.`);
    }
    const inputId = document.querySelector(`#${componentId} .c-input__field .c-field-tag__wrapper input`)?.id;
    const input = document.getElementById(inputId);
    const settings = {
      whitelist: config.options,
      enforceWhitelist: !config.userInput,
      duplicates: config.allowDuplicates,
      maxTags: config.maxItems,
      delimiters: null,
      templates: {
        tag: (tagData) => `<tag 
              contenteditable='false'
              spellcheck="false" class='tagify__tag'
              value="${tagData.value}"
              tabindex="0">
                <x title='remove tag' class='tagify__tag__removeBtn'></x>
                <div>
                  <span class='tagify__tag-text'>${tagData.display}</span>
                </div>
            </tag>`,
        dropdownItem: (tagData) => `<div 
              class='tagify__dropdown__item' 
              tagifySuggestionIdx="${tagData.tagifySuggestionIdx}" 
              value="${tagData.value}">
                <span>${tagData.display}</span>
              </div>`,
      },
      originalInputValueFormat: (valuesArr) => valuesArr.map((item) => item.value),
      dropdown: {
        enabled: config.dropdownSuggestionsStartAfter,
        maxItems: config.dropdownMaxItems,
        closeOnSelect: config.dropdownCloseOnSelect,
        highlightFirst: config.highlight,
      },
      transformTag: (tagData) => {
        if (!tagData.display) {
          tagData.display = tagData.value;
          tagData.value = encodeURIComponent(tagData.value);
        }
        tagData.display = tagData.display
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;');
      },
    };

    const tagify = new Tagify(input, settings);
    tagify.addTags(value);
    this.instances[componentId] = tagify;
  }

  /**
     * @param {string} componentId
     * @return {Tagify|null}
     */
  get(componentId) {
    return this.instances[componentId] ?? null;
  }
}
