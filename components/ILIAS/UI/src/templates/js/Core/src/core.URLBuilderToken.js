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
 ********************************************************************
 */

const URLBuilderTokenSeparator = '_';
const URLBuilderTokenLength = 24;

export default class URLBuilderToken {
  /**
     * @type {string[]}
     */
  #namespace = [];

  /**
     * @type {string}
     */
  #parameterName = '';

  /**
     * @type {string|null}
     */
  #token = null;

  /**
   * @type {string}
   */
  #name = '';

  /**
     * @param {string[]} namespace
     * @param {string} parameterName
     * @param {string|null} token
     */
  constructor(namespace, parameterName, token = null) {
    this.#namespace = namespace;
    this.#parameterName = parameterName;
    this.#token = token;
    if (this.#token === null) {
      this.#token = URLBuilderToken.createToken();
    }
    this.#name = this.#namespace.join(URLBuilderTokenSeparator) + URLBuilderTokenSeparator;
    this.#name += this.#parameterName;
  }

  /**
     * @returns {string|null}
     */
  getToken() {
    return this.#token;
  }

  /**
     * @returns {string}
     */
  getName() {
    return this.#name;
  }

  /**
     * @returns {string}
     */
  static createToken() {
    let token = '';
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const charactersLength = characters.length;
    while (token.length < URLBuilderTokenLength) {
      token += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return token;
  }
}
