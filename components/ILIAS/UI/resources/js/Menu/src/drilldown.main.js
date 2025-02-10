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
 ******************************************************************** */

export default class Drilldown {
  /**
   * @type {Set<function(string)>}
   */
  #engageListeners = new Set();

  /**
   * @type {DrilldownPersistence}
   */
  #persistence;

  /**
   * @type {DrilldownModel}
   */
  #model;

  /**
   * @type {DrilldownMapping}
   */
  #mapping;

  /**
   * @type {string}
   */
  #backSignal;

  /**
   * @param {JQueryEventListener} jqueryEventListener
   * @param {DrilldownPersistence} persistence
   * @param {DrilldownModel} model
   * @param {DrilldownMapping} mapping
   * @param {string} backSignal
   */
  constructor(jqueryEventListener, document, persistence, model, mapping, backSignal) {
    this.#persistence = persistence;
    this.#model = model;
    this.#mapping = mapping;
    this.#backSignal = backSignal;

    jqueryEventListener.on(document, this.#backSignal, () => { this.#upLevel(); });
    this.#mapping.maybeAddFilterHandler(
      (e) => {
        if (e.key !== 'Tab' && e.key !== 'Shift') {
          this.#filter(e);
        }
      },
    );

    this.parseLevels();
    this.engageLevel(this.#persistence.read());
  }

  parseLevels() {
    this.#mapping.parseLevel(
      (headerDisplayElement, parent, leaves, sublist, level) => this.#model
        .addLevel(headerDisplayElement, parent, leaves, sublist, level),
      (index, text) => this.#model.buildLeaf(index, text),
      (levelId) => {
        this.engageLevel(levelId);
      },
    );
  }

  /**
   * @param {function(string)} callback (receives drilldown-level argument)
   */
  removeEngageListener(callback) {
    if (this.#engageListeners.has(callback)) {
      this.#engageListeners.delete(callback);
    }
  }

  /**
   * @param {function(string)} callback
   */
  addEngageListener(callback) {
    if (!this.#engageListeners.has(callback)) {
      this.#engageListeners.add(callback);
    }
  }

  /**
   * Returns the signal which is triggered when the back-nav is clicked.
   * @returns {string}
   */
  getBackSignal() {
    return this.#backSignal;
  }

  /**
   * @param {string} levelId
   * @returns {void}
   */
  engageLevel(levelId) {
    this.#model.engageLevel(levelId);
    this.#apply();
    this.#engageListeners.forEach((callback) => {
      callback(levelId);
    });
  }

  /**
   * @param {Event} e
   * @returns {void}
   */
  #filter(e) {
    this.#model.engageLevel(0);
    this.#model.filter(e);
    this.#mapping.setFiltered(this.#model.getFiltered());
    e.target.focus();
  }

  /**
   * @returns {void}
   */
  #upLevel() {
    this.#model.upLevel();
    this.engageLevel(this.#model.getCurrent().id);
  }

  /**
   * @returns {void}
   */
  #apply() {
    const current = this.#model.getCurrent();
    const parent = this.#model.getParent();
    let level = 2;
    if (current.parent === null) {
      level = 0;
    } else if (current.parent === '0') {
      level = 1;
    }
    this.#mapping.setEngaged(current.id);
    this.#persistence.store(current.id);
    this.#mapping.setHeader(current.headerDisplayElement, parent.headerDisplayElement);
    this.#mapping.setHeaderBacknav(level);
    this.#mapping.correctRightColumnPositionAndHeight(current.id);
  }
}
