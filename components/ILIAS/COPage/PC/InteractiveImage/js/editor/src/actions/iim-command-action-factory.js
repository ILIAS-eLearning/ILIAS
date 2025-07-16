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
 ******************************************************************** */

import ACTIONS from './iim-action-types.js';

/**
 * COPage command actions being sent to the server
 */
export default class IIMCommandActionFactory {
  /**
   * @type {ClientActionFactory}
   */
  // clientActionFactory;

  /**
   * @param {ClientActionFactory} clientActionFactory
   */
  constructor(clientActionFactory) {
    this.COMPONENT = 'InteractiveImage';
    this.clientActionFactory = clientActionFactory;
  }

  /**
   * @param pcid
   * @param content
   * @param redirect
   * @return {CommandAction}
   */
  saveTriggerProperties(triggerNr, title, shapeType, coords, hl_mode, hl_class) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.C_SAVE_TRIGGER_PROPERTIES, {
      trigger_nr: triggerNr,
      title,
      shape_type: shapeType,
      coords,
      hl_mode,
      hl_class,
    });
  }

  deleteTrigger(nr) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.C_DELETE_TRIGGER, {
      nr,
    });
  }

  /**
   * @param pcid
   * @param content
   * @param redirect
   * @return {CommandAction}
   */
  saveTriggerOverlay(triggerNr, overlay, coords) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.C_SAVE_TRIGGER_OVERLAY, {
      trigger_nr: triggerNr,
      overlay,
      coords,
    });
  }

  /**
   * @param pcid
   * @param content
   * @param redirect
   * @return {CommandAction}
   */
  saveTriggerPopup(triggerNr, popup, position, size) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.C_SAVE_TRIGGER_POPUP, {
      trigger_nr: triggerNr,
      popup,
      position,
      size,
    });
  }

  /**
   * @param {formData} data
   * @return {CommandAction}
   */
  uploadOverlay(data) {
    return this.clientActionFactory.formCommand(this.COMPONENT, ACTIONS.C_UPLOAD_OVERLAY, data);
  }

  deleteOverlay(overlay) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.C_DELETE_OVERLAY, {
      overlay,
    });
  }

  /**
   * @param {formData} data
   * @return {CommandAction}
   */
  savePopup(data) {
    console.log('---');
    console.log(data);

    return this.clientActionFactory.formCommand(this.COMPONENT, ACTIONS.C_SAVE_POPUP, data);
  }

  deletePopup(nr) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.C_DELETE_POPUP, {
      nr,
    });
  }

  /**
   * @param {formData} data
   * @return {CommandAction}
   */
  saveSettings(data) {
    return this.clientActionFactory.formCommand(this.COMPONENT, ACTIONS.C_SAVE_SETTINGS, data);
  }
}
