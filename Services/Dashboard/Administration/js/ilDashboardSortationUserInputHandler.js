

/**
 * @param {Element} selectionInput
 * @param {Element} checkboxInput
 */
function updateSelectionInputWithActivatedOptions(selectionInput, checkboxInput) {
    selectionInput.querySelectorAll('option').forEach(function (element) {
        const checkbox = checkboxInput.querySelector('input[value=\"' + element.value + '\"]')
        if (checkbox &&  checkbox.checked === false) {
            element.setAttribute('disabled', 'disabled');
        }
        if (checkbox && checkbox.checked === true) {
            element.removeAttribute('disabled');
        }
    });
    updateSelectedValueToFirstPossibleOption(selectionInput);
    switchBackToDefaultIfEnabled(selectionInput, checkboxInput);
}

/**
 * @param {Element} selectionInput
 * @param {Element} checkboxInput
 */
function switchBackToDefaultIfEnabled(selectionInput, checkboxInput) {
    selectionInput.querySelectorAll('option').forEach(function (element) {
        const checkboxOption = checkboxInput.querySelector('input[value=\"' + element.value + '\"]')
        if (element.hasAttribute('default') && checkboxOption && checkboxOption.checked === true) {
            element.setAttribute('selected', 'selected');
        }
    });
}

/**
 * @param {Element} selectionInput
 * @param {Element} checkboxInput
 */
function updateSelectedValueToFirstPossibleOption(selectionInput) {
    const selectedDefaultSortation =  selectionInput.querySelector('option[selected="selected"]');
    if (!selectedDefaultSortation || selectedDefaultSortation.getAttribute('disabled') === 'disabled') {
        const firstPossibleOption = selectionInput.querySelector('option:not([disabled])');
        if (firstPossibleOption) {
            firstPossibleOption.setAttribute('selected', 'selected');
        }
    }
}

/**
 * @param {Element} checkboxInput
 */
function ensureLastOptionNotDeselectable(checkboxInput) {
    const checkedCheckbox = checkboxInput.querySelectorAll('input[type=\"checkbox\"]:checked');
    if (checkedCheckbox.length === 1) {
        checkedCheckbox[0].setAttribute('disabled', 'disabled');
    } else {
        checkboxInput.querySelectorAll('input[type=\"checkbox\"]:disabled').forEach(function (element) {
            element.removeAttribute('disabled');
            }
        )
    }
}

function disableMinusOption(selectionInput) {
    const minusOption = selectionInput.querySelector('option[value=\"\"]');
    if (minusOption) {
        minusOption.setAttribute('disabled', 'disabled');
        minusOption.style.display = 'none';
    }
}

/**
 * @param {int} view
 */
function handleUserInputForSortationsByView(view) {
    const selectionInput = document.querySelectorAll('[data-select="sorting' + view + '"]')[0];
    const checkboxInput = document.querySelectorAll('[data-checkbox="activeSorting' + view + '"]')[0];
    disableMinusOption(selectionInput);
    updateSelectionInputWithActivatedOptions(selectionInput, checkboxInput);
    const selectedOption = selectionInput.querySelectorAll('option[selected=\"selected\"]')[0];
    if (selectedOption) {
        selectedOption.setAttribute('default', 'default');
    }
    ensureLastOptionNotDeselectable(checkboxInput);
    checkboxInput.onchange = function () {
        updateSelectionInputWithActivatedOptions(selectionInput, checkboxInput);
        ensureLastOptionNotDeselectable(checkboxInput);
    };
}

