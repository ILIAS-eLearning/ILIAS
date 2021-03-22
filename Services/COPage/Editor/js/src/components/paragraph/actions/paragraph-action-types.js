/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

const ACTIONS = {

  // query actions (being sent to the server to "ask for stuff")

  // command actions (being sent to the server to "change things")
  INSERT: "insert",                        // inserts paragraph
  UPDATE: "update",                        // update paragraph
  UPDATE_AUTO: "update.auto",              // auto update paragraph
  INSERT_AUTO: "insert.auto",              // auto insert paragraph
  SPLIT: "split",              // split paragraph
  CMD_SECTION_CLASS: "cmd.sec.class",    // section format
  CMD_MERGE_PREVIOUS: "cmd.merge.previous",    // merge with previous paragraph
  CMD_CANCEL: "cmd.cancel",    // cancel actions

  // editor actions (things happening in the editor client side)
  PARAGRAPH_CLASS: "par.class",    // paragraph class
  SELECTION_FORMAT: "selection.format",    // format character
  SELECTION_REMOVE_FORMAT: "selection.removeFormat",
  SELECTION_KEYWORD: "selection.keyword",
  SELECTION_TEX: "selection.tex",
  SELECTION_ANCHOR: "selection.anchor",
  SELECTION_FN: "selection.fn",
  LIST_BULLET: "list.bullet",
  LIST_NUMBER: "list.number",
  LIST_OUTDENT: "list.outdent",
  LIST_INDENT: "list.indent",
  LINK_WIKI_SELECTION: "link.wikiSelection",
  LINK_WIKI: "link.wiki",
  LINK_INTERNAL: "link.internal",
  LINK_EXTERNAL: "link.external",
  LINK_USER: "link.user",
  SAVE_RETURN: "save.return",
  AUTO_SAVE: "save.auto",
  AUTO_INSERT_POST: "post.insert.auto",
  SPLIT_POST: "post.split",
  SPLIT_PARAGRAPH: "par.split",
  MERGE_PREVIOUS: "merge.previous",
  SECTION_CLASS: "sec.class"    // section format

};
export default ACTIONS;