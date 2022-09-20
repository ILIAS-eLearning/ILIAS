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

const ACTIONS = {

  // query actions (being sent to the server to "ask for stuff")

  // command actions (being sent to the server to "change things")
  UPDATE_DATA: "update.data",
  MODIFY_TABLE: "modify.table",

  // editor actions (things happening in the editor client side)
  COL_AFTER: "col.after",
  COL_BEFORE: "col.before",
  COL_LEFT: "col.left",
  COL_RIGHT: "col.right",
  COL_DELETE: "col.delete",
  ROW_AFTER: "row.after",
  ROW_BEFORE: "row.before",
  ROW_UP: "row.up",
  ROW_DOWN: "row.down",
  ROW_DELETE: "row.delete",
  EDIT_CELL: "edit.cell",    // edit cell
  SAVE_RETURN: "save.return",
  AUTO_SAVE: "save.auto"

};
export default ACTIONS;