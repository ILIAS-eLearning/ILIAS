
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

import Overlay from "../overlay/overlay.js";

/**
 * Shape
 */
export default class Trigger {

    /**
     */
    constructor(
      nr,
      area = null,
      marker = null,
      overlay= null,
      popupNr= "",
      popupPosition= "",
      popupSize= "",
      title= "",
    ) {
        this.nr = nr;
        this.marker = marker;
        this.overlay = overlay;
        this.popupNr = popupNr;
        this.popupPosition = popupPosition;
        this.popupSize = popupSize;
        this.title = title;
        this.area = area;
    }


    toPropertiesObject() {
        let markerX = "";
        let markerY = "";
        const type = (this.area === null)
            ? 'Marker'
            : 'Area';
        if (this.area === null && this.marker !== null) {
            markerX = this.marker.getX();
            markerY = this.marker.getY();
        }
        return {
            MarkerX: markerX,
            MarkerY: markerY,
            Nr: this.nr,
            Overlay: this.overlay.getSrc(),
            OverlayX: this.overlay.getX(),
            OverlayY: this.overlay.getY(),
            PopupHeight: '',
            PopupNr: this.popupNr,
            PopupWidth: '',
            PopupX: '',
            PopupY: '',
            PopupPosition: this.popupPosition,
            PopupSize: this.popupSize,
            Title: this.title,
            Type: type
        };
    }

    setArea(area) {
        this.area = area;
        this.marker = null;
    }

    setOverlay(overlay) {
        this.overlay = overlay;
    }

    setMarker(marker) {
        this.area = null;
        this.marker = marker;
    }

    getShape() {
        if (this.area){
            return this.area.getShape(this.nr);
        }
    }

    getOverlay() {
        return this.overlay;
    }

    getNr() {
        return this.nr;
    }

    getMarker() {
        return this.marker;
    }

    getPopupNr() {
        return this.popupNr;
    }

    getPopupPosition() {
        return this.popupPosition;
    }

    getPopupSize() {
        return this.popupSize;
    }

}
