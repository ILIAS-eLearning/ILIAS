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

import Action from "../../actions/action.js";

/**
 * Action queue
 */
export default class CommandQueue {

  constructor() {
    this.queue = [];
    this.pending = false;
  }

  count() {
    return this.queue.length;
  }

  push(promise) {
    return new Promise((resolve, reject) => {
      this.queue.push({
        promise,
        resolve,
        reject,
      });
      this.shift();
    });
  }

  shift() {
    if (this.pending) {
      return false;
    }
    const item = this.queue.shift();
    if (!item) {
      return false;
    }
    try {
      this.pending = true;
      item.promise()
      .then((value) => {
        this.pending = false;
        item.resolve(value);
        this.shift();
      })
      .catch(err => {
        this.pending = false;
        item.reject(err);
        this.shift();
      })
    } catch (err) {
      this.pending = false;
      item.reject(err);
      this.shift();
    }
    return true;
  }

}