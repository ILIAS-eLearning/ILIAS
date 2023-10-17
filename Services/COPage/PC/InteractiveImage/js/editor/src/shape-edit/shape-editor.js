
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

import ShapeFactory from "./shape-factory.js";
import Poly from "./poly.js";
import IimCommonUtil from "../../../common/src/util.js";

/**
 * Circle
 */
export default class ShapeEditor {

    /**
     * @param Handle center
     * @param Handle point
     */
    constructor(mobElement) {
        this.mobElement = mobElement;
        this.shapes = [];
        this.currentShape = null;
        this.overlays = [];
        this.currentOverlay = null;
        this.markers = [];
        this.currentMarker = null;
        this.factory = new ShapeFactory();
        this.initEvents();
        this.allowAdd = false
        this.iimCommonUtil = new IimCommonUtil();
    }

    setAllowAdd(allow) {
        this.allowAdd = allow;
    }

    initEvents() {
        const t = this;
        const f = this.factory;
        const mob = this.mobElement;
        mob.addEventListener("click", (e) => {
            if (t.currentShape === null || !t.allowAdd) {
                return;
            }
            const cs = t.shapes[t.currentShape];
            if (cs instanceof Poly) {
                e = e || window.event;
                e.preventDefault();
                let rect = mob.getBoundingClientRect();
                let x = Math.round(e.clientX - rect.left);
                let y = Math.round(e.clientY - rect.top);
                cs.addHandle(f.handle(x, y));
                t.repaint();
            }
        });
    }

    factory() {
        return this.factory;
    }

    removeAllShapes() {
        this.shapes = [];
        this.currentShape = null;
    }
    addShape(shape, asCurrent = false) {
        if (!shape) {
            return;
            shape = this.factory.rect(10,10,50,50);
        }
        this.shapes.push(shape);
        if (asCurrent) {
            this.currentShape = this.shapes.length - 1;
        }
    }

    addMarker(marker, asCurrent = false) {
        if (!marker) {
            return;
        }
        this.markers.push(marker);
        if (asCurrent) {
            this.currentMarker = this.markers.length - 1;
        }
    }

    removeAllMarkers() {
        this.markers = [];
        this.currentMarker = null;
    }

    removeAllOverlays() {
        this.overlays = [];
        this.currentOverlay = null;
    }

    addOverlay(overlay, asCurrent = false) {
        this.overlays.push(overlay);
        if (asCurrent) {
            this.currentOverlay = this.overlays.length - 1;
        }
    }

    removeAllChilds(node) {
        while (node.firstChild) {
            node.removeChild(node.lastChild);
        }
    }

    removeAllChildsOfName(node, name) {
        node.querySelectorAll(name).forEach(n => n.remove());
    }

    removeAllChildsBySelector(node, selector) {
        node.querySelectorAll(selector).forEach(n => n.remove());
    }

    getSvg() {
        return this.iimCommonUtil.getOverlaySvg(this.mobElement);
    }

    addClickLayer() {
        let click = document.getElementById("il-copg-iim-click");
        if (!click) {
            const img = this.mobElement.querySelector("img");
            const click = img.cloneNode(true);
            click.id = "il-copg-iim-click";
            click.style.position = "absolute";
            click.style.left = "0px";
            click.style.top = "0px";
            click.style.width = "100%";
            click.style.height = "100%";
            click.style.opacity = "1e-10";
            this.mobElement.appendChild(click);

            const map = document.createElement("map");
            map.name = "il-copg-iim-map";
            map.id = "il-copg-iim-map";
            this.mobElement.appendChild(map);
            let cnt = 0;
            this.shapes.forEach((shape) => {
                shape.addToMap(cnt++, map);
            });
            click.useMap = "#il-copg-iim-map";
        };
        return click;
    }

    removeAllHandles() {
        this.removeAllChildsOfName(this.mobElement, "a[data-copg-iim-type='handle']");
    }

    removeAllOverlayImages() {
        this.removeAllChildsBySelector(this.mobElement, "img[data-copg-iim-type='overlay']");
    }

    removeAllMarkerLinks() {
        this.removeAllChildsBySelector(this.mobElement, "a[data-copg-iim-type='marker']");
    }

    repaint() {
        console.log("REPAINT");
        this.repaintSvg();
        this.removeAllHandles();
        this.removeAllOverlayImages();
        this.removeAllMarkerLinks();
        if (this.currentShape !== null) {
            console.log("1");
            const cs = this.shapes[this.currentShape];
            cs.getHandles().forEach((h) => {
                h.addHandleToMobElement(this.mobElement, !this.allowAdd);
                h.setOnDrag(() => {
                    this.repaintSvg();
                });
            });
        } else {
            console.log("2");
            if (this.currentMarker !== null) {
                console.log("3");
                console.log("ADDING MARKER");
                const m = this.markers[this.currentMarker];
                m.addMarkerToMobElement(this.mobElement, true);
            } else {
                this.markers.forEach((m) => {
                    m.addMarkerToMobElement(this.mobElement, false);
                });
            }
        }
        if (this.currentOverlay !== null) {
            const ov = this.overlays[this.currentOverlay];
            ov.addOverlayToMobElement(this.mobElement, true);
        }
    }

    repaintSvg() {
        const svg = this.getSvg();
        this.removeAllChilds(svg);
        let cnt = 0;
        this.shapes.forEach((shape) => {
            shape.addToSvg(svg);
        });
    }

}
