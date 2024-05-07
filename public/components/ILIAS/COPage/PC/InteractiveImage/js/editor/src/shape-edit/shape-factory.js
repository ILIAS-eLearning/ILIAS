
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
import Rect from "./rect.js";
import Circle from "./circle.js";
import Poly from "./poly.js";

/**
 * Shape
 */
export default class ShapeFactory {

  constructor() {
  }

  /**
   * @param int x
   * @param int y
   * @return Handle
   */
  handle(x, y) {
    return new Handle(x, y);
  }

  /**
   * @param int x1
   * @param int y1
   * @param int x2
   * @param int y2
   * @return Rect
   */
  rect(x1, y1, x2, y2, data = {}) {
    return new Rect(this.handle(x1, y1), this.handle(x2, y2), data);
  }

  rectFromCoordString(coordStr, data = {}) {
    return new Rect(this.handle(x1, y1), this.handle(x2, y2), data);
  }

  /**
   * @param int x1
   * @param int y1
   * @param int x2
   * @param int y2
   * @return Circle
   */
  circle(x1, y1, x2, y2, data = {}) {
    return new Circle(this.handle(x1, y1), this.handle(x2, y2), data);
  }

  /**
   * @return Poly
   */
  poly(coords, data = {}) {
    const p = new Poly(data);
    for (let i=0; i < (coords.length / 2); i++) {
      p.addHandle(this.handle(coords[i*2], coords[i*2+1]));
    }
    return p;
  }

}
