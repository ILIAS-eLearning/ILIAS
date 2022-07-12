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

import PageModelActionHandler from "../components/page/model/page-model-action-handler.js";
import ParagraphModelActionHandler from "../components/paragraph/model/paragraph-model-action-handler.js";
import TableModelActionHandler from "../components/table/model/table-model-action-handler.js";

/**
 * Model action handler
 */
export default class ModelActionHandler {

  /**
   * {PageModel}
   */
  //model;

  /**
   * {PageModelActionHandler}
   */
  //pageModelHandler;

  /**
   *
   * @param {Model} model
   */
  constructor(model) {
    this.model = model;
    this.pageModelHandler = new PageModelActionHandler(
      model.model("page")
    );
    this.paragraphModelHandler = new ParagraphModelActionHandler(
      model.model("page")
    );
    this.tableModelHandler = new TableModelActionHandler(
      model.model("page"),
      model.model("table")
    );
  }


  /**
   * @return {Model}
   */
  getModel() {
    return this.model;
  }

  /**
   * @param {EditorAction} action
   */
  handle(action) {
    this.pageModelHandler.handle(action);
    this.paragraphModelHandler.handle(action);
    this.tableModelHandler.handle(action);
  }
}