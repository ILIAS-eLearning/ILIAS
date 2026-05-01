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
 *
 * @type {undefined|AbortController}
 */
let abortController;

/**
 *
 * @type {undefined|number}
 */
let timeout;

/**
 * @param {Tagify} instance
 * @returns {HTMLElement|null}
 */
function getEditableInput(instance) {
  return instance.DOM.input
    ?? instance.DOM.scope.querySelector('[contenteditable="true"]');
}

/**
 * @param {HTMLLabelElement} label
 * @param {HTMLInputElement} input
 * @param {Document} doc
 * @returns {string}
 */
function ensureLabelId(label, input, doc) {
  if (label.id) {
    return label.id;
  }

  const baseId = `${input.id}-label`;
  let labelId = baseId;
  let suffix = 1;
  while (doc.getElementById(labelId) !== null) {
    labelId = `${baseId}-${suffix}`;
    suffix += 1;
  }
  label.id = labelId;

  return labelId;
}

/**
 * @param {Tagify} instance
 * @param {HTMLInputElement} input
 */
function bindLabelFocus(instance, input) {
  const formContext = input.closest('.c-input');
  if (formContext === null) {
    return;
  }

  const label = formContext.querySelector(`label[for="${input.id}"]`);
  if (label === null) {
    return;
  }

  const editableInput = getEditableInput(instance);
  if (editableInput !== null) {
    const labelId = ensureLabelId(label, input, editableInput.ownerDocument);
    editableInput.setAttribute('aria-labelledby', labelId);
  }

  label.addEventListener('click', (event) => {
    if (input.readOnly || input.disabled) {
      return;
    }

    event.preventDefault();
    getEditableInput(instance)?.focus();
  });
}

/**
 * @param {string} inputId
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
        tagData.value = encodeURIComponent(tagData.value);
      }
      tagData.display = tagData.display
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
    },
    templates: {
      wrapper(input, _s) {
        return `<div class="${_s.classNames.namespace} ${_s.mode ? `${_s.classNames[`${_s.mode}Mode`]}` : ''} ${input.className}"
            ${_s.readonly ? 'readonly' : ''}
            ${_s.disabled ? 'disabled' : ''}
            ${_s.required ? 'required' : ''}
            ${_s.mode === 'select' ? "spellcheck='false'" : ''}
            tabIndex="-1">
            ${this.settings.templates.input.call(this)}
          \u200B
        </div>`;
      },
      tag(tagData) {
        return `<div contenteditable='false'
          spellcheck="false" class='tagify__tag'
          value="${tagData.value}"
          tabindex="0">
          <span title='remove tag' class='tagify__tag__removeBtn'></span>
          <div>
              <span class='tagify__tag-text'>${tagData.display}</span>
          </div>
        </div>`;
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
 * @param {number} suggestionsStartAfter
 * @param {URLBuilder} autocompleteEndpoint
 * @param {URLBuilderToken} autocompleteToken
 * @param {InputEvent} event
 * @param {number} tagAutocompleteTriggerTimeout
 */
function retrieveAutocomplete(
  instance,
  suggestionsStartAfter,
  autocompleteEndpoint,
  autocompleteToken,
  event,
  tagAutocompleteTriggerTimeout,
) {
  if (abortController instanceof AbortController) {
    abortController.abort();
  }
  abortController = new AbortController();

  instance.whitelist = null;

  if (timeout !== undefined) {
    instance.DOM.scope.ownerDocument.defaultView.clearTimeout(timeout);
    timeout = undefined;
  }

  if (event.detail.value.length < suggestionsStartAfter) {
    return;
  }

  timeout = instance.DOM.scope.ownerDocument.defaultView.setTimeout(
    () => {
      const searchTerm = event.detail.value;
      autocompleteEndpoint.writeParameter(autocompleteToken, searchTerm);
      instance.loading(true);
      fetch(autocompleteEndpoint.getUrl().toString(), { signal: abortController.signal })
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
 * @param {HTMLElement} input
 * @param {Object} config
 * @param {Array} value
 * @param {undefined|URLBuilder} autocompleteEndpoint
 * @param {undefined|URLBuilderToken} autocompleteToken
 */
export default function init(
  Tagify,
  input,
  config,
  value,
  autocompleteEndpoint,
  autocompleteToken,
) {
  const instance = new Tagify(
    input,
    buildSettings(input.id, config),
  );
  bindLabelFocus(instance, input);
  instance.addTags(value);
  if (autocompleteEndpoint !== undefined) {
    instance.on('input', (event) => {
      retrieveAutocomplete(
        instance,
        config.suggestionStarts,
        autocompleteEndpoint,
        autocompleteToken,
        event,
        config.autocompleteTriggerTimeout,
      );
    });
  }
}
