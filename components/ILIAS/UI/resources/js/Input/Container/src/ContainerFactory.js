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

import Container from './Container.js';
import createFields from './Field/createFields.js';

export default class ContainerFactory {
  /** @type {Map<string, Container>} */
  #instances = new Map();

  /** @type {HTMLDocument} */
  #document;

  /**
   * @param {HTMLDocument} document
   */
  constructor(document) {
    this.#document = document;
  }

  /**
   * @param {string} componentId
   * @return {Container}
   */
  createContainer(componentId) {
    if (this.#instances.has(componentId)) {
      throw new Error(`Container with id '${componentId}' has already been registered.`);
    }
    const containerElement = this.#document.getElementById(componentId);
    if (containerElement === null) {
      throw new Error(`Could not find HTMLFormElement for container '${componentId}'.`);
    }
    const container = new Container(createFields(containerElement));
    this.#instances.set(componentId, container);
    return container;
  }

  /**
   * @param {string} componentId
   * @return {Container|null}
   */
  get(componentId) {
    if (this.#instances.has(componentId)) {
      return this.#instances.get(componentId);
    }
    return null;
  }

  /**
   * @returns {Container[]}
   */
  getAll() {
    return Array.from(this.#instances);
  }
}
