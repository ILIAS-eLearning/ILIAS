/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * UI action handler
 */
export default class UIActionHandler {

  /**
   * @type {UI}
   */
  ui;

  /**
   * @type {ActionFactory}
   */
  actionFactory;

  /**
   * @type {Dispatcher}
   */
  dispatcher;

  /**
   * @type {Client}
   */
  client;

  /**
   * @param {ActionFactory} actionFactory
   * @param {Client} client
   */
  constructor(actionFactory, client) {
    this.actionFactory = actionFactory;
    this.client = client;
  }

  /**
   * @param {UI} ui
   */
  setUI(ui) {
    this.ui = ui;
  }

  /**
   * @param {DISPATCHER} dispatcher
   */
  setDispatcher(dispatcher) {
    this.dispatcher = dispatcher;
  }

  /**
   * @param {EditorAction} action
   * @param {Model} model
   */
  handle(action, model) {

    const dispatcher = this.dispatcher;
    const actionFactory = this.actionFactory;
    const client = this.client;
    let form_sent = false;

    const params = action.getParams();
    switch (action.getType()) {

      case "dnd.drag":
        //this.ui.hideAddButtons();
        //this.ui.showDropareas();
        break;

      case "dnd.drop":
        //this.ui.showAddButtons();
        //this.ui.hideDropareas();
        break;

      case "create.add":
        if (params.ctype !== "par") {
          client.sendForm(actionFactory.command().copage().createLegacy(params.ctype, params.pcid,
            params.hierid));
          form_sent = true;
        } else {
          // @todo refactor legacy
          editParagraph(params.hierid + ":" + params.pcid, 'insert', false);
        }
        break;

      case "multi.toggle":
        this.ui.highlightSelected(model.getSelected());
        break;

      case "multi.action":
        let type = params.type;

        // @todo refactor legacy
        if (["delete", "cut", "copy", "characteristic", "activate"].includes(type)) {
          client.sendForm(actionFactory.command().copage().multiLegacy(type,
            Array.from(model.getSelected())));
          form_sent = true;
        }
        if (["all", "none"].includes(type)) {
          this.ui.highlightSelected(model.getSelected());
        }
        break;
    }


    // if we sent a (legacy) form, deactivate everything
    if (form_sent === true) {
      this.ui.showPageHelp();
      this.ui.hideAddButtons();
      this.ui.hideDropareas();
      this.ui.disableDragDrop();
    } else {

      console.log(model.getState());

      switch (model.getState()) {
        case model.STATE_PAGE:
          this.ui.showPageHelp();
          this.ui.showAddButtons();
          this.ui.hideDropareas();
          this.ui.enableDragDrop();
          break;

        case model.STATE_MULTI_ACTION:
          this.ui.showMultiButtons();
          this.ui.hideAddButtons();
          this.ui.hideDropareas();
          this.ui.disableDragDrop();
          break;

        case model.STATE_DRAG_DROP:
          this.ui.showPageHelp();
          this.ui.hideAddButtons();
          this.ui.showDropareas();
          break;

        case model.STATE_COMPONENT:
          this.ui.showPageHelp();
          this.ui.hideAddButtons();
          this.ui.hideDropareas();
          this.ui.disableDragDrop();
          break;
      }
    }
  }
}