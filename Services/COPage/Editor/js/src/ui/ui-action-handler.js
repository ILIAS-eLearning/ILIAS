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

    const params = action.getParams();
    switch (action.getType()) {

      case "dnd.drag":
        this.ui.hideAddButtons();
        this.ui.showDropareas();
        break;

      case "dnd.drop":
        this.ui.showAddButtons();
        this.ui.hideDropareas();
        break;

      case "create.add":
        // GET ist form action auf ilpageeditorgui
        // POST: array(3) { ["target"]=> array(1) { [0]=> string(0) "" } ["command2_3_1"]=> string(10) "insert_mob" ["cmd"]=> array(1) { ["exec_2_3_1:25d41b9122de4883a5e099ce3571385d"]=> string(2) "Ok" } }
        console.log("create.add");
        console.log(params.ctype);
        if (params.ctype !== "par") {
          client.sendForm(actionFactory.command().copage().createLegacy(params.ctype, params.pcid,
            params.hierid));
        } else {
          // @todo refactor legacy
          editParagraph(params.hierid + ":" + params.pcid, 'insert', false);
        }
        break;
    }
  }
}