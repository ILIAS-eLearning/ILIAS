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

import createPromptState from './createPromptState.js';

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class PromptStateLoader {
  /** @type {AsyncRenderer} */
  #asyncRenderer;

  /** @type {Map<string, {request: RequestInit, state: PromptState}>} (url => {...}) */
  #cache = new Map();

  /**
   * @param {AsyncRenderer} asyncRenderer
   */
  constructor(asyncRenderer) {
    this.#asyncRenderer = asyncRenderer;
  }

  /**
   * @param {URL} url
   * @param {RequestInit} request
   * @returns {Promise<PromptState>}
   * @throws {Error} if state could not be fetched
   */
  async load(endpoint, request = {}) {
    const cacheKey = endpoint.href;
    const cacheHit = this.#cache.get(cacheKey);

    // only return cache hit if the request is GET
    if (cacheHit && !request.body) {
      return cacheHit.state;
    }

    const rawState = await this.#asyncRenderer.loadContent(endpoint, request);
    const state = createPromptState(rawState);
    this.#cache.set(cacheKey, { request, state });

    return state;
  }
}
