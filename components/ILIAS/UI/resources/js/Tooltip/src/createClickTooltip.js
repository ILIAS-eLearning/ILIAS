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

/** @type {number} defines how long the Tooltip is visible by default. */
const DEFAULT_DURATION_IN_MS = 2_300;

/**
 * Creates a Tooltip which appears **temporarily after clicking** the given target element.
 *
 * @param {HTMLElement} targetElement
 * @param {number} [durationInMs]
 * @returns {Tooltip}
 */
export default function createClickTooltip(targetElement, durationInMs = DEFAULT_DURATION_IN_MS) {
  const tooltip = createTooltip(targetElement);

  let hideTimeoutId = null;
  tooltip.getTargetElement().addEventListener('click', () => {
    clearTimeout(hideTimeoutId);
    tooltip.show();
    hideTimeoutId = setTimeout(() => tooltip.hide(), durationInMs);
  });

  return tooltip;
}
