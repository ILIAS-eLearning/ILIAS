/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Controller (handles editor initialisation process)
 */
export default class Model {

  STATE_PAGE = "page";                  // page editing
  STATE_DRAG_DROP = "drag_drop";        // drag drop
  STATE_COMPONENT = "component";   // component editing (in slate)

  /**
   *
   * @type {*[]}
   */
  states = [];

  /**
   * @type {Object}
   */
  model = {
    state: this.STATE_PAGE
  };

  constructor() {
    this.states = [this.STATE_PAGE, this.STATE_DRAG_DROP, this.STATE_COMPONENT];
  }

  /**
   * @param {string} state
   */
  setState(state) {
    if (this.states.includes(state)) {
      this.model.state = state;
    }
  }

  /**
   * @return {string}
   */
  getState() {
      return this.model.state;
  }

}