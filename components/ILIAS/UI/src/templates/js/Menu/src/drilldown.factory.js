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
   * @param {DOMDocument} document
   */
  construct(document) {
    this.#document = document;
  }

  /**
   * @param {string} drilldownId
   * @param {string} backSignal
   * @param {string} persistanceId
   * @return {void}
   * @throws {Error} if the input was already initialized.
   */
  init(drilldownId, backSignal, persistanceId) {
    if (undefined !== this.#instances[drilldownId]) {
      throw new Error(`Drilldown with id '${drilldownId}' has already been initialized.`);
    }

    this.#instances[drilldownId] = new Drilldown(
      $,
      new DrilldownPersistance(new il.Utilities.CookieStorage(persistanceId)),
      new DrilldownModel(),
      new DrilldownMapping(document, drilldownId),
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
