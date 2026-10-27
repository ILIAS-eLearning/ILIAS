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

export const PROMPT_CLASS = 'c-prompt';
export const PROMPT_STATE_CLASS = `${PROMPT_CLASS}-state`;
export const PROMPT_CLOSE_CLASS = `${PROMPT_CLASS}__close`;

export const PROMPT_STATE_INSTRUCTION_ATTRIBUTE = 'data-instruction';
export const PROMPT_STATE_REDIRECT_ATTRIBUTE = 'data-redirect-url';
export const PROMPT_STATE_SECTION_ATTRIBUTE = 'data-section';

export const PROMPT_STATE_INSTRUCTION_SHOW = 'show';
export const PROMPT_STATE_INSTRUCTION_CLOSE = 'close';
export const PROMPT_STATE_INSTRUCTION_REDIRECT = 'redirect';

export const PROMPT_STATE_SECTION_TITLE = 'title';
export const PROMPT_STATE_SECTION_CONTENT = 'content';
export const PROMPT_STATE_SECTION_BUTTONS = 'buttons';

export const PROMPT_SELECTOR = `.${PROMPT_CLASS}`;
export const PROMPT_TITLE_SELECTOR = `.${PROMPT_CLASS}__title`;
export const PROMPT_CLOSE_SELECTOR = `.${PROMPT_CLOSE_CLASS}`;
export const PROMPT_BUTTONS_SELECTOR = `.${PROMPT_CLASS}__buttons`;
export const PROMPT_SCRIPTS_SELECTOR = `.${PROMPT_CLASS}__scripts`;
export const PROMPT_CONTENT_SELECTOR = `.${PROMPT_CLASS}__contents`;
export const PROMPT_STATE_SELECTOR = `.${PROMPT_STATE_CLASS}`;
export const PROMPT_STATE_REDIRECT_SELECTOR = `[${PROMPT_STATE_REDIRECT_ATTRIBUTE}]`;
export const PROMPT_STATE_INSTRUCTION_SELECTOR = `[${PROMPT_STATE_INSTRUCTION_ATTRIBUTE}]`;
export const PROMPT_STATE_SECTION_TITLE_SELECTOR = `[${PROMPT_STATE_SECTION_ATTRIBUTE}="${PROMPT_STATE_SECTION_TITLE}"]`;
export const PROMPT_STATE_SECTION_CONTENT_SELECTOR = `[${PROMPT_STATE_SECTION_ATTRIBUTE}="${PROMPT_STATE_SECTION_CONTENT}"]`;
export const PROMPT_STATE_SECTION_BUTTONS_SELECTOR = `[${PROMPT_STATE_SECTION_ATTRIBUTE}="${PROMPT_STATE_SECTION_BUTTONS}"]`;
