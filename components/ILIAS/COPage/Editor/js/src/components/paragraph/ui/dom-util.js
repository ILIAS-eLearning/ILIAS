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

/**
 * Dom utilities
 */
export default class DomUtil {

  /**
   * @type {boolean}
   */
  //debug = true;

  constructor() {
    this.debug = true;
  }

  /**
   * @return {int}
   */
  getDocumentScrollLeft() {
    return Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);
  }

  /**
   * @return {int}
   */
  getDocumentScrollTop() {
    return Math.max(document.documentElement.scrollTop, document.body.scrollTop);
  }

  getXY (node) {
    let scrollLeft, scrollTop, box,
      xy = false;

    if (node.style.display !== 'none') {
      box = node.getBoundingClientRect();
      scrollLeft = this.getDocumentScrollLeft();
      scrollTop = this.getDocumentScrollTop();
      xy = [box.left, box.top];

      if ((scrollTop || scrollLeft)) {
        xy[0] += scrollLeft;
        xy[1] += scrollTop;
      }
      xy[0] = Math.round(xy[0]);
      xy[1] = Math.round(xy[1]);
    }

    return xy;
  }

  getRegion(node) {
    const p = this.getXY(node),
      t = p[1],
      r = p[0] + node.offsetWidth,
      b = p[1] + node.offsetHeight,
      l = p[0];
    const reg = this.region(t,r,b,l);
    console.log(reg);
    return reg;
  };

  region(t,r,b,l) {
    return {
      top: t,
      y: t,
      left: l,
      x: l,
      right: r,
      bottom: b,
      width: r - l,
      height: b - t
    };
  }
  getViewportWidth() {
    return document.documentElement.clientWidth;
  }

  getViewportHeight() {
    return document.documentElement.clientHeight;
  }

  getClientRegion() {
    const t = this.getDocumentScrollTop(),
      l = this.getDocumentScrollLeft(),
      r = this.getViewportWidth() + l,
      b = this.getViewportHeight() + t;
    return this.region(t, r, b, l);
  }

  getComputedStyle(node, property) {
    return node.ownerDocument.defaultView.getComputedStyle(node,null)[property];
  }

  setX(node, x) {
    this.setXY(node, [x, null]);
  }

  setY(node, y) {
    this.setXY(node, [null, y]);
  }

  setXY(node, xy, retry = false) {
    let pos = node.style.position,
      delta = [ // assuming pixels; if not we will have to retry
        parseInt( this.getComputedStyle(node, 'left'), 10 ),
        parseInt( this.getComputedStyle(node, 'top'), 10 )
      ],
      currentXY,
      newXY;

    currentXY = this.getXY(node);

    if (!xy || currentXY === false) {
      return false;
    }

    if (pos === 'static') {
      node.style.position = relative;
    }

    if (isNaN(delta[0]) ) {     // 'auto'
      delta[0] = (pos === 'relative') ? 0 : node.offsetLeft;
    }
    if (isNaN(delta[1]) ) {     // 'auto'
      delta[1] = (pos === 'relative') ? 0 : node.offsetTop;
    }

    if (xy[0] !== null) { // from setX
      node.style.left = (xy[0] - currentXY[0] + delta[0] + 'px');
    }

    if (xy[1] !== null) { // from setY
      node.style.top = (xy[1] - currentXY[1] + delta[1] + 'px');
    }

    if (!retry) {
      newXY = this.getXY(node);

      if ((xy[0] !== null && newXY[0] !== xy[0]) ||
        (xy[1] !== null && newXY[1] !== xy[1]) ) {
        this.setXY(node, xy, true);
      }
    }
  }
}