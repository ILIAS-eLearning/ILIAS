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

const tagAutocompleteTriggerTimeout = 200;

/**
 * @param {HTMLInput} input
 * @param {Object} config
 * @returns {Object}
 */
function buildSettings(inputId, config) {
  return {
    id: inputId,
    whitelist: config.options,
    enforceWhitelist: !config.userInput,
    duplicates: config.allowDuplicates,
    maxTags: config.maxItems,
    delimiters: null,
    originalInputValueFormat: (valuesArr) => valuesArr.map((item) => item.value),
    dropdown: {
      enabled: config.dropdownSuggestionsStartAfter,
      maxItems: config.dropdownMaxItems,
      closeOnSelect: config.dropdownCloseOnSelect,
      highlightFirst: config.highlight,
    },
    transformTag(tagData) {
      if (!tagData.display) {
        tagData.display = tagData.value;
        tagData.value = encodeURI(tagData.value);
      }
      tagData.display = tagData.display
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
    },
    templates: {
      tag(tagData) {
        return `<tag contenteditable='false'
          spellcheck="false" class='tagify__tag'
          value="${tagData.value}"
          tabindex="0">
          <x title='remove tag' class='tagify__tag__removeBtn'></x>
          <div>
              <span class='tagify__tag-text'>${tagData.display}</span>
          </div>
        </tag>`;
      },
      dropdownItem(tagData) {
        return `<div class='tagify__dropdown__item' tagifySuggestionIdx="${tagData.tagifySuggestionIdx}" value="${tagData.value}">
          <span>${tagData.display}</span>
          </div>`;
      },
    },
  };
}

/**
 * @param {Tagify} instance
 * @param {Object} context
 * @param {URL} autocompleteEndpoint
 * @param {InputEvent} event
 */
function retrieveAutocomplete(
  instance,
  context,
  autocompleteEndpoint,
  event,
) {
  context.controller.abort();
  context.controller = new AbortController();

  instance.whitelist = null;

  if (typeof context.timeout === 'number') {
    instance.DOM.scope.ownerDocument.defaultView.clearTimeout(context.timeout);
    context.timeout = undefined;
  }

  context.timeout = instance.DOM.scope.ownerDocument.defaultView.setTimeout(
    () => {
      const searchTerm = event.detail.value;
      autocompleteEndpoint.searchParams.append('term', searchTerm);
      instance.loading(true);
      fetch(autocompleteEndpoint.toString(), { signal: context.timeout.signal })
        .then((answer) => answer.json())
        .catch(() => {})
        .then((options) => {
          instance.whitelist = options;
          instance.loading(false).dropdown.show(searchTerm);
        });
    },
    tagAutocompleteTriggerTimeout,
  );
}

/**
 * @param {Tagify} Tagify
 * @param {HTMLInput} input
 * @param {Object} config
 * @param {array} value
 */
export default function init(Tagify, input, config, value) {
  const instance = new Tagify(
    input,
    buildSettings(input.id, config),
  );
  instance.addTags(value);
  if (config.autocompleteEndpoint !== null) {
    const context = {
      controller: new AbortController(),
      timeout : undefined,
    };
    instance.on('input', (event) => {
      retrieveAutocomplete(
        instance,
        context,
        new URL(config.autocompleteEndpoint),
        event,
      );
    });
  }
}
