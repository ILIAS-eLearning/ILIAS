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
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class PromptRegistry {
  /** @type {Map<string, Prompt>} (prompt-id => Prompt) */
  #promptMap = new Map();

  /**
   * @param {string} id
   * @param {Prompt} prompt
   */
  set(id, prompt) {
    if (this.#promptMap.has(id)) {
      throw new Error(`Prompt already exists: '${id}'`);
    }
    this.#promptMap.set(id, prompt);
  }

  /**
   * @param {string} id
   * @returns {Prompt|null}
   */
  get(id) {
    if (this.#promptMap.has(id)) {
      return this.#promptMap.get(id);
    }
    return null;
  }
}
