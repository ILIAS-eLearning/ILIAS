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

export default class Drilldown {
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
   * @param {jQuery} $
   * @param {DrilldownPersistence} persistence
   * @param {DrilldownModel} model
   * @param {DrilldownMapping} mapping
   * @param {string} backSignal
   */
  constructor($, persistence, model, mapping, backSignal) {
    this.#persistence = persistence;
    this.#model = model;
    this.#mapping = mapping;

    $(document).on(backSignal, () => { this.#upLevel(); });
    this.#mapping.setFilterHandler(
      (e) => {
        this.#filter(e);
      },
    );
    this.#mapping.parseLevel(
      (headerDisplayElement, parent, leaves) => this.#model
        .addLevel(headerDisplayElement, parent, leaves),
      (index, text) => this.#model.buildLeaf(index, text),
      (levelId) => {
        this.#engageLevel(levelId);
      },
    );

    this.#engageLevel(this.#persistence.read());
  }

  /**
   *
   * @param {integer} levelId
   * @returns {void}
   */
  #engageLevel(levelId) {
    this.#model.engageLevel(levelId);
    this.#apply();
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
    this.#apply();
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
