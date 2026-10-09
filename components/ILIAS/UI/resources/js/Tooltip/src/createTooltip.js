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
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */

import * as CONSTANTS from './constants.js';
import Tooltip from './Tooltip.js';

/**
 * Returns the visible main-element of the given document.
 *
 * A document may contain multiple main-elemets, only one must be visible
 * (not have a hidden-attribute).
 *
 * @param {HTMLDocument} document
 * @returns {HTMLElement|null}
 * @see https://html.spec.whatwg.org/multipage/grouping-content.html#the-main-element
 */
function getVisibleMainElement(document) {
  const mainElements = document.getElementsByTagName('main');
  const visibleMain = Array.from(mainElements).find(
    (element) => Object.prototype.hasOwnProperty.call(element, 'hidden') === false,
  );

  return (undefined !== visibleMain) ? visibleMain : null;
}

/**
 * Creates a Tooltip surrounding the given target element that is **operated manually**.
 *
 * @param {HTMLElement} targetElement
 * @return {Tooltip}
 */
export default function createTooltip(targetElement) {
  const containerElement = targetElement.closest(CONSTANTS.TOOLTIP_CONTAINER_SELECTOR);
  const tooltipElement = containerElement.querySelector(`:scope > ${CONSTANTS.TOOLTIP_SELECTOR}`);
  const contentElement = tooltipElement.querySelector(`:scope > ${CONSTANTS.TOOLTIP_CONTENT_SELECTOR}`);
  if (!tooltipElement || !containerElement || !contentElement) {
    throw new Error('Could not find tooltip elements inside the current DOM.');
  }

  return new Tooltip(
    targetElement,
    containerElement,
    tooltipElement,
    contentElement,
    getVisibleMainElement(targetElement.ownerDocument),
  );
}
