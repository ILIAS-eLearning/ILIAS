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

import ProgressBar from './ProgressBar';

/**
 * @param {Document} document
 * @param {HTMLProgressElement} element
 * @param {string} updateSignal
 */
export default function createProgressBar(document, element) {
  if (!(element instanceof document.defaultView.HTMLProgressElement)) {
    throw new Error('Progress bar must have a <progress> element.');
  }

  const messageElement = element.parentElement.querySelector('.c-progress-bar__message');
  if (messageElement === null) {
    throw new Error('Could not find progress bar message element.');
  }

  return new ProgressBar(element, messageElement);
}
