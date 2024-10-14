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
 *********************************************************************/

const diffLeft = (left, right) => Object.keys(left)
      .filter(key => !Reflect.has(right, key))
      .map(key => ({key, value: left[key]}));

export default class WatchList {
  /** @type {object.<string, object>} */
  #list;
  /** @type {Array.<function(object): void>} */
  #onChangeList;

  constructor() {
    this.#list = {};
    this.#onChangeList = [];
  }

  find(key) {
    return this.#list[key];
  }

  has(key) {
    return Reflect.has(this.#list, String(key));
  }

  includes(key) {
    return this.has(key);
  }

  onChange(callback) {
    this.#onChangeList.push(callback);
  }

  add(key, value) {
    key = String(key);
    this.#list[key] = value;
    this.#changed({added: [{key, value}], removed: []});
  }

  remove(key) {
    key = String(key);
    if (!Reflect.has(this.#list, key)) {
      return;
    }
    const value = this.#list[key];
    delete this.#list[key];
    this.#changed({added: [], removed: [{key, value}]});
  }

  setAll(list) {
    const diff = {
      added: diffLeft(list, this.#list),
      removed: diffLeft(this.#list, list),
    };
    this.#list = list;
    this.#changed(diff);
  }

  all() {
    return this.#list;
  }

  #changed(diff) {
    this.#onChangeList.forEach(f => f(diff));
  }
}
