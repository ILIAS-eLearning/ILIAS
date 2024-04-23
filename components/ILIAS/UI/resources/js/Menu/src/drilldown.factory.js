import $ from 'jquery';
import Drilldown from './drilldown.main';
import DrilldownPersistance from './drilldown.persistence';
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
   * @param {DOMDocument} document
   * @param {jQuery} jQuery
   */
  constructor(document, jQuery) {
    this.#document = document;
    this.#jQuery = jQuery;
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
      new DrilldownPersistance(new il.Utilities.CookieStorage(persistanceId)),
      new DrilldownModel(),
      new DrilldownMapping(this.#document, drilldownId),
      backSignal,
    );
  }

  /**
   * @param {string} drilldownId
   * @param {Drilldown|null}
   */
  get(drilldownId) {
    return this.#instances[drilldownId] ?? null;
  }
}
