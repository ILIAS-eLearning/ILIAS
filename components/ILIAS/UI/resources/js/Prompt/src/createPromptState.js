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

import * as PROMPT from './constants.js';
import PromptState from './PromptState.js';

/**
 * @param {string} selector
 * @param {DocumentFragment} fragment
 * @returns {string|null}
 */
function extractElementText(fragment, selector) {
  return fragment.querySelector(selector)?.textContent ?? null;
}

/**
 * @param {string} attribute
 * @param {DocumentFragment} fragment
 * @returns {string|null}
 */
function extractAttribute(fragment, attribute) {
  return fragment.querySelector(`[${attribute}]`)?.getAttribute(attribute) ?? null;
}

/**
 * @param {string} selector
 * @param {DocumentFragment} fragment
 * @returns {HTMLElement[]}
 */
function extractElementChildren(fragment, selector) {
  return Array.from(fragment.querySelector(selector)?.children ?? []);
}

/**
 * Creates a Prompt State from its raw format (as DocumentFragment).
 *
 * @param {DocumentFragment} fragment
 * @returns {PromptState}
 */
export default function createPromptState(fragment) {
  const instruction = extractAttribute(fragment, PROMPT.PROMPT_STATE_INSTRUCTION_ATTRIBUTE);
  if (!instruction) {
    throw new Error('State must provide instruction');
  }
  return new PromptState(
    instruction,
    extractAttribute(fragment, PROMPT.PROMPT_STATE_REDIRECT_ATTRIBUTE),
    extractElementText(fragment, PROMPT.PROMPT_STATE_SECTION_TITLE_SELECTOR),
    extractElementChildren(fragment, PROMPT.PROMPT_STATE_SECTION_CONTENT_SELECTOR),
    extractElementChildren(fragment, PROMPT.PROMPT_STATE_SECTION_BUTTONS_SELECTOR),
    Array.from(fragment.querySelectorAll('script')),
  );
}
