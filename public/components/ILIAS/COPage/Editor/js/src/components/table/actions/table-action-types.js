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
  SET_PROPERTIES: "set.properties",   // cell properties
  CMD_TOGGLE_MERGE: "toggle.merge",

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
  AUTO_SAVE: "save.auto",
  SWITCH_EDIT_TABLE: "switch.edit.table",
  SWITCH_FORMAT_CELLS: "switch.format.cells",
  SWITCH_MERGE_CELLS: "switch.merge.cells",
  TOGGLE_ROW: "toggle.row",
  TOGGLE_COL: "toggle.col",
  TOGGLE_CELL: "toggle.cell",
  TOGGLE_TABLE: "toggle.table",
  PROPERTIES_SET: "properties.set",   // cell properties
  TOGGLE_MERGE: "toggle.merge",
  CANCEL_CELL_EDIT: "cancel.cell.edit"    // cancel cell (paragraph) editing
};
export default ACTIONS;