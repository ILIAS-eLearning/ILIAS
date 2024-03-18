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

export default class Pagination {
  /**
   * @type {HTMLDivElement}
   */
  #component;

  /**
   * @type {JQueryEventDispatcher}
   */
  #eventDispatcher;

  /**
   * @param {string} componentId
   * @param {JQueryEventDispatcher} eventDispatcher
   * @throws {Error} if DOM element is missing
   */
  constructor(componentId, eventDispatcher) {
    this.#eventDispatcher = eventDispatcher;
    this.#component = document.getElementById(componentId);
    if (this.#component === null) {
      throw new Error(`Could not find a Pagination for id '${componentId}'.`);
    }
  }

  /**
   * @param {Event} event
   * @param {array} signalData
   * @param {string} signal
   */
  onInternalSelect(event, signalData, signal) {
    const triggerer = signalData.triggerer[0]; // the shy-button
    const param = triggerer.getAttribute('data-action'); // the pagination-value
    const sigdata = {
      signalData: {
        id: signal,
        event: 'select',
        triggerer: this.#component,
        options: {
          page: param,
        },
      },
    };
    let dd = this.#component.querySelectorAll('.dropdown-toggle'); // the (potential) dropdown

    if (dd.length > 0) {
      dd = dd.at(0);
      // close dropdown and set current value
      dd.parentNode.classList.remove('open');
      dd.childNodes[0].data = `${(parseInt(param, 10) + 1).toString()} `;
    }
    this.#eventDispatcher.dispatch(this.#component, signal, sigdata);
  }
}
