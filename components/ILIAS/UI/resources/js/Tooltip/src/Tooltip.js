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
 */

import * as CONSTANTS from './constants.js';

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class Tooltip {
  /** @var {HTMLElement} */
  #targetElement;

  /** @var {HTMLElement} */
  #containerElement;

  /** @var {HTMLElement} */
  #tooltipElement;

  /** @var {HTMLElement} */
  #contentElement;

  /** @var {HTMLElement|null} */
  #mainElement;

  /**
   * @param {HTMLElement} targetElement
   * @param {HTMLElement} containerElement
   * @param {HTMLElement} tooltipElement
   * @param {HTMLElement} contentElement
   * @param {HTMLElement|null} mainElement
   */
  constructor(
    targetElement,
    containerElement,
    tooltipElement,
    contentElement,
    mainElement,
  ) {
    this.#targetElement = targetElement;
    this.#containerElement = containerElement;
    this.#tooltipElement = tooltipElement;
    this.#contentElement = contentElement;
    this.#mainElement = mainElement;
  }

  /**
   * @returns {HTMLElement}
   */
  getTargetElement() {
    return this.#targetElement;
  }

  /**
   * @returns {HTMLElement}
   */
  getContainerElement() {
    return this.#containerElement;
  }

  /**
   * @returns {HTMLElement}
   */
  getTooltipElement() {
    return this.#tooltipElement;
  }

  show() {
    this.getContainerElement().classList.add(CONSTANTS.TOOLTIP_VISIBLE_CLASS);
    this.#checkVerticalBounds();
    this.#checkHorizontalBounds();
  }

  hide() {
    this.getContainerElement().classList.remove(CONSTANTS.TOOLTIP_VISIBLE_CLASS);
    this.getContainerElement().classList.remove(CONSTANTS.TOOLTIP_TOP_CLASS);
    this.getTooltipElement().style.transform = null;
  }

  /**
   * @param {string} textContent
   */
  setContent(textContent) {
    this.#contentElement.textContent = textContent;
  }

  /**
   * @returns {boolean}
   */
  isVisible() {
    return this.getContainerElement().classList.contains(CONSTANTS.TOOLTIP_VISIBLE_CLASS);
  }

  /**
   * @returns {boolean}
   */
  isHidden() {
    return !this.isVisible();
  }

  #checkVerticalBounds() {
    const displayRect = this.#getDisplayRect();

    // prefer above the target
    this.getContainerElement().classList.add(CONSTANTS.TOOLTIP_TOP_CLASS);
    const overflowTop = displayRect.top - this.getTooltipElement().getBoundingClientRect().top;
    if (overflowTop <= 0) {
      return; // fits above
    }

    // doesn't fit above, try below
    this.getContainerElement().classList.remove(CONSTANTS.TOOLTIP_TOP_CLASS);
    const overflowBottom = this
      .getTooltipElement()
      .getBoundingClientRect()
      .bottom - displayRect.bottom;

    // fits neither way: use whichever side overflows less
    if (overflowBottom > overflowTop) {
      this.getContainerElement().classList.add(CONSTANTS.TOOLTIP_TOP_CLASS);
    }
  }

  #checkHorizontalBounds() {
    const tooltipRect = this.getTooltipElement().getBoundingClientRect();
    const displayRect = this.#getDisplayRect();

    let shift = 0;
    if (tooltipRect.right > displayRect.right) {
      shift = displayRect.right - tooltipRect.right; // negative: move left
    } else if (tooltipRect.left < displayRect.left) {
      shift = displayRect.left - tooltipRect.left; // positive: move right
    }

    if (shift !== 0) {
      // keep the CSS centering (-50%) and add the correction on top
      this.getTooltipElement().style.transform = `translateX(calc(-50% + ${shift}px))`;
    }
  }

  /**
   * @returns {{left: number, top: number, width: number, height: number}}
   */
  #getDisplayRect() {
    if (this.#mainElement !== null && this.#mainElement.contains(this.#tooltipElement)) {
      return this.#mainElement.getBoundingClientRect();
    }
    return {
      left: 0,
      top: 0,
      width: this.getTooltipElement().ownerDocument.defaultView.innerWidth,
      height: this.getTooltipElement().ownerDocument.defaultView.innerHeight,
    };
  }
}
