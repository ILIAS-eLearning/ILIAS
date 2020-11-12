/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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