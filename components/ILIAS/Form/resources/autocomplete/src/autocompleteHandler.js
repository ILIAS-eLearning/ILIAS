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

const moreValue = 'more';
const triggerTimeout = 500;
const listCssClass = 'c-form__autocomplete';

/**
 *
 * @type {AbortController}
 */
let controller;

/**
 *
 * @type {Number}
 */
let timeoutId;

/**
 * @param {HTMLElement} container
 * @returns {void}
 */
function setAccessibilityAttributesToContainer(container) {
  const ariaLive = document.createAttribute('role');
  ariaLive.value = 'status';
  container.setAttributeNode(ariaLive);
  const ariaRelevant = document.createAttribute('aria-relevant');
  ariaRelevant.value = 'additions';
  container.setAttributeNode(ariaRelevant);
}

/**
 * @param {Object} values
 * @returns {Array}
 */
function buildItems(values) {
  if (typeof values.items === 'undefined') {
    return values;
  }

  const valueArray = [];
  Object.entries(values.items).forEach(
    ([key, value]) => {
      valueArray[key] = value;
    },
  );
  return valueArray;
}

/**
 *
 * @param {String} label
 * @param {String} value
 * @param {Integer} id
 * @returns {HTMLElement}
 */
function buildListElement(label, value, id) {
  const listElement = document.createElement('li');
  listElement.tabIndex = 0;
  listElement.textContent = label;
  listElement.dataset.value = value;
  if (typeof id !== 'undefined') {
    listElement.dataset.id = id;
  }
  return listElement;
}

/**
 * @param {HTMLElement} inputField
 * @param {Object} config
 * @returns {void}
 */
function removeList(inputField, config) {
  if (config.appendTo) {
    document.querySelector(config.appendTo)?.querySelector(`.${listCssClass}`)?.remove();
  } else {
    inputField.parentNode.querySelector(`.${listCssClass}`)?.remove();
  }
}

function clearTimeout() {
  if (typeof timeoutId === 'number') {
    window.clearTimeout(timeoutId);
    timeoutId = undefined;
  }
}

/**
 * @param {String} fullUrl
 * @param {HTMLElement} inputField
 * @param {Object} config
 * @returns {void}
 */
async function fetchListItemsAndBuildSelector(fullUrl, inputField, config) {
  try {
    const { signal } = controller;

    const response = await fetch(fullUrl, { signal });
    if (!response.ok) {
      throw new Error(`Response status: ${response.status}`);
    }

    const responseJson = await response.json();
    const items = buildItems(responseJson);

    if (items.length === 0) {
      removeList(inputField, config);
      return;
    }

    const list = document.createElement('ul');
    if (typeof config.appendTo !== 'string') {
      list.style.left = `${inputField.offsetLeft}px`;
    }
    list.style.minWidth = `${inputField.offsetWidth}px`;
    list.classList.add(listCssClass);
    items.forEach((elem) => {
      if (inputField.value !== elem.value && inputField.value.includes(elem.value)) {
        return;
      }
      list.appendChild(buildListElement(elem.label || elem.value, elem.value, elem.id));
    });
    if (responseJson.hasMoreResults) {
      list.appendChild(buildListElement(config.moreText, moreValue));
    }
    if (list.children.length === 0) {
      return;
    }
    list.addEventListener('keydown', (e) => { keyHandler(inputField, e, config); });
    list.addEventListener('click', (e) => { onSelectHandler(inputField, e, config); });
    const activeElementValue = document.activeElement.dataset.value;
    removeList(inputField, config);
    if (config.appendTo) {
      document.querySelector(config.appendTo).appendChild(list);
      if (typeof activeElementValue !== 'undefined') {
        document.querySelector(config.appendTo)
          .querySelector(`[data-value="${activeElementValue}"]`)
          .focus();
      }
    } else {
      inputField.parentNode.insertBefore(list, inputField.nextElementSibling);
      if (typeof activeElementValue !== 'undefined') {
        inputField.parentNode.querySelector(`[data-value="${activeElementValue}"]`).focus();
      }
    }
    if (config.open) {
      config.open(list);
    }
  } catch (e) {
  }
}

/**
 * @param {HTMLInputElement} inputField
 * @param {Event} e
 * @param {Object} config
 * @returns {void}
 */
function keyHandler(inputField, e, config) {
  if (e.key === 'Enter' && e.target.nodeName === 'LI') {
    e.preventDefault();
    onSelectHandler(inputField, e, config);
  }

  if (e.key === 'ArrowDown') {
    e.stopImmediatePropagation();
    e.preventDefault();
    if (e.target === inputField) {
      if (config.appendTo) {
        document.querySelector(config.appendTo)
          .querySelector(`.${listCssClass}`)
          .firstElementChild
          .focus();
      } else {
        e.target.parentNode.querySelector(`.${listCssClass}`)?.firstElementChild?.focus();
      }
    }

    if (e.target.nodeName === 'LI' && e.target.nextElementSibling !== null) {
      e.target.nextElementSibling.focus();
    }
  }

  if (e.key === 'ArrowUp' && e.target.nodeName === 'LI') {
    e.stopImmediatePropagation();
    e.preventDefault();

    if (e.target.previousElementSibling === null) {
      inputField.focus();
    } else {
      e.target.previousElementSibling.focus();
    }
  }
}

/**
 * @param {Event} e
 * @param {Object} config
 * @returns {void}
 */
function onChangeHandler(e, config) {
  if (typeof e.key === 'undefined' || e.key === 'Tab'
    || e.key === 'ArrowDown' || e.key === 'ArrowUp') {
    return;
  }

  if (e.target.value.length < config.autocompleteLength) {
    clearTimeout();
    removeList(e.target, config);
    return;
  }

  const term = getTermFromSelectedValue(e.target.value, config.delimiter);

  clearTimeout();
  timeoutId = window.setTimeout(
    () => {
      fetchListItemsAndBuildSelector(
        `${config.dataSource}&term=${encodeURIComponent(term)}`,
        e.target,
        config,
      );
    },
    triggerTimeout,
  );
}

/**
 *
 * @param {String} value
 * @param {String} delimiter
 * @returns {String}
 */
function getTermFromSelectedValue(value, delimiter) {
  if (delimiter === null) {
    return value.trim();
  }

  return value.split(delimiter).at(-1).trim();
}

/**
 * @param {HTMLInputElement} inputField
 * @param {Event} e
 * @param {Object} config
 * @returns {void}
 */
function onSelectHandler(inputField, e, config) {
  controller.abort();
  controller = new AbortController();
  let { value } = e.target.dataset;
  if (value === moreValue) {
    const term = getTermFromSelectedValue(
      inputField.value,
      config.delimiter,
    );
    fetchListItemsAndBuildSelector(
      `${config.dataSource}&term=${encodeURIComponent(term)}&fetchall=1`,
      inputField,
      config,
    );
    return;
  }

  if (config.delimiter !== null) {
    const currentValueArray = inputField.value
      .split(config.delimiter);
    let currentValue = '';
    if (currentValueArray.length > 1) {
      currentValue = `${currentValueArray.slice(0, -1).join(`${config.delimiter} `)}${config.delimiter} `;
    }
    value = `${currentValue}${value}${config.delimiter} `;
  }
  inputField.value = value;
  inputField.focus();
  e.target.parentNode.remove();

  if (config.submitOnSelection && 'id' in e.target.dataset) {
    window.location.href = `${config.submitUrl}&selected_id=${encodeURIComponent(e.target.dataset.id)}`;
  }
}

export default function autocompleteHandler(autocompleteInput, config) {
  controller = new AbortController();
  setAccessibilityAttributesToContainer(autocompleteInput.parentElement);
  autocompleteInput.addEventListener('keydown', (e) => { keyHandler(autocompleteInput, e, config); });
  autocompleteInput.addEventListener('keyup', (e) => { onChangeHandler(e, config); });
}
