
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

import Handle from "./handle.js";

/**
 * Shape
 */
export default class Shape {

    /**
     * @param Handle[] coords
     */
    constructor(handles = [], data = {}) {
        this.handles = handles;
        this.data = data;
    }

    /**
     * @return Handle[]
     */
    getHandles() {
        return this.handles;
    }

    getStyle () {
        return "";
        return "stroke:red; stroke-width:1; fill:none;";
    }

    getStyleClass () {
        return "copg-iim-area-shape-sel";
    }

    createSvgElement(name) {
        return document.createElementNS("http://www.w3.org/2000/svg", name);
    }

    /*getElementId (nr) {
        return "il-copg-iim-shape-" + nr;
    }*/

    /**
     * @param int nr
     */
    addToSvg(svg) {
    }

    addDataAttributes(el) {
        for (const [key, value] of Object.entries(this.data)) {
            el.dataset[key] = value;
        }
    }

    setStyle(el) {
        el.classList.add(this.getStyleClass());
    }

    /**
     * @param int nr
     */
    addToMap(nr, map) {
    }

    getCurrentCoordsAsString() {
        return "";
    }

    getAreaCoordsString() {
        return "";
    }

    getAreaShapeString() {
        return "";
    }

    /**
     * @param int nr
     */
    addToMap(nr, map) {
        const area = document.createElement("area");
        area.shape = this.getAreaShapeString();
        area.coords = this.getAreaCoordsString();
        area.href = "#";
        map.appendChild(area);
    }

}
