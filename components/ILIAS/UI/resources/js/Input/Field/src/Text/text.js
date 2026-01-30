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
 * @typedef {Object} Config
 * @property {int} autocompleteTriggerTimeout
 * @property {int} suggestionStarts
 */

/**
 * @param {HTMLInputElement} input
 * @param {Config} config
 * @param {undefined|URLBuilder} autocompleteEndpoint
 * @param {undefined|URLBuilderToken} autocompleteToken
 */
export default function init(
  input,
  config,
  autocompleteEndpoint,
  autocompleteToken,
) {
  if (!input || !input.id) {
    return;
  }

  const { autocompleteTriggerTimeout, suggestionStarts } = config;
  /** @var {undefined|AbortController} autocompleteList */
  let abortController;
  /** @var {undefined|number} autocompleteList */
  let timeout;
  /** @var {HTMLUListElement} autocompleteList */
  let autocompleteList;

  function handleAutocomplete() {
    if (abortController instanceof AbortController) {
      abortController.abort();
    }
    abortController = new AbortController();

    if (timeout !== undefined) {
      clearTimeout(timeout);
      timeout = undefined;
    }

    if (input.value.length < suggestionStarts) {
      return;
    }

    timeout = setTimeout(
      () => {
        const searchTerm = input.value;
        autocompleteEndpoint.writeParameter(autocompleteToken, searchTerm);
        return fetch(
          autocompleteEndpoint.getUrl().toString(),
          { signal: abortController.signal },
        )
          .then((response) => response.json())
          .catch(() => {})
          .then((data) => {
            if (!data || !(data.constructor instanceof Array)) {
              return;
            }
            showAutocompleteList(data);
          });
      },
      autocompleteTriggerTimeout,
    );
  }

  function showAutocompleteList(data) {
    if (data.length === 0) {
      hideAutocompleteList();
      return;
    }

    data.forEach((autocompleteResult) => {
      const listItem = document.createElement('li');
      listItem.dataset.value = autocompleteResult.value;
      listItem.innerText = autocompleteResult.display;

      listItem.addEventListener('click', () => {
        input.value = listItem.dataset.value;
        hideAutocompleteList();
      });

      autocompleteList.append(listItem);
    });
    autocompleteList.classList.remove('hidden');
  }

  function hideAutocompleteList() {
    clearAutocompleteList();
    autocompleteList.classList.add('hidden');
  }

  function clearAutocompleteList() {
    while (autocompleteList.firstChild) {
      autocompleteList.removeChild(autocompleteList.lastChild);
    }
  }

  function createAutocompleteList() {
    const autocompleteListElement = document.createElement('ul');
    autocompleteListElement.classList.add('c-form__autocomplete');
    autocompleteListElement.classList.add('hidden');
    input.parentElement.insertBefore(autocompleteListElement, input.nextSibling);

    window.addEventListener('click', (event) => {
      if (
        event.target !== autocompleteListElement
          || autocompleteListElement.contains(event.target)
      ) {
        hideAutocompleteList();
      }
    });

    return autocompleteListElement;
  }

  if (autocompleteEndpoint && autocompleteToken) {
    autocompleteList = createAutocompleteList();
    input.addEventListener('input', () => {
      clearAutocompleteList();
      handleAutocomplete();
    });
  }
}
