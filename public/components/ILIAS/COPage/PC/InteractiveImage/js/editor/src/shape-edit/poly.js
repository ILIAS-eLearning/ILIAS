
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

import Shape from "./shape.js";

/**
 * Poly
 */
export default class Poly extends Shape {

    constructor(data = {}) {
        super([], data);
    }

    /**
     * @return Handle
     */
    addHandle(h) {
        return this.handles.push(h);
    }

    addToSvg(svg) {
        let p = this.createSvgElement("polygon");
        let points = "";
        this.handles.forEach((h) => {
            points = points + h.getX() + "," + h.getY() + " ";
        });
        p = svg.appendChild(p);
        p.setAttribute("points", points);
        //p.id = this.getElementId(nr);
        this.addDataAttributes(p);
        this.setStyle(p);
        return p;
    }

    getAreaCoordsString() {
        let cstr = "";
        this.handles.forEach((h) => {
            if (cstr != "") {
                cstr = cstr + ",";
            }
            cstr = cstr + h.getX() + "," + h.getY();
        });
        return cstr;
    }

    getAreaShapeString() {
        return "poly";
    }

}
