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
 *********************************************************************/

il.Dashboard = il.Dashboard || {};

 il.Dashboard.handleUserInputForSortationsByView = function() {
    function updateSelectionInputWithActivatedOptions(selectionInput, checkboxInput) {
        selectionInput.querySelectorAll('option').forEach(function (element) {
            if (element.value !== '') {
                const checkbox = checkboxInput('input[data-value="' + element.value + '"]')[0];
                if (checkbox.checked === false) {
                    element.setAttribute('disabled', 'disabled');
                } else {
                    element.removeAttribute('disabled');
                }
            }
        });
        updateSelectedValueToFirstPossibleOption(selectionInput);
        switchBackToDefaultIfEnabled(selectionInput, checkboxInput);
    }

    function switchBackToDefaultIfEnabled(selectionInput, checkboxInput) {
        selectionInput.querySelectorAll('option').forEach(function (element) {
            const checkboxOption = checkboxInput('input[data-value="' + element.value + '"]')[0];
            if (element.hasAttribute('default') && checkboxOption && checkboxOption.checked) {
                selectionInput.querySelectorAll('option').forEach(function (option) {
                    option.removeAttribute('selected');
                });
                element.setAttribute('selected', 'selected');
            }
        });
    }

    function updateSelectedValueToFirstPossibleOption(selectionInput) {
        const selectedDefaultSortation = selectionInput.querySelector('option[selected="selected"]:not([disabled="disabled"])');
        if (!selectedDefaultSortation) {
            const firstPossibleOption = selectionInput.querySelector('option:not([disabled])');
            if (firstPossibleOption) {
                firstPossibleOption.setAttribute('selected', 'selected');
            }
        }
    }

    function ensureLastOptionNotDeselectable(checkboxInput) {
        const checkedCheckbox = checkboxInput('input[type="checkbox"]:checked');
        if (checkedCheckbox.length === 1) {
            checkedCheckbox[0].setAttribute('disabled', 'disabled');
        } else {
            checkboxInput('input[type="checkbox"]:disabled').forEach(function (element) {
                element.removeAttribute('disabled');
            });
        }
    }

    function selectFrom(nodeList) {
        const list = Array.from(nodeList);
        return (selector) => selector ?
            list.flatMap(n => Array.from(n.querySelectorAll(selector))) :
            Array.from(list);
    }

    /**
     * @param {int} view
     */
    return function(view) {
        const selectionInput = document.querySelector('[data-select="sorting' + view + '"]');
        const checkboxInput = selectFrom(document.querySelectorAll('[data-checkbox="activeSorting' + view + '"]'));
        const selectedOption = selectionInput.querySelector('option[selected="selected"]');
        if (selectedOption) {
            selectedOption.setAttribute('default', 'default');
        }
        updateSelectionInputWithActivatedOptions(selectionInput, checkboxInput);
        ensureLastOptionNotDeselectable(checkboxInput);
        checkboxInput('input').forEach(n => n.addEventListener('change', () => {
          updateSelectionInputWithActivatedOptions(selectionInput, checkboxInput);
          ensureLastOptionNotDeselectable(checkboxInput);
        }));
    };
}();

