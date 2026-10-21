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

import createTooltip from './createTooltip.js';

/**
 * @param {MouseEvent} event
 * @param {Tooltip} tooltip
 */
function handlePointerDown(event, tooltip) {
  if (!tooltip.isVisible()) {
    return;
  }
  if (event.target === tooltip.getTargetElement() || event.target === tooltip.getTooltipElement()) {
    event.preventDefault();
  } else {
    tooltip.hide();
    tooltip.getTargetElement().blur();
  }
}

/**
 * @param {KeyboardEvent} event
 * @param {Tooltip} tooltip
 */
function handleKeyDown(event, tooltip) {
  if (!tooltip.isVisible()) {
    return;
  }
  if (event.key === 'Esc' || event.key === 'Escape') {
    tooltip.hide();
  }
}

/**
 * Creates a Tooltip which appears **while hovering** the given target element.
 *
 * @param {HTMLElement} targetElement
 * @returns {Tooltip}
 */
export default function createHoverTooltip(targetElement) {
  const tooltip = createTooltip(targetElement);

  tooltip.getTargetElement().addEventListener('focus', () => tooltip.show());
  tooltip.getTargetElement().addEventListener('blur', () => tooltip.hide());

  tooltip.getContainerElement().addEventListener('mouseenter', () => tooltip.show());
  tooltip.getContainerElement().addEventListener('touchstart', () => tooltip.show());
  tooltip.getContainerElement().addEventListener('mouseleave', () => tooltip.hide());

  targetElement.ownerDocument.addEventListener('keydown', (event) => handleKeyDown(event, tooltip));
  targetElement.ownerDocument.addEventListener('pointerdown', (event) => handlePointerDown(event, tooltip));

  return tooltip;
}
