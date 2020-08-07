/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * action queue
 */
export default class ActionQueue {

  actions = [];

  constructor() {
  }

  /**
   * Push action to queue
   * @param {Action} action
   */
  push(action) {
    this.actions.push(actions);
  }

  /**
   * Pop action from queue
   * @returns {Action}
   */
  pop() {
    return this.actions.shift();
  }

}