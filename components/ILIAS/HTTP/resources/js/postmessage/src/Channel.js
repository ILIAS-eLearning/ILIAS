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

import { envelopeCodec } from './protocol.js';

/**
 * One end of a postMessage connection between two windows of different
 * origins, e.g. an ILIAS page and content of the content domain or of an
 * external server in an iframe. Only messages coming from
 * the expected window AND the expected origin are dispatched, outgoing
 * messages are always addressed to that origin, never to '*'.
 */
export default class Channel {
  /** @type {Window} */
  #window;

  /** @type {function(): ?Window} */
  #peer;

  /** @type {string} */
  #origin;

  /** @type {string} */
  #name;

  /** @type {Map<string, function(*, object): *>} */
  #handlers = new Map();

  /** @type {Map<number, {resolve: function, reject: function, timer: *}>} */
  #pending = new Map();

  /** @type {number} */
  #counter = 0;

  /** @type {function(MessageEvent): void} */
  #listener;

  /** @type {{encode: function(string, object): *, decode: function(string, *): ?object}} */
  #codec;

  /**
   * @param {Window} window the own window, messages are received on it
   * @param {function(): ?Window} peer returns the window of the other end
   * @param {string} origin the origin of the other end
   * @param {string} name name of the channel, both ends must use the same
   * @param {{encode: function(string, object): *, decode: function(string, *): ?object}} codec
   *        translates the messages on the wire, see protocol.js
   */
  constructor(window, peer, origin, name, codec = envelopeCodec) {
    if (typeof origin !== 'string' || origin === '' || origin === '*' || origin === 'null') {
      throw new Error(`Invalid origin for frame bridge channel: ${origin}`);
    }
    this.#window = window;
    this.#peer = peer;
    this.#origin = origin;
    this.#name = name;
    this.#codec = codec;
    this.#listener = (event) => this.#receive(event);
    this.#window.addEventListener('message', this.#listener);
  }

  /**
   * @returns {string}
   */
  getOrigin() {
    return this.#origin;
  }

  /**
   * Registers the handler for a message type. The return value of the handler
   * (or the value its promise resolves to) is sent back for requests.
   *
   * @param {string} type
   * @param {function(*, object): *} handler
   * @returns {Channel}
   */
  on(type, handler) {
    this.#handlers.set(type, handler);
    return this;
  }

  /**
   * Sends a message without expecting an answer.
   *
   * @param {string} type
   * @param {*} payload
   */
  send(type, payload) {
    this.#post({ type, payload });
  }

  /**
   * Sends a message and resolves with the answer of the other end.
   *
   * @param {string} type
   * @param {*} payload
   * @param {number} timeout in milliseconds, 0 waits forever
   * @returns {Promise<*>}
   */
  request(type, payload, timeout = 10000) {
    this.#counter += 1;
    const id = this.#counter;
    return new Promise((resolve, reject) => {
      let timer = null;
      if (timeout > 0) {
        timer = setTimeout(() => {
          this.#pending.delete(id);
          reject(new Error(`Frame bridge request '${type}' timed out.`));
        }, timeout);
      }
      this.#pending.set(id, { resolve, reject, timer });
      this.#post({
        type, payload, id,
      });
    });
  }

  destroy() {
    this.#window.removeEventListener('message', this.#listener);
    this.#pending.forEach((pending) => {
      clearTimeout(pending.timer);
      pending.reject(new Error('Frame bridge channel closed.'));
    });
    this.#pending.clear();
    this.#handlers.clear();
  }

  /**
   * @param {{type: string, payload: *, id?: number, re?: number, error?: string}} message
   */
  #post(message) {
    const peer = this.#peer();
    if (peer) {
      peer.postMessage(this.#codec.encode(this.#name, message), this.#origin);
    }
  }

  /**
   * @param {MessageEvent} event
   */
  #receive(event) {
    if (event.origin !== this.#origin || event.source !== this.#peer()) {
      return;
    }
    const message = this.#codec.decode(this.#name, event.data);
    if (message === null) {
      return;
    }

    if (message.re !== undefined) {
      this.#settle(message);
      return;
    }

    const handler = this.#handlers.get(message.type);
    if (message.id === undefined) {
      if (handler) {
        handler(message.payload, message);
      }
      return;
    }

    if (!handler) {
      this.#post({
        type: message.type, payload: null, re: message.id, error: `No handler for '${message.type}'.`,
      });
      return;
    }

    Promise.resolve()
      .then(() => handler(message.payload, message))
      .then(
        (result) => this.#post({ type: message.type, payload: result, re: message.id }),
        (error) => this.#post({
          type: message.type, payload: null, re: message.id, error: String(error?.message ?? error),
        }),
      );
  }

  /**
   * @param {object} message
   */
  #settle(message) {
    const pending = this.#pending.get(message.re);
    if (!pending) {
      return;
    }
    this.#pending.delete(message.re);
    clearTimeout(pending.timer);
    if (message.error !== undefined) {
      pending.reject(new Error(message.error));
    } else {
      pending.resolve(message.payload);
    }
  }
}
