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
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */

/**
 * @param {HTMLLinkElement} variableNameElement
 * @returns {string|null}
 */
function extractVariableName(variableNameElement) {
  const match = variableNameElement.textContent.match(/^\s*\{\{([^}]+)\}\}\s*$/);
  return match ? match[1].trim() : null;
}

/**
 * @param {Textarea} textareaComponent
 * @param {HTMLElement} mustacheVariablesElement
 */
export default function createMustacheVariables(textareaComponent, inputElement) {
  const mustacheVariableNames = inputElement.querySelectorAll('.c-input--has-mustache-variables__definitions > li > a');
  mustacheVariableNames.forEach((variableNameElement) => {
    const variableName = extractVariableName(variableNameElement);
    variableNameElement.addEventListener('click', () => {
      textareaComponent.insertCharactersAroundSelection(`{{${variableName}}}`, '');
    });
  });
}
