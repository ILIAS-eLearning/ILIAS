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
  Q_INIT: "init",

  // command actions (being sent to the server to "change things")
  C_SAVE_TRIGGER_PROPERTIES: "save.trigger.properties",
  C_SAVE_TRIGGER_OVERLAY: "save.trigger.overlay",
  C_SAVE_TRIGGER_POPUP: "save.trigger.popup",
  C_UPLOAD_OVERLAY: "upload.overlay",
  C_DELETE_OVERLAY: "delete.overlay",
  C_SAVE_POPUP: "save.popup",
  C_DELETE_POPUP: "delete.popup",
  C_SAVE_SETTINGS: "save.settings",

  // editor actions (things happening in the editor client side)
  E_ADD_TRIGGER: "add.trigger",
  E_EDIT_TRIGGER: "edit.trigger",
  E_TRIGGER_PROPERTIES: "trigger.properties",
  E_TRIGGER_PROPERTIES_SAVE: "trigger.properties.save",
  E_TRIGGER_SHAPE_CHANGE: "trigger.shape.change",
  E_TRIGGER_OVERLAY_CHANGE: "trigger.overlay.change",
  E_TRIGGER_OVERLAY: "trigger.overlay",
  E_TRIGGER_OVERLAY_ADD: "trigger.add.overlay",
  E_TRIGGER_OVERLAY_SAVE: "trigger.overlay.save",
  E_TRIGGER_POPUP_ADD: "trigger.add.popup",
  E_TRIGGER_POPUP_SAVE: "trigger.save.popup",
  E_OVERLAY_UPLOAD: "overlay.upload",
  E_OVERLAY_DELETE: "overlay.delete",
  E_POPUP_DELETE: "popup.delete",
  E_POPUP_SAVE: "popup.save",
  E_POPUP_RENAME: "popup.rename",
  E_TRIGGER_POPUP: "trigger.popup",
  E_TRIGGER_BACK: "trigger.back",
  E_SWITCH_SETTINGS: "switch.settings",
  E_SWITCH_OVERLAYS: "switch.overlays",
  E_SWITCH_POPUPS: "switch.popups",
  E_SAVE_SETTINGS: "component.save",
  E_COMPONENT_BACK: "component.back",         // component sends back (to main page) request
};
export default ACTIONS;