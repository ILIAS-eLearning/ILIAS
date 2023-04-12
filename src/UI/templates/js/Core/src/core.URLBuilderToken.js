export default class URLBuilderToken {
  /**
     * @type {number}
     */
  static TOKEN_LENGTH = 24;

  /**
   * @type {string}
   */
  static SEPARATOR = '_';

  /**
     * @type {string[]}
     */
  #namespace = [];

  /**
     * @type {string}
     */
  #name = '';

  /**
     * @type {string|null}
     */
  #token = null;

  /**
     * @param {string[]} namespace
     * @param {string} name
     * @param {string|null} token
     */
  constructor(namespace, name, token = null) {
    this.#namespace = namespace;
    this.#name = name;
    this.#token = token;
    if (this.#token === null) {
      this.#createToken();
    }
  }

  /**
     * @returns {string}
     */
  get token() {
    return this.#token;
  }

  /**
     * @returns {string}
     */
  getName() {
    return this.#namespace.join(URLBuilderToken.SEPARATOR) + URLBuilderToken.SEPARATOR + this.#name;
  }

  /**
     * @returns {void}
     */
  #createToken() {
    let token = '';
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const charactersLength = characters.length;
    while (token.length < URLBuilderToken.TOKEN_LENGTH) {
      token += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    this.#token = token;
  }
}
