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

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class Prompt {
  /** @type {PromptRenderer} */
  #promptRenderer;

  /** @type {PromptStateLoader} */
  #stateLoader;

  /** @type {HTMLElement[]} */
  #errorElements;

  /** @type {HTMLDialogElement} */
  #dialogElement;

  /** @type {boolean} */
  #isLoading = false;

  /**
   * @param {PromptRenderer} promptRenderer
   * @param {PromptStateLoader} stateLoader
   * @param {HTMLElement[]} errorElements
   * @param {HTMLDialogElement} dialogElement
   */
  constructor(promptRenderer, stateLoader, errorElements, dialogElement) {
    this.#promptRenderer = promptRenderer;
    this.#stateLoader = stateLoader;
    this.#errorElements = errorElements;
    this.#dialogElement = dialogElement;

    // catch bubbling events to capture anchors and forms inside the dialog
    this.#dialogElement.addEventListener('submit', (event) => this.#interceptFormSubmitEvent(event));
    this.#dialogElement.addEventListener('click', (event) => this.#interceptAnchorClickEvent(event));
  }

  /**
   * Opens the Prompt with the last loaded Prompt State.
   */
  show() {
    this.#dialogElement.showModal();
  }

  /**
   * Closes the Prompt.
   */
  close() {
    this.#dialogElement.close();
  }

  /**
   * Loads a Prompt State from the server asynchronously and performs
   * the received instruction.
   *
   * @param {URL|string} url
   * @param {RequestInit} [request={}]
   * @returns {Promise<void>}
   * @throws {Error} if state could not be loaded or handled
   */
  async loadState(url, request = {}) {
    if (this.#isLoading) {
      return;
    }
    try {
      this.#isLoading = true;
      const state = await this.#stateLoader.load(this.#getUrl(url.toString()), request);
      this.#handleState(state);
    } catch (error) {
      this.#handleError(error);
    } finally {
      this.#isLoading = false;
    }
  }

  /**
   * @param {PromptState} state
   * @throws {Error} if the state cannot be processed
   */
  #handleState(state) {
    if (state.instruction === PROMPT.PROMPT_STATE_INSTRUCTION_SHOW) {
      this.#promptRenderer.render(state.title, state.contents, state.buttons, state.scripts);
      this.show();
    } else if (state.instruction === PROMPT.PROMPT_STATE_INSTRUCTION_REDIRECT) {
      this.#redirect(this.#getUrl(state.redirect));
    } else if (state.instruction === PROMPT.PROMPT_STATE_INSTRUCTION_CLOSE) {
      this.close();
    } else {
      throw new Error(`Could not process state instruction: '${state.instruction}'`);
    }
  }

  /**
   * @param {Error} error
   * @throws {Error} the given error
   */
  #handleError(error) {
    this.#promptRenderer.render(null, this.#errorElements, []);
    this.show();
    throw error;
  }

  /**
   * @param {SubmitEvent} event
   */
  #interceptFormSubmitEvent(event) {
    if (!event.defaultPrevented
      && event.target instanceof this.#dialogElement.ownerDocument.defaultView.HTMLFormElement
    ) {
      this.#submitFormInsidePrompt(event, event.target);
    }
  }

  /**
   * @param {SubmitEvent} event
   * @param {HTMLFormElement} form
   */
  #submitFormInsidePrompt(event, form) {
    const method = (event.submitter?.getAttribute('formmethod') ?? form.method).toUpperCase();

    // ignore prompt actions that affect the dialog
    if (method === 'DIALOG') {
      return;
    }

    const action = this.#getUrl(event.submitter?.getAttribute('formaction') ?? form.action);
    const body = new FormData(form);

    event.preventDefault();

    // convert body to query parameters for GET request
    if (method === 'GET') {
      const query = new URLSearchParams(body);
      action.search = query.toString();
      this.loadState(action);
      return;
    }

    this.loadState(action, { method, body });
  }

  /**
   * @param {PointerEvent} event
   */
  #interceptAnchorClickEvent(event) {
    if (!event.defaultPrevented
      && event.target instanceof this.#dialogElement.ownerDocument.defaultView.HTMLAnchorElement
    ) {
      this.#openAnchorInsidePrompt(event, event.target);
    }
  }

  /**
   * @param {MouseEvent} event
   * @param {HTMLAnchorElement} form
   */
  #openAnchorInsidePrompt(event, anchor) {
    // ignore anchors that dont open in same target
    if (anchor.target && anchor.target !== '_self') {
      return;
    }

    event.preventDefault();

    this.loadState(this.#getUrl(anchor.href));
  }

  /**
   * @param {URL} url
   */
  #redirect(url) {
    this.#dialogElement.ownerDocument.defaultView.location.replace(url);
  }

  /**
   * @param {string} rawUrl
   * @returns {URL}
   */
  #getUrl(rawUrl) {
    return new URL(rawUrl, this.#dialogElement.ownerDocument.defaultView.location.baseURI);
  }
}
