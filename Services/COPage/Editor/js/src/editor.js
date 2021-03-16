/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import UI from "./ui/ui.js";
import Controller from "./controller.js";
import Client from "./client/client.js";
import ActionFactory from "./actions/action-factory.js";
import ResponseFactory from "./client/response/response-factory.js";
import Model from "./model/model.js";
import Dispatcher from "./dispatcher.js";
import ModelActionHandler from "./model/model-action-handler.js";
import UIActionHandler from "./ui/ui-action-handler.js";
import ToolSlate from "./ui/tool-slate.js";
import PageModifier from "./ui/page-modifier.js";

/**
 * Editor (mainly sets up dependency tree)
 */
export default (function ($, il) {

  /**
   * @type {Controller}
   */
  let controller;

  /**
   * @param {string} endpoint
   * @param {string} form_action
   */
  function init(endpoint, form_action) {

    // action factory (used to invoke actions from the ui)
    const actionFactory = new ActionFactory();

    // action factory (used to invoke actions from the ui)
    const responseFactory = new ResponseFactory();

    // client (used to send/get data to server)
    const client = new Client(endpoint, endpoint, form_action, responseFactory);

    // model
    const model = new Model();

    // action handler and dispatcher
    const modelActionHandler = new ModelActionHandler(model);
    const uiActionHandler = new UIActionHandler(actionFactory, client);
    const dispatcher = new Dispatcher(modelActionHandler, uiActionHandler);

    // ui
    const toolSlate = new ToolSlate();
    const pageModifier = new PageModifier(toolSlate);
    const ui = new UI(client, dispatcher, actionFactory, model, toolSlate, pageModifier);

    client.setDefaultErrorHandler((error) => {
      pageModifier.displayError(error);
    });

    // remaining dependecies for ui action handler
    uiActionHandler.setUI(ui);
    uiActionHandler.setDispatcher(dispatcher);

    // main controller
    controller = new Controller(ui);
    controller.init();
  }

  /**
   * This is legacy handling, needs to be moved to action/dispatcher
   */
  function reInitUI() {
    controller.reInit();
  }

  return {
    init,
    reInitUI
  };

})($, il);