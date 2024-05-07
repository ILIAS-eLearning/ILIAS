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

import AreaFactory from "../area/area-factory.js";
import TriggerFactory from "../trigger/trigger-factory.js";
import OverlayFactory from '../overlay/overlay-factory.js';
import MarkerFactory from '../marker/marker-factory.js';


/**
 * Interactive Image Model
 */
export default class Model {

  constructor() {
    this.debug = true;

    this.STATE_OVERVIEW = "overview";                 // overview
    this.STATE_TRIGGER_PROPERTIES = "trigger_prop";   // trigger properties
    this.STATE_TRIGGER_OVERLAY = "trigger_overlay";   // trigger overlay
    this.STATE_TRIGGER_POPUP = "trigger_popup";   // trigger popup
    this.STATE_SETTINGS = "settings";   // settings
    this.STATE_OVERLAYS = "overlays";   // settings
    this.STATE_POPUPS = "popups";   // settings
    this.ACTION_STATE_ADD = "add";   // add
    this.ACTION_STATE_EDIT = "edit";   // edit

    this.model = {
      state: this.STATE_OVERVIEW,
      areaNr: 0,
      iim: null,
      currentTrigger: null
    };
    this.states = [
      this.STATE_OVERVIEW,
      this.STATE_TRIGGER_PROPERTIES,
      this.STATE_TRIGGER_OVERLAY,
      this.STATE_TRIGGER_POPUP,
      this.STATE_SETTINGS,
      this.STATE_OVERLAYS,
      this.STATE_POPUPS
    ];
    this.actionStates = [
      this.ACTION_STATE_ADD,
      this.ACTION_STATE_EDIT
    ];
    this.areaFactory = new AreaFactory();
    this.triggerFactory = new TriggerFactory();
    this.overlayFactory = new OverlayFactory();
    this.markerFactory = new MarkerFactory();
  }

  log(message) {
    if (this.debug) {
      console.log(message);
    }
  }

  /**
   * Note: area.Id = trigger.Nr
   */
  initModel(iimModel) {
    this.model.iim = iimModel;
  }

  /**
   * @param {string} state
   */
  setState(state) {
    if (this.states.includes(state)) {
      this.log("model.setState " + state);
      this.model.state = state;
    }
  }

  /**
   * @return {string}
   */
  getState() {
    return this.model.state;
  }

  setActionState(state) {
    if (this.actionStates.includes(state)) {
      this.model.actionState = state;
    }
  }

  /**
   * @return {string}
   */
  getActionState() {
    return this.model.actionState;
  }

  getNextTriggerNr() {
    let maxNr = 0;
    this.model.iim.triggers.forEach((a) => {
      maxNr = Math.max(maxNr, a.Nr);
    });
    return maxNr + 1;
  }

  getCaption() {
    return this.model.iim.media_item.caption;
  }

  addStandardTrigger() {
    const area = this.areaFactory.area(
      "Rect",
      "10,10,50,50"
    );
    this.model.currentTrigger = this.triggerFactory.trigger(
      this.getNextTriggerNr(),
      null,
      null,
      "",
      "",
      "",
      "",
      area
    );
    this.log("addStandardTrigger");
  }

  changeTriggerShape(shape) {
    let area;
    let marker;
    console.log("MODEL: CHANGE SHAPE");
    console.log(shape);
    switch (shape) {
      case "Rect":
        area = this.areaFactory.area(
          "Rect",
          "10,10,50,50"
        );
        this.model.currentTrigger.setArea(area);
        break;
      case "Circle":
        area = this.areaFactory.area(
          "Circle",
          "100,100,50"
        );
        this.model.currentTrigger.setArea(area);
        break;
      case "Poly":
        area = this.areaFactory.area(
          "Poly",
          ""
        );
        this.model.currentTrigger.setArea(area);
        break;
      case "Marker":
        marker = this.markerFactory.marker(0, 0, this.model.currentTrigger.getNr());
        this.model.currentTrigger.setMarker(marker);
        break;
    }
  }

  changeTriggerOverlay(selection) {
    const tr = this.model.currentTrigger;
    const ov = tr.getOverlay();
    let x = 0;
    let y = 0;
    if (ov) {
      x = ov.getX();
      y = ov.getY();
    }

    if (selection && selection != '') {
      tr.setOverlay(this.overlayFactory.forSelection(selection, x, y, this.model.iim));
    } else {
      tr.setOverlay(null);
    }
  }

  setTriggerByNr(triggerNr) {
    this.model.currentTrigger = this.triggerFactory.fullTriggerFromModel(
      triggerNr,
      this.model.iim
    );
  }

  resetCurrentTrigger() {
    this.model.currentTrigger = null;
  }

  updateCurrentTriggerFromModel() {
    if (this.getCurrentTrigger()) {
      this.setTriggerByNr(this.getCurrentTrigger().getNr());
    }
  }

  getCurrentTrigger() {
    return this.model.currentTrigger;
  }

  getOverlays() {
    return this.model.iim.overlays;
  }

  getPopups() {
    return this.model.iim.popups;
  }

  getPopupTitle(nr) {
    let title = '';
    this.model.iim.popups.forEach((p) => {
      if (p.nr === nr) {
        title = p.title;
      }
    });
    return title;
  }

}