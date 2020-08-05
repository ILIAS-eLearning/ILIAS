/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Editor action dispatcher
 */
export default class Dispatcher {

  constructor(modelActionHandler, uiActionHandler) {
    this.modelActionHandler = modelActionHandler;
    this.uiActionHandler = uiActionHandler;
  }

  /**
   * @param {EditorAction} action
   */
  dispatch(action) {
    console.log("dispatch " + action.getType());
    console.log(action.getParams());
    this.modelActionHandler.handle(action);
    this.uiActionHandler.handle(action, this.modelActionHandler.getModel());
  }
}