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

import ProgressBarAsyncDecorator from './ProgressBarAsyncDecorator.js';

/**
 * @param {AsyncRenderer} asyncRenderer
 * @param {ProgressBar} progressBar
 * @param {HTMLProgressElement} element
 * @param {number} refreshRateInMs
 * @returns {ProgressBarAsyncDecorator}
 */
export default function createAsyncProgressBar(
  asyncRenderer,
  progressBar,
  element,
  refreshRateInMs,
) {
  if (!element.hasAttribute('data-url')) {
    throw new Error('Async progress bar must provide a "data-url" attribute.');
  }

  return new ProgressBarAsyncDecorator(
    asyncRenderer,
    progressBar,
    refreshRateInMs,
    element.getAttribute('data-url'),
  );
}
