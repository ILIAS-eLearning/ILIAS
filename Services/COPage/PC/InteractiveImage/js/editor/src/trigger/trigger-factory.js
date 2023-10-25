
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

import Trigger from "./trigger.js";
import AreaFactory from "../area/area-factory.js"
import OverlayFactory from "../overlay/overlay-factory.js"
import MarkerFactory from "../marker/marker-factory.js"

/**
 * Shape
 */
export default class TriggerFactory {

  constructor() {
    this.areaFactory = new AreaFactory();
    this.overlayFactory = new OverlayFactory();
    this.markerFactory = new MarkerFactory();
  }

  trigger(
    nr,
    marker,
    overlay,
    popupNr,
    popupPosition,
    popupSize,
    title,
    area
  ) {
    return new Trigger(
      nr,
      area,
      marker,
      overlay,
      popupNr,
      popupPosition,
      popupSize,
      title
    );
  }

  /**
   */
  fromPropertiesObject(o, area = null, overlay = null, marker = null) {
    return new Trigger(
      o.Nr,
      area,
      marker,
      overlay,
      o.PopupNr,
      o.PopupPosition,
      o.PopupSize,
      o.Title
    );
  }

  fullTriggerFromModel(nr, model) {
    let trigger = null;
    model.triggers.forEach((tr) => {
      let marker = null;
      if (tr.Nr == nr) {
        const area = this.areaFactory.fromModelForId(tr.Nr, model);
        const overlay = this.overlayFactory.fromModelForNr(tr.Nr, model);
        if (area === null) {
          marker = this.markerFactory.marker(parseInt(tr.MarkerX), parseInt(tr.MarkerY), tr.Nr);
        }
        trigger = this.fromPropertiesObject(tr, area, overlay, marker);
      }
    });
    return trigger;
  }

}
