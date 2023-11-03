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

import IIMUIActionHandler from './iim-ui-action-handler.js';

/**
 * UI action handler
 */
export default class UIActionHandler {

  /**
   * @type {UI}
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
   * @type {PageUIActionHandler}
   */
  //pageActionHandler;

  /**
   * @type {ParagraphUIActionHandler}
   */
  //paragraphActionHandler;

  /**
   * @type {MediaUIActionHandler}
   */
  //mediaActionHandler;

  /**
   * @type {TableUIActionHandler}
   */
  //tableActionHandler;

  /**
   * @param {ActionFactory} actionFactory
   * @param {Client} client
   */
  constructor(actionFactory, client) {
    this.ui = null;
    this.dispatcher = null;
    this.actionFactory = actionFactory;
    this.client = client;
    this.iimActionHandler = new IIMUIActionHandler(
      this.actionFactory,
      this.client
    );
  }

  /**
   * @param {UI} ui
   */
  setUI(ui) {
    this.ui = ui;
    this.iimActionHandler.setUI(this.ui.iim);
  }

  /**
   * @param {DISPATCHER} dispatcher
   */
  setDispatcher(dispatcher) {
    this.dispatcher = dispatcher;
    this.iimActionHandler.setDispatcher(dispatcher);
  }

  /**
   * @param {EditorAction} action
   * @param {Model} model
   */
  handle(action, model) {
    this.iimActionHandler.handle(action, model);
  }
}