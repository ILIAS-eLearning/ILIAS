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
import sleep from '../../Core/src/sleep.js';

/** @type {number} defines how long a success/failure status is visible. */
const STATUS_DURATION_IN_MS = 1_300;

/** @type {Set<HTMLButtonElement>} holds buttons which temporarily show status. */
const busyButtonSet = new Set();

/** @param {HTMLElement} element */
function showElement(element) {
  return element.classList.replace(CONSTANTS.HIDDEN_CLASS, CONSTANTS.VISIBLE_CLASS);
}

/** @param {HTMLElement} element */
function hideElement(element) {
  return element.classList.replace(CONSTANTS.VISIBLE_CLASS, CONSTANTS.HIDDEN_CLASS);
}

/**
 * Hides the default status and shows the given temporary status, then reverses it.
 *
 * @param {HTMLElement} defaultStatus
 * @param {HTMLElement} temporaryStatus
 */
async function showStatusTemporarily(defaultStatus, temporaryStatus) {
  hideElement(defaultStatus);
  showElement(temporaryStatus);
  await sleep(STATUS_DURATION_IN_MS);
  hideElement(temporaryStatus);
  showElement(defaultStatus);
}

/**
 * Copies the given payload to the computers clipboard and updates the
 * temporary status accordingly.
 *
 * @param {Navigator} navigator
 * @param {function(string): Promise<void>} performTransferCallback
 * @param {HTMLButtonElement} transferButton
 * @param {string} payload
 * @param {HTMLElement} defaultStatusElement
 * @param {HTMLElement} successStatusElement
 * @param {HTMLElement} failureStatusElement
 * @returns {Promise<void>}
 */
async function performTransfer(
  navigator,
  performTransferCallback,
  transferButton,
  payload,
  defaultStatusElement,
  successStatusElement,
  failureStatusElement,
) {
  if (busyButtonSet.has(transferButton)) {
    return;
  }
  try {
    busyButtonSet.add(transferButton);
    await performTransferCallback(payload);
    await showStatusTemporarily(defaultStatusElement, successStatusElement);
  } catch (error) {
    await showStatusTemporarily(defaultStatusElement, failureStatusElement);
  } finally {
    busyButtonSet.delete(transferButton);
  }
}

/**
 * @param {Navigator} navigator
 * @param {function(string): Promise<void>} performTransferCallback
 * @param {HTMLButtonElement} transferButton
 * @param {string} payload
 */
export default function createTransferButton(
  navigator,
  performTransferCallback,
  transferButton,
  payload,
) {
  const defaultStatusElement = transferButton.querySelector(CONSTANTS.DEFAULT_STATUS_SELECTOR);
  const successStatusElement = transferButton.querySelector(CONSTANTS.SUCCESS_STATUS_SELECTOR);
  const failureStatusElement = transferButton.querySelector(CONSTANTS.FAILURE_STATUS_SELECTOR);
  if (!defaultStatusElement || !successStatusElement || !failureStatusElement) {
    throw new Error('Transfer: one or more status elements not found.');
  }
  transferButton.addEventListener('click', () => {
    performTransfer(
      navigator,
      performTransferCallback,
      transferButton,
      payload,
      defaultStatusElement,
      successStatusElement,
      failureStatusElement,
    );
  });
}
