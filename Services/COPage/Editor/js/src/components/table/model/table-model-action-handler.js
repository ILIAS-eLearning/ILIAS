/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import ACTIONS from "../actions/table-action-types.js";

/**
 * Table model action handler
 */
export default class TableModelActionHandler {

  /**
   * {PageModel}
   */
  //pageModel;

  //tableModel;

  /**
   *
   * @param {PageModel} pageModel
   * @param {TableModel} tableModel
   */
  constructor(pageModel, tableModel) {
    this.pageModel = pageModel;
    this.tableModel = tableModel;
  }


  /**
   * @param {EditorAction} action
   */
  handle(action) {

    const params = action.getParams();

    if (action.getComponent() === "Table") {
      switch (action.getType()) {

        case ACTIONS.EDIT_CELL:
          this.pageModel.setCurrentPageComponent("Table", params.tablePcid, params.tableHierid);
          this.pageModel.setState(this.pageModel.STATE_COMPONENT);
          this.tableModel.setCurrentCell(parseInt(params.row), parseInt(params.column));
          break;
      }
    }
  }
}