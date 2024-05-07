
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
 * Circle
 */
export default class Circle extends Shape {

    /**
     * @param Handle center
     * @param Handle point
     */
    constructor(center, point, data = {}) {
        super([center, point], data);
    }

    /**
     * @return Handle
     */
    getCenter() {
        return this.handles[0];
    }

    /**
     * @return Handle
     */
    getPoint() {
        return this.handles[1];
    }

    getRadius() {
        return Math.sqrt(
          ((this.getCenter().getX() - this.getPoint().getX()) ** 2) +
          ((this.getCenter().getY() - this.getPoint().getY()) ** 2)
        );
    }
    addToSvg(svg) {
        let c = this.createSvgElement("circle");
        const cx = this.getCenter().getX();
        const cy = this.getCenter().getY();
        const r = this.getRadius();
        c = svg.appendChild(c);
        c.setAttribute("cx", cx);
        c.setAttribute("cy", cy);
        c.setAttribute("r", r);
        //c.id = this.getElementId(nr);
        this.addDataAttributes(c);
        this.setStyle(c);
        return c;
    }

    getAreaCoordsString() {
        return this.getCenter().getX() + "," +
          this.getCenter().getY() + "," +
          this.getRadius();
    }

    getAreaShapeString() {
        return "circle";
    }

}
