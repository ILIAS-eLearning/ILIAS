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
 ******************************************************************** */

import Util from '../common/util.js';
import AreaFactory from '../area/area-factory.js';

const presentation = (function () {
  function init(node) {
    let svg;
    const util = new Util();
    const areaFactory = new AreaFactory();

    // all mob images that use a map
    node.querySelectorAll('img[usemap^="#map_il_"]').forEach((img) => {
      const mapName = img.getAttribute('usemap').substring(1); // remove "#"
      const map = document.querySelector(`map[name="${mapName}"]`);
      if (map) {
        svg = util.getOverlaySvg(img.parentNode); // this will add the svg to the mob
        const areas = map.querySelectorAll('area');
        areas.forEach((areaEl) => {
          const shapeAtt = areaEl.getAttribute('shape');
          const coordsAtt = areaEl.getAttribute('coords');
          const hlMode = areaEl.getAttribute('data-hl-mode');
          const hlClass = areaEl.getAttribute('data-hl-class');
          const area = areaFactory.area(
            shapeAtt,
            coordsAtt,
            hlClass,
            hlMode,
          );
          const shape = area.getShape();
          const shapeEl = shape.addToSvg(svg);
          shapeEl.classList.add(`copg-iim-hl-mode-${hlMode}`);
          shapeEl.classList.add(`copg-iim-hl-class-${hlClass}`);
        });
      }
    });
  }

  return {
    init,
  };
}());
window.addEventListener('load', () => {
  presentation.init(document);
}, false);
