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

import Container from './container.class.js';

export default class ContainerFactory {
  /**
   * @type {Array<string, Container>}
   */
  #instances = [];

  /**
   * @type {Object<string, class>}
   */
  #transforms;

  /**
   * @param {Object<string,Class>} transforms
   */
  constructor(transforms) {
    this.#transforms = transforms;
  }

  /**
   * @param {string} componentId
   * @return {void}
   */
  init(componentId) {
    const search = `#${componentId}`;
    const component = document.querySelector(search);
    if (this.#instances[componentId] !== undefined) {
      throw new Error(`Container with id '${componentId}' has already been registered.`);
    }
    this.#instances[componentId] = new Container(this.#transforms, component);
  }

  /**
   * @param {string} componentId
   * @return {Container}
   */
  get(componentId) {
    return this.#instances[componentId];
  }

  getAll() {
    return this.#instances;
  }

  first() {
    return this.#instances[Object.keys(this.#instances).shift()];
  }
}
