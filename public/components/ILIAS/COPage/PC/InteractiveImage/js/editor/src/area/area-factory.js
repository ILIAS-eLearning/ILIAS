
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

import Area from "./area.js";

/**
 * Shape
 */
export default class AreaFactory {

  constructor() {
  }

  area(
    shape,
    coords,
    hClass = '',
    hMode= '',
    id = 0,
    link = null
  ) {
    return new Area(
      shape,
      coords,
      hClass,
      hMode,
      id,
      link
    );
  }

  /**
   */
  fromPropertiesObject(o, link = null) {
    return new Area(
      o.Shape,
      o.Coords,
      o.HighlightClass,
      o.HighlightMode,
      o.Id,
      link
    );
  }

  fromModelForId(id, model) {
    let area = null;
    model.media_item.areas.forEach((a) => {
      if (a.Id == id) {
        area = this.fromPropertiesObject(a, null);
      }
    });
    return area;
  }

}
