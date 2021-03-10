/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import PageUI from '../components/page/ui/page-ui.js';
import ParagraphUI from '../components/paragraph/ui/paragraph-ui.js';
import MediaUI from '../components/media/ui/media-ui.js';
import TableUI from '../components/table/ui/table-ui.js';
import AutoSave from '../components/paragraph/ui/auto-save.js';

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
   * @param {PageModifer} pageModifer
   */
  constructor(client, dispatcher, actionFactory, model, toolSlate,
              pageModifer) {
    this.uiModel = {};
    this.client = client;
    this.dispatcher = dispatcher;
    this.actionFactory = actionFactory;
    this.model = model;
    this.toolSlate = toolSlate;
    this.pageModifer = pageModifer;

    // @todo we need a ui factory here...
    this.page = new PageUI(
      this.client,
      this.dispatcher,
      this.actionFactory,
      this.model.model("page"),
      this.toolSlate,
      this.pageModifer
    );
    this.paragraph = new ParagraphUI(
      this.client,
      this.dispatcher,
      this.actionFactory,
      this.model.model("page"),
      this.toolSlate,
      this.pageModifer,
      new AutoSave());
    this.media = new MediaUI(
      this.client,
      this.dispatcher,
      this.actionFactory,
      this.model.model("page"),
      this.toolSlate,
      this.pageModifer
    );
    this.table = new TableUI(
      this.client,
      this.dispatcher,
      this.actionFactory,
      this.model.model("page"),
      this.toolSlate,
      this.pageModifer,
      this.paragraph,
      this.model.model("table"),
    );

    this.page.addComponentUI("Paragraph", this.paragraph);
    this.page.addComponentUI("Media", this.media);
    this.page.addComponentUI("Table", this.table);
    this.pageModifer.setPageUI(this.page);
  }

  /**
   * @return {PageUI}
   */
  getPageUI() {
    return this.page;
  }

  //
  // Initialisation
  //

  /**
   */
  init() {
    const ui_all_action = this.actionFactory.page().query().uiAll();
    this.client.sendQuery(ui_all_action).then(result => {
      this.uiModel = result.getPayload();

      // move page component model to model
      console.log(this.uiModel);
      this.model.model("page").setComponentModel(this.uiModel.pcModel);
      this.model.model("page").activatePasting(this.uiModel.pasting);
      this.uiModel.pcModel = null;

      this.toolSlate.init(this.uiModel);
      this.page.init(this.uiModel);
      this.paragraph.init(this.uiModel);
      this.media.init(this.uiModel);
      this.table.init(this.uiModel);
    });
  }

  /**
   */
  reInit() {
    this.page.reInit();
  }
}
