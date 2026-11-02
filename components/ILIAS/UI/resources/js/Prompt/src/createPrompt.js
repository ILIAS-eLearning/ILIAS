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

import * as PROMPT from './constants.js';
import Prompt from './Prompt.js';
import PromptRenderer from './PromptRenderer.js';

/**
 * @param {HTMLElement} element
 * @param {string} selector
 * @returns {HTMLElement<selector>}
 */
function getElementOrAbort(element, selector) {
  const foundElement = element.querySelector(selector);
  if (!foundElement) {
    throw new Error(`Could not find Prompt element: ${selector}`);
  }
  return foundElement;
}

/**
 * @param {PromptStateLoader} promptStateLoader
 * @param {TemplateRenderer} templateRenderer
 * @param {HTMLElement} element
 */
export default function createPrompt(promptStateLoader, templateRenderer, element) {
  return new Prompt(
    new PromptRenderer(
      getElementOrAbort(element, `${PROMPT.PROMPT_BUTTONS_SELECTOR} > ${PROMPT.PROMPT_CLOSE_SELECTOR}`),
      getElementOrAbort(element, PROMPT.PROMPT_TITLE_SELECTOR),
      getElementOrAbort(element, PROMPT.PROMPT_CONTENT_SELECTOR),
      getElementOrAbort(element, PROMPT.PROMPT_BUTTONS_SELECTOR),
      getElementOrAbort(element, PROMPT.PROMPT_SCRIPTS_SELECTOR),
    ),
    promptStateLoader,
    Array.from(templateRenderer.createContent(getElementOrAbort(element, ':scope > template')).children),
    getElementOrAbort(element, ':scope > dialog'),
  );
}
