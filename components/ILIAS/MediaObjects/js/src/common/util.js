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

export default class Util {
  constructor() {
  }

  getOverlaySvg(mobElement) {
    let svg = mobElement.querySelector("[data-mob-type='svg-overlay']");
    if (!svg) {
      svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      svg.setAttribute('data-copg-iim-type', 'svg-overlay');
      svg.style.position = 'absolute';
      svg.style.left = '0px';
      svg.style.top = '0px';
      svg.style.width = '100%';
      svg.style.height = '100%';
      mobElement.appendChild(svg);
      svg.addEventListener('click', (e) => {
        // Prevent SVG from handling the click normally
        e.preventDefault();
        e.stopPropagation();

        // Temporarily disable pointer events to find what's underneath
        svg.style.pointerEvents = 'none';

        const underlying = document.elementFromPoint(e.clientX, e.clientY);

        // Restore pointer events
        svg.style.pointerEvents = '';

        if (underlying) {
          underlying.click(); // trigger click on img/area
        }
      });
    }
    return svg;
  }
}
