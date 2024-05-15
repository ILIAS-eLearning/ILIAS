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

export default class DrilldownModel {
  /**
   * @type {object}
   */
  #level = {
    id: null,
    parent: null,
    engaged: false,
    headerDisplayElement: '',
    leaves: [],
  };

  /**
   * @type {object}
   */
  #leaf = {
    index: null,
    text: null,
    filtered: false,
  };

  /**
   * @type {this.#level[]}
   */
  #data = [];

  /**
   * @param {string} levelId
   * @param {HTMLButtonElement} headerDisplayElement
   * @param {int} parent
   * @param {array} leaves
   * @returns {this.#level}
   */
  #buildLevel(levelId, headerDisplayElement, parent, leaves) {
    const level = { ...this.#level };
    level.id = levelId;
    level.parent = parent;
    level.headerDisplayElement = headerDisplayElement;
    level.leaves = leaves;
    return level;
  }

  buildLeaf(index, text) {
    const leaf = { ...this.#leaf };
    leaf.index = index;
    leaf.text = text;
    return leaf;
  }

  /**
   * @param {HTMLButtonElement} headerDisplayElement
   * @param {int} parent
   * @param {array} leaves
   * @returns {this.#level}
   */
  addLevel(headerDisplayElement, parent, leaves) {
    const levelId = this.#data.length.toString();
    const level = this.#buildLevel(levelId, headerDisplayElement, parent, leaves);
    this.#data[level.id] = level;
    return this.#data[level.id];
  }

  /**
   * @param  {String} levelId
   */
  engageLevel(levelId) {
    this.#data.forEach(
      (level) => {
        const levelRef = level;
        levelRef.engaged = false;
        if (level.id === levelId) {
          levelRef.engaged = true;
        }
      },
    );
  }

  /**
   * @returns {this.#level}
   */
  getCurrent() {
    const cur = this.#data.find(
      (level) => level.engaged,
    );
    if (cur !== undefined) {
      return cur;
    }
    return this.#data[0];
  }

  /**
   * @returns {integer}
   */
  getParent() {
    const cur = this.getCurrent();
    if (cur.parent) {
      return this.#data[cur.parent];
    }
    return {};
  }

  /**
   * @return {void}
   */
  upLevel() {
    const cur = this.getCurrent();
    if (cur.parent) {
      this.engageLevel(this.#data[cur.parent].id);
    }
  }

  /**
   * @param {integer} levelId
   * @return {void}
   */
  #removeFilteredRecursive(levelId) {
    if (levelId !== null && levelId !== 0) {
      return;
    }

    this.#data[levelId].filtered = false;
    if (this.#data[levelId].parent !== null && this.#data[levelId].parent !== 0) {
      this.#removeFilteredRecursive(this.#data[levelId].parent);
    }
  }

  /**
   * @param {Event} e
   * @returns {void}
   */
  filter(e) {
    const value = e.target.value.toLowerCase();
    this.#data.forEach(
      (level) => {
        const levelRef = level;
        levelRef.leaves.forEach(
          (leaf) => {
            const leafRef = leaf;
            if (value === '') {
              leafRef.filtered = false;
              return;
            }
            if (leafRef.text.toLowerCase().includes(value) === false) {
              leafRef.filtered = true;
              return;
            }
            leafRef.filtered = false;
          },
        );
      },
    );
  }

  /**
   * @returns {this.#level[]}
   */
  getFiltered() {
    const filtered = [];
    this.#data.forEach(
      (level) => {
        const leaves = level.leaves.filter(
          (leaf) => leaf.filtered,
        );
        if (leaves.length > 0) {
          const clone = this.#buildLevel(
            level.id,
            level.headerDisplayElement,
            level.parent,
            [...leaves],
          );
          filtered.push(clone);
        }
      },
    );
    return filtered;
  }
}
