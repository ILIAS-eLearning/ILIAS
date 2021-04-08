import ACTIONS from "../actions/media-action-types.js";
import PAGE_ACTIONS from "../../page/actions/page-action-types.js";

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * media ui
 */
export default class MediaUI {


  /**
   * @type {boolean}
   */
  //debug = true;

  /**
   * Model
   * @type {PageModel}
   */
  //page_model = {};

  /**
   * UI model
   * @type {Object}
   */
  //uiModel = {};

  /**
   * @type {Client}
   */
  //client;

  /**
   * @type {Dispatcher}
   */
  //dispatcher;

  /**
   * @type {ActionFactory}
   */
  //actionFactory;

  /**
   * @type {ToolSlate}
   */
  //toolSlate;

  /**
   * @type {pageModifier}
   */
//  pageModifier;


  /**
   * @param {Client} client
   * @param {Dispatcher} dispatcher
   * @param {ActionFactory} actionFactory
   * @param {PageModel} page_model
   * @param {ToolSlate} toolSlate
   * @param {PageModifier} pageModifier
   */
  constructor(client, dispatcher, actionFactory, page_model, toolSlate, pageModifier) {
    this.debug = true;
    this.client = client;
    this.dispatcher = dispatcher;
    this.actionFactory = actionFactory;
    this.page_model = page_model;
    this.toolSlate = toolSlate;
    this.pageModifier = pageModifier;
    this.uiModel = {};
  }

  //
  // Initialisation
  //

  /**
   * @param message
   */
  log(message) {
    if (this.debug) {
      console.log(message);
    }
  }


  /**
   */
  init(uiModel) {
    this.log("media-ui.init");

    const action = this.actionFactory;
    const dispatch = this.dispatcher;

    this.uiModel = uiModel;
    let t = this;
  }

  /**
   */
  reInit() {
  }

  initCreationDialog() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    document.querySelectorAll("[data-copg-ed-type='media-action']").forEach((el) => {
      const actionType = el.dataset.copgEdAction;
      let url;

      switch (actionType) {

        case ACTIONS.SELECT_POOL:
          url = el.dataset.copgEdParUrl;
          el.addEventListener("click", (event) => {
            dispatch.dispatch(action.media().editor().selectPool(url, this.page_model.getCurrentInsertPCId()));
          });

        case ACTIONS.OPEN_CLIPBOARD:
          url = el.dataset.copgEdParUrl;
          el.addEventListener("click", (event) => {
            dispatch.dispatch(action.media().editor().openClipboard(url, this.page_model.getCurrentInsertPCId()));
          });

      }
    });
  }

  handlePoolSelection(url, pcid) {
    this.pageModifier.redirect(url + "&pcid=" + pcid);
  }

  handleOpenClipboard(url, pcid) {
    this.pageModifier.redirect(url + "&pcid=" + pcid);
  }

}
