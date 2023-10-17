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

import IIMUI from './iim-ui.js';
import ActionFactory from '../actions/action-factory.js';
import IIMUIModifier from "./iim-ui-modifier.js";

/**
 * editor ui
 */
export default class UI {

  /**
   * UI model
   * @type {Object}
   */
  //uiModel = {};

  /**
   * Model
   * @type {Model}
   */
  //model = {};

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
   * @type {PageUI}
   */
  //page;

  /**
   * @type {ParagraphUI}
   */
  //paragraph;

  /**
   * @type {MediaUI}
   */
  //media;

  /**
   * @type {ToolSlate}
   */
  //toolSlate;

  /**
   * @type {pageModifier}
   */
  //pageModifier;

  /**
   * @param {Client} client
   * @param {Dispatcher} dispatcher
   * @param {ActionFactory} actionFactory
   * @param {Model} model
   * @param {ToolSlate} toolSlate
   * @param {IIMUIModifier} IIMUIModifier
   */
  constructor(client, dispatcher, actionFactory, iimModel, toolSlate,
              uiModifier) {
    this.uiModel = {};
    this.client = client;
    this.dispatcher = dispatcher;
    this.actionFactory = actionFactory;
    this.iimModel = iimModel;
    this.toolSlate = toolSlate;
    this.uiModifier = uiModifier;
    this.debug = true;

    this.iim = new IIMUI(
      this.client,
      this.dispatcher,
      this.actionFactory,
      iimModel,
      this.uiModel,
      this.toolSlate,
      this.uiModifier
    );

    /*
    this.page.addComponentUI("Paragraph", this.paragraph);
    this.page.addComponentUI("Media", this.media);
    this.page.addComponentUI("Table", this.table);
    this.page.addComponentUI("PlaceHolder", this.placeholder);
    this.pageModifer.setPageUI(this.page);*/
  }

  /**
   * @return {PageUI}
   */
  getPageUI() {
    return this.page;
  }

  /**
   * @param message
   */
  log(message) {
    if (this.debug) {
      console.log(message);
    }
  }

  //
  // Initialisation
  //

  /**
   */
  init(after_init) {
    const ui_all_action = this.actionFactory.interactiveImage().query().init();
    this.client.sendQuery(ui_all_action).then(result => {

      const p = result.getPayload();
      console.log("INIT PAYLOAD");
      console.log(p);
      this.iimModel.initModel(p.iimModel);
      this.uiModel = p.uiModel;
      this.uiModifier.setUIModel(p.uiModel);
      this.iim.init(this.uiModel);
      /*
      this.uiModel = result.getPayload();

      // move page component model to model
      this.log("ui.js, init, uiModel:");
      this.log(this.uiModel);
      this.log("ui.js, init, pcModel:");
      this.log(this.uiModel.pcModel);
      this.model.model("page").setComponentModel(this.uiModel.pcModel);
      this.model.model("page").activatePasting(this.uiModel.pasting);
      this.uiModel.pcModel = null;

      this.toolSlate.init(this.uiModel);
      this.page.init(this.uiModel);
      this.paragraph.init(this.uiModel);
      this.media.init(this.uiModel);
      this.table.init(this.uiModel);
      this.placeholder.init(this.uiModel);
      if (after_init) {
        after_init();
      }*/
    });
  }

  /**
   */
  reInit() {
    this.iim.reInit();
  }
}
