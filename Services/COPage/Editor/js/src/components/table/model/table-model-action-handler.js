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
          break;
      }
    }
  }
}