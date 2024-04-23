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

import Drilldown from './drilldown.main';
import DrilldownPersistence from './drilldown.persistence';
import DrilldownModel from './drilldown.model';
import DrilldownMapping from './drilldown.mapping';

export default class DrilldownFactory {
  /**
   * @type {Array<string, Drilldown>}
   */
  #instances = [];

  /**
   * @type {DOMDocument}
   */
  #document;

  /**
   * @type {jQuery}
   */
  #jQuery;

  /**
   * @type {object}
   */
  #il;

  /**
   * @param {DOMDocument} document
   * @param {jQuery} jQuery
   * @param {object} il
   */
  constructor(document, jQuery, il) {
    this.#document = document;
    this.#jQuery = jQuery;
    this.#il = il;
  }

  /**
   * @param {string} drilldownId
   * @param {string} backSignal
   * @param {string} persistanceId
   * @return {void}
   * @throws {Error} if the input was already initialized.
   */
  init(drilldownId, backSignal, persistanceId) {
    if (this.#instances[drilldownId] !== undefined) {
      throw new Error(`Drilldown with id '${drilldownId}' has already been initialized.`);
    }

    if (this.#document.getElementById(drilldownId) === null) {
      return;
    }

    this.#instances[drilldownId] = new Drilldown(
      this.#jQuery,
      new DrilldownPersistence(new this.#il.Utilities.CookieStorage(persistanceId)),
      new DrilldownModel(),
      new DrilldownMapping(this.#document, drilldownId),
      backSignal,
    );
  }
}
