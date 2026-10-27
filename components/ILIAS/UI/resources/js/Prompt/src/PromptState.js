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
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class PromptState {
  /** @type {string} */
  instruction;

  /** @type {string|null} */
  redirect;

  /** @type {string|null} */
  title;

  /** @type {HTMLElement[]} */
  contents;

  /** @type {HTMLElement[]} */
  buttons;

  /** @type {HTMLScriptElement[]} */
  scripts;

  /**
   * @param {string} instruction
   * @param {string} redirect
   * @param {string|null} title
   * @param {HTMLElement[]} contents
   * @param {HTMLButtonElement[]} buttons
   * @param {HTMLScriptElement[]} scripts
   */
  constructor(instruction, redirect, title, contents, buttons, scripts) {
    this.instruction = instruction;
    this.redirect = redirect;
    this.title = title;
    this.contents = contents;
    this.buttons = buttons;
    this.scripts = scripts;
  }
}
