/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

const ACTIONS = {

  // query actions (being sent to the server to "ask for stuff")
  UI_ALL: "ui.all",
  EDIT_FORM: "component.edit.form",

  // command actions (being sent to the server to "change things")
  CREATE_LEGACY: "create.legacy", // calls a legacy creation form for a page component
  EDIT_LEGACY: "edit.legacy",     // calls a legacy edit form for a page component
  MULTI_LEGACY: "multi.legacy",   // performas a multi-selection action the legacy way (send form)
  CUT: "cut",                 // cut and paste
  PASTE: "paste",             // cut and paste
  COPY: "copy",             // copy and paste
  DRAG_DROP: "drag.drop",   // drag and drop (single element)
  FORMAT: "format",   // format paragraphs and sections
  DELETE: "delete",   // delete content
  ACTIVATE: "activate",   // activate content
  INSERT: "insert",   // generic insert component (after showing the generic insert form)
  UPDATE: "update",   // generic insert component (after showing the generic insert form)
  LIST_EDIT_CMD: "list.edit.cmd",       // edit list item

  // editor actions (things happening in the editor client side)
  DND_DRAG: "dnd.drag",           // start dragging
  DND_STOPPED: "dnd.stopped",           // dragging stopped (both on drop and on non-drop area)
  DND_DROP: "dnd.drop",           // dropping
  COMPONENT_INSERT: "component.insert",       // hit add link in add dropdown
  COMPONENT_EDIT: "component.edit",         // hit component for editing, opens form in slate or legacy view
  COMPONENT_SWITCH: "component.switch",         // hit other component of same type while editing
  COMPONENT_CANCEL: "component.cancel",         // components cancel button is pressed
  COMPONENT_SAVE: "component.save",         // components save button is pressed (standard creation form)
  COMPONENT_AFTER_SAVE: "component.saved",         // after componente has been saved
  COMPONENT_UPDATE: "component.update",         // components save button is pressed (editing form)
  COMPONENT_SETTINGS: "component.settings",         // routes to legacy settings -> edit.legacy
  MULTI_TOGGLE: "multi.toggle",   // toggle an element for multi selection
  MULTI_ACTION: "multi.action",   // perform multi action
  MULTI_PASTE: "multi.paste",   // paste multi items
  FORMAT_SAVE: "format.save",             // save selected formats
  FORMAT_CANCEL: "format.cancel",             // cancel format selection
  FORMAT_PARAGRAPH: "format.paragraph",   // select paragraph format
  FORMAT_SECTION: "format.section",       // select section format
  FORMAT_MEDIA: "format.media",       // select section format
  MULTI_DELETE: "multi.delete",       // delete selection
  MULTI_ACTIVATE: "multi.activate",       // activate selection
  SWITCH_SINGLE: "switch.single",       // single mode: click selects single component for editing
  SWITCH_MULTI: "switch.multi",       // multi mode: click selects one or multiple components for multi actions
  PAGE_EDITING: "page.editing",       // return to page editing, e.g. after an cmd has been finished server side
  LIST_EDIT: "list.edit"       // edit list item

};
export default ACTIONS;