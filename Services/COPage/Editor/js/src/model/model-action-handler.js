/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Model action handler
 */
export default class ModelActionHandler {

  /**
   * {Model}
   */
  model;

  /**
   *
   * @param {Model} model
   */
  constructor(model) {
    this.model = model;
  }


  /**
   * @return {Model}
   */
  getModel() {
    return this.model;
  }

  /**
   * @param {EditorAction} action
   */
  handle(action) {
    switch (action.getType()) {

      case "dnd-drag":
        this.model.setState(this.model.STATE_DRAG_DROP);
        break;

      case "dnd-drop":
        this.model.setState(this.model.STATE_PAGE);
        break;
    }
  }
}