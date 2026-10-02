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

import Prompt from './prompt.class.js';

export default class PromptFactory {
  /**
    * @type {import('../../Core/src/AsyncRenderer.js').default}
    */
  #asyncRenderer;

  /**
   * @type {Array<string, Prompt>}
   */
  #instances = [];

  /**
   * @param {import('../../Core/src/AsyncRenderer.js').default} asyncRenderer
   */
  constructor(asyncRenderer) {
    this.#asyncRenderer = asyncRenderer;
  }

  /**
   * @param {string} id
   * @return {void}
   * @throws {Error} if the prompt was already initialized.
   */
  init(id) {
    if (this.#instances[id] !== undefined) {
      return;
    }

    try {
      this.#instances[id] = new Prompt(this.#asyncRenderer, id);
    } catch (error) {
      // Prompt element may not exist yet during async content replacement.
    }
  }

  /**
   * @param {string} id
   * @return {Prompt|null}
   */
  get(id) {
    return this.#instances[id] ?? null;
  }
}
