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
 *********************************************************************/

const HEARTBEAT_TIMEOUT = 5000;

/**
 * Type like typing not the type.
 * This interface is used to decide what happens when the user is typing or stops typing.
 *
 * @typedef {{release: function(): void, heartbeat: function(): void}} Type
 */

/**
 * This class implements {Type} and is used to notify the given {ServerConnector} whether or not the user is typing.
 * @implements {Type}
 */
export class TypeSelf {
  /** @type {ServerConnector} */
  #serverConnector;
  /** @type {boolean} */
  #isTyping;
  /** @type {function(): void} */
  #reset;

  /**
   * @param {ServerConnector} serverConnector
   */
  constructor(serverConnector) {
    this.#serverConnector = serverConnector;
    this.#isTyping = false;
    this.#reset = () => {};
    window.addEventListener('beforeunload',this.release.bind(this));
  }

  release() {
    this.#reset();
    if (this.#isTyping) {
      this.#serverConnector.userStoppedTyping();
      this.#isTyping = false;
    }
  }

  heartbeat() {
    this.#reset();
    if (!this.#isTyping) {
      this.#serverConnector.userStartedTyping();
      this.#isTyping = true;
    }
    this.#reset = clearTimeout.bind(null, setTimeout(this.release.bind(this), HEARTBEAT_TIMEOUT));
  }
}

/**
 * This class implements {Type} and is used in case nothing should be notified when the user is typing.
 *
 * @implements {Type}
 */
export class TypeNothing {
  release() {}
  heartbeat() {}
}
