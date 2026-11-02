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

import createHashCode from '../../Core/src/createHashCode.js';

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class PromptRenderer {
  /** @type {HTMLFormElement} */
  #closeDialogElement;

  /** @type {HTMLSpanElement} */
  #titleElement;

  /** @type {HTMLElement} */
  #contentElement;

  /** @type {HTMLElement} */
  #buttonsElement;

  /** @type {HTMLElement} */
  #scriptsElement;

  /** @type {Set<string>} */
  #insertedScriptFingerprints = new Set();

  /**
   * @param {HTMLFormElement} closeDialogElement
   * @param {HTMLSpanElement} titleElement
   * @param {HTMLElement} contentElement
   * @param {HTMLElement} buttonsElement
   * @param {HTMLElement} scriptsElement
   */
  constructor(closeDialogElement, titleElement, contentElement, buttonsElement, scriptsElement) {
    this.#closeDialogElement = closeDialogElement;
    this.#titleElement = titleElement;
    this.#contentElement = contentElement;
    this.#buttonsElement = buttonsElement;
    this.#scriptsElement = scriptsElement;
  }

  /**
   * @param {string|null} title (pass null to keep current title)
   * @param {HTMLElement[]} contents
   * @param {HTMLButtonElement[]} buttons
   * @param {HTMLScriptElement[]} [scripts=[]]
   */
  render(title, contents, buttons, scripts = []) {
    this.#maybeUpdateTitle(title);
    this.#updateContent(contents);
    this.#updateButtons(buttons);
    this.#insertScriptsOnce(scripts);
  }

  /**
   * @param {string|null} title
   */
  #maybeUpdateTitle(title) {
    // ensure title is only updated if provided
    if (title !== null && title.trim() !== '') {
      this.#titleElement.textContent = title;
    }
  }

  /**
   * @param {HTMLElement[]} contents
   */
  #updateContent(contents) {
    this.#contentElement.replaceChildren(...contents);
  }

  /**
   * @param {HTMLButtonElement[]} buttons
   */
  #updateButtons(buttons) {
    // ensure the close action stays in last place
    this.#buttonsElement.replaceChildren(...buttons, this.#closeDialogElement);
  }

  /**
   * @param {HTMLScriptElement[]} scripts
   */
  #insertScriptsOnce(scripts) {
    scripts.forEach((script) => {
      const fingerprint = createHashCode(JSON.stringify(script.outerHTML));
      if (!this.#insertedScriptFingerprints.has(fingerprint)) {
        this.#scriptsElement.append(script);
        this.#insertedScriptFingerprints.add(fingerprint);
      }
    });
  }
}
