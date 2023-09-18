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

import PageUI from '../components/page/ui/page-ui.js';
import ParagraphUI from '../components/paragraph/ui/paragraph-ui.js';
import MediaUI from '../components/media/ui/media-ui.js';
import TableUI from '../components/table/ui/table-ui.js';
import AutoSave from '../components/paragraph/ui/auto-save.js';
import PlaceHolderUI from '../components/placeholder/ui/placeholder-ui.js';

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
    this.debug = true;

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
    this.placeholder = new PlaceHolderUI(
        this.client,
        this.dispatcher,
        this.actionFactory,
        this.model.model("page"),
        this.toolSlate,
        this.pageModifer
    );


    this.page.addComponentUI("Paragraph", this.paragraph);
    this.page.addComponentUI("Media", this.media);
    this.page.addComponentUI("Table", this.table);
    this.page.addComponentUI("PlaceHolder", this.placeholder);
    this.pageModifer.setPageUI(this.page);
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
    const ui_all_action = this.actionFactory.page().query().uiAll();
    this.client.sendQuery(ui_all_action).then(result => {
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
      }
    });
  }

  /**
   */
  reInit() {
    this.page.reInit();
  }
}
