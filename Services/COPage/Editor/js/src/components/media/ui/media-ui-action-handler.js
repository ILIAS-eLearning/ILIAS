/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import ACTIONS from "../actions/media-action-types.js";
import MediaUI from "./media-ui.js";
import PAGE_ACTIONS from "../../page/actions/page-action-types.js";

/**
 * Media UI action handler
 */
export default class MediaUIActionHandler {

  /**
   * @type {MediaUI}
   */
  //ui;

  /**
   * @type {ActionFactory}
   */
  //actionFactory;

  /**
   * @type {Dispatcher}
   */
  //dispatcher;

  /**
   * @type {Client}
   */
  //client;

  /**
   * @param {ActionFactory} actionFactory
   * @param {Client} client
   */
  constructor(actionFactory, client) {
    this.actionFactory = actionFactory;
    this.client = client;
    this.ui = null;
    this.dispatcher = null;
  }

  /**
   * @param {ParagraphUI} ui
   */
  setUI(ui) {
    this.ui = ui;
  }

  /**
   * @param {Dispatcher} dispatcher
   */
  setDispatcher(dispatcher) {
    this.dispatcher = dispatcher;
  }

  /**
   * @param {EditorAction} action
   * @param {PageModel} page_model
   */
  handle(action, page_model) {

    const dispatcher = this.dispatcher;
    const actionFactory = this.actionFactory;
    const client = this.client;
    let form_sent = false;

    const params = action.getParams();

    // page actions
    if (action.getComponent() === "Page" && page_model.getCurrentPCName() === "MediaObject") {


      switch (action.getType()) {

        case PAGE_ACTIONS.COMPONENT_INSERT:
          this.ui.initCreationDialog();
          break;

      }
    }

    if (action.getComponent() === "Media") {
      switch (action.getType()) {

        case ACTIONS.SELECT_POOL:
          this.ui.handlePoolSelection(params.url, params.pcid);
          break;

        case ACTIONS.OPEN_CLIPBOARD:
          this.ui.handleOpenClipboard(params.url, params.pcid);
          break;
      }
    }
  }
}