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
          this.tableModel.setState(this.tableModel.STATE_DATA);
          break;

        case ACTIONS.SWITCH_EDIT_TABLE:
          this.tableModel.setState(this.tableModel.STATE_TABLE);
          this.tableModel.selectNone();
          break;

        case ACTIONS.SWITCH_FORMAT_CELLS:
          this.tableModel.setState(this.tableModel.STATE_CELLS);
          break;

        case ACTIONS.SWITCH_MERGE_CELLS:
          this.tableModel.setState(this.tableModel.STATE_MERGE);
          break;

        case ACTIONS.SAVE_RETURN:
          this.tableModel.setState(this.tableModel.STATE_TABLE);
          break;

        case ACTIONS.CANCEL_CELL_EDIT:
          this.tableModel.setState(this.tableModel.STATE_TABLE);
          break;

        case ACTIONS.TOGGLE_CELL:
          this.tableModel.toggleCell(parseInt(params.row), parseInt(params.col), params.expand);
          break

        case ACTIONS.TOGGLE_ROW:
          this.tableModel.toggleRow(parseInt(params.nr), params.expand);
          break;

        case ACTIONS.TOGGLE_TABLE:
          this.tableModel.toggleTable(params.expand);
          break;

        case ACTIONS.TOGGLE_COL:
          this.tableModel.toggleCol(parseInt(params.nr), params.expand);
          break;
      }
    }
  }
}