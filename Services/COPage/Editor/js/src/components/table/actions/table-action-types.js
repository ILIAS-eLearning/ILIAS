/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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