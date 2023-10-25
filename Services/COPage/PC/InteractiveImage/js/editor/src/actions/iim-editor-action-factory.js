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

import ACTIONS from "./iim-action-types.js";

/**
 * COPage action factory
 *
 */
export default class IIMEditorActionFactory {

  /**
   * @type {EditorActionFactory}
   */
  //editorActionFactory;

  /**
   *
   * @param {EditorActionFactory} editorActionFactory
   */
  constructor(editorActionFactory) {
    this.COMPONENT = "InteractiveImage";
    this.editorActionFactory = editorActionFactory;
  }

  componentBack() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_COMPONENT_BACK, {});
  }

  /**
   * @returns {EditorAction}
   */
  addTrigger() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_ADD_TRIGGER, {});
  }

  editTrigger(nr) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_EDIT_TRIGGER, {
      triggerNr : nr
    });
  }

  /**
   * @returns {EditorAction}
   */
  triggerProperties() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_TRIGGER_PROPERTIES, {});
  }

  /**
   * @returns {EditorAction}
   */
  triggerOverlay() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_TRIGGER_OVERLAY, {});
  }

  /**
   * @returns {EditorAction}
   */
  triggerPopup() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_TRIGGER_POPUP, {});
  }

  /**
   * @returns {EditorAction}
   */
  triggerBack() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_TRIGGER_BACK, {});
  }

  /**
   * @returns {EditorAction}
   */
  switchSettings() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_SWITCH_SETTINGS, {});
  }

  /**
   * @returns {EditorAction}
   */
  switchOverlays() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_SWITCH_OVERLAYS, {});
  }

  /**
   * @returns {EditorAction}
   */
  switchPopups() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_SWITCH_POPUPS, {});
  }

  /**
   * @returns {EditorAction}
   */
  saveTriggerProperties(
    nr,
    title,
    shapeType,
    coords
  ) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_TRIGGER_PROPERTIES_SAVE, {
      nr: nr,
      title: title,
      shapeType: shapeType,
      coords: coords
    });
  }

  /**
   * @returns {EditorAction}
   */
  changeTriggerShape(
    shape
  ) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_TRIGGER_SHAPE_CHANGE, {
      shape: shape
    });
  }

  /**
   * @returns {EditorAction}
   */
  addTriggerOverlay(
  ) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_TRIGGER_OVERLAY_ADD, {
    });
  }

  /**
   * @returns {EditorAction}
   */
  saveTriggerOverlay(
    nr,
    overlay,
    coords
  ) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_TRIGGER_OVERLAY_SAVE, {
      nr: nr,
      overlay: overlay,
      coords: coords
    });
  }

  /**
   * @returns {EditorAction}
   */
  saveTriggerPopup(
    nr,
    popup,
    position,
    size
  ) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_TRIGGER_POPUP_SAVE, {
      nr: nr,
      popup: popup,
      position: position,
      size: size
    });
  }

  /**
   * @returns {EditorAction}
   */
  changeTriggerOverlay(
    overlay
  ) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_TRIGGER_OVERLAY_CHANGE, {
      overlay: overlay
    });
  }

  uploadOverlay(data) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_OVERLAY_UPLOAD, {
      data: data
    });
  }

  deleteOverlay(overlay) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_OVERLAY_DELETE, {
      overlay: overlay
    });
  }

  renamePopup(nr) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_POPUP_RENAME, {
      nr: nr
    });
  }

  deletePopup(nr) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_POPUP_DELETE, {
      nr: nr
    });
  }

  /**
   * @returns {EditorAction}
   */
  addTriggerPopup(
  ) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_TRIGGER_POPUP_ADD, {
    });
  }

  savePopup(data, nr) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_POPUP_SAVE, {
      data: data,
      nr: nr
    });
  }

  saveSettings(form) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_SAVE_SETTINGS, {
      form: form
    });
  }

}