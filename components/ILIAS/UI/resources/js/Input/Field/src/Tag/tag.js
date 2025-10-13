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
 * @type {Array}
 */
const instances = [];

/**
 *
 * @type {AbortController}
 */
let abortController;

/**
 *
 * @type {number}
 */
let timeout;

/**
 * @type {string}
 */
const tagOrderablePlaceholderClass = 'c-field-tag__dropzone';

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
    a11y: {
      focusableTags: true,
    },
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
          spellcheck="false" class='c-field-tag__tag tagify__tag'
          aria-describedby="${inputId}-operation"
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
 * @returns {void}
 */
function retrieveAutocomplete(
  instance,
  suggestionsStartAfter,
  autocompleteEndpoint,
  autocompleteToken,
  event,
  tagAutocompleteTriggerTimeout,
) {
  if (typeof abortController !== 'undefined') {
    abortController.abort();
  }
  abortController = new AbortController();

  instance.whitelist = null;

  if (typeof timeout === 'number') {
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

/*
 * @param {Tagify} instance
 * @returns {void}
 */
function removePlaceholders(instance) {
  instance.DOM.scope.querySelectorAll(`.${tagOrderablePlaceholderClass}`).forEach(
    (elem) => {
      instance.DOM.scope.removeChild(elem);
    },
  );
}

/**
 * @param {Tagify} instance
 * @param {HTMLElement} draggedElement
 * @returns {void}
 */
function onDragStart(instance, draggedElement) {
  removePlaceholders(instance);

  const style = draggedElement.ownerDocument.defaultView.getComputedStyle(draggedElement);
  const dropzone = instance.DOM.scope.ownerDocument.createElement('div');
  dropzone.classList.add(tagOrderablePlaceholderClass);
  dropzone.style.height = style.height;
  dropzone.style.width = style.width;
  dropzone.style.marginRight = style.marginRight;
  dropzone.style.marginBottom = style.marginBottom;
  dropzone.style.marginLeft = style.marginLeft;
  dropzone.style.marginTop = style.marginTop;
  instance.DOM.scope.querySelectorAll(`.${instance.settings.classNames.tag}`).forEach(
    (elem) => {
      if (elem === draggedElement) {
        return;
      }
      if (elem.previousElementSibling !== draggedElement
        && !elem.previousElementSibling?.classList.contains(tagOrderablePlaceholderClass)) {
        elem.parentNode.insertBefore(dropzone.cloneNode(true), elem);
      }

      if (elem.nextElementSibling === draggedElement) {
        return;
      }

      elem.parentNode.insertBefore(dropzone.cloneNode(true), elem.nextElementSibling);
    },
  );
}

/**
 * @param {Tagify} instance
 * @param {KeyEvent} event
 * @returns {void}
 */
function deleteTagOnKeypress(instance, event) {
  if (event.key === 'Delete' && event.target.dataset.selected !== 'true') {
    instance.removeTags(event.target);
  }
}

/**
 * @param {Tagify} instance
 * @returns {void}
 */
function onChange(instance) {
  removePlaceholders(instance);
  instance.updateValueByDOMTags();
}

/**
 * @param {Tagify} instance
 * @returns {void}
 */
function addEventListenersForDeletion(instance) {
  instance.getTagElms().forEach((elem) => {
    elem.addEventListener(
      'keydown',
      (event) => { deleteTagOnKeypress(instance, event); },
    );
  });
  instance.on('add', (e) => {
    e.detail.tag.addEventListener(
      'keydown',
      (event) => { deleteTagOnKeypress(instance, event); },
    );
  });
}

/**
 *
 * @param {string} instanceId
 * @returns {Tagify}
 */
export function getTagifyInstance(instanceId) {
  return instances[instanceId];
}

/**
 * @param {Tagify} Tagify
 * @param {function} makeDraggable
 * @param {HTMLInput} input
 * @param {Object} config
 * @param {array} value
 * @param {URLBuilder} autocompleteEndpoint
 * @param {URLBuilderToken} autocompleteToken
 * @returns {void}
 */
export function init(
  Tagify,
  makeDraggable,
  input,
  config,
  value,
  autocompleteEndpoint,
  autocompleteToken,
) {
  instances[input.id] = new Tagify(
    input,
    buildSettings(input.id, config),
  );
  instances[input.id].addTags(value);
  addEventListenersForDeletion(instances[input.id]);
  if (typeof autocompleteEndpoint !== 'undefined') {
    instances[input.id].on('input', (event) => {
      retrieveAutocomplete(
        instances[input.id],
        config.suggestionStarts,
        autocompleteEndpoint,
        autocompleteToken,
        event,
        config.autocompleteTriggerTimeout,
      );
    });
  }
  if (config.orderable) {
    makeDraggable(
      'move',
      instances[input.id].DOM.scope,
      instances[input.id].settings.classNames.tag,
      tagOrderablePlaceholderClass,
      {
        infoContainer: instances[input.id].DOM.scope.previousElementSibling,
        texts: {
          default() {
            return config.accessibilityInfo.default;
          },
          tagSelected(selectedTag) {
            return config.accessibilityInfo.tagSelected.replace(
              '%s',
              instances[input.id].getTagTextNode(selectedTag).innerText,
            );
          },
          position(selectedPlaceholder) {
            if (selectedPlaceholder.previousElementSibling === null) {
              return config.accessibilityInfo.positionInfoFirst;
            }

            return config.accessibilityInfo.positionInfo.replace(
              '%s',
              instances[input.id].getTagTextNode(selectedPlaceholder.previousSibling).innerText,
            );
          },
        },
      },
      (draggedElement) => { onDragStart(instances[input.id], draggedElement); },
      () => { onChange(instances[input.id]); },
    );
  }
}
