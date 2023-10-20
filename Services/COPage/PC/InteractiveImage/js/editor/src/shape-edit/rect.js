
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
 * Shape
 */
export default class Rect extends Shape {

    /**
     * @param Handle topLeft
     * @param Handle bottomRight
     */
    constructor(topLeft, bottomRight, data = {}) {
        super([topLeft, bottomRight], data);
    }

    /**
     * @return Handle
     */
    getTopLeft() {
        return this.handles[0];
    }

    /**
     * @return Handle
     */
    getBottomRight() {
        return this.handles[1];
    }

    addToSvg(svg) {
        let r = this.createSvgElement("rect");
        const x = Math.min(this.getTopLeft().getX(), this.getBottomRight().getX());
        const y = Math.min(this.getTopLeft().getY(), this.getBottomRight().getY());
        const w = Math.abs(this.getTopLeft().getX() - this.getBottomRight().getX());
        const h = Math.abs(this.getTopLeft().getY() - this.getBottomRight().getY());
        r = svg.appendChild(r);
        r.setAttribute("x", x);
        r.setAttribute("y", y);
        r.setAttribute("width", w);
        r.setAttribute("height", h);
        this.addDataAttributes(r);
        //r.id = this.getElementId(nr);
        this.setStyle(r);
        return r;
    }

    getAreaCoordsString() {
        return this.getTopLeft().getX() + "," +
          this.getTopLeft().getY() + "," +
          this.getBottomRight().getX() + "," +
          this.getBottomRight().getY();
    }

    getAreaShapeString() {
        return "rect";
    }
}
