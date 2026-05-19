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
import createTransferButton from './createTransferButton.js';
import performClipboardTransfer from './performClipboardTransfer.js';
import performWebShareTransfer from './performWebShareTransfer.js';

/**
 * @param {HTMLInputElement} payloadInput
 * @param {string} payload
 * @returns {{text: string, url: string}}
 */
function createWebShareTransferPayload(payloadInput, payload) {
  return {
    text: payloadInput.labels[0]?.textContent ?? '',
    url: payload,
  };
}

/**
 * @param {Navigator} navigator
 * @param {HTMLInputElement} payloadInput
 * @param {string} transferType
 * @returns {function(string): Promise<void>}
 */
function createTransferCallbackForType(navigator, payloadInput, transferType) {
  switch (transferType) {
    case CONSTANTS.CLIPBOARD_TRANSFER_TYPE:
      return (payload) => performClipboardTransfer(navigator, payload);
    case CONSTANTS.WEB_SHARE_TRANSFER_TYPE:
      return (payload) => performWebShareTransfer(
        navigator,
        createWebShareTransferPayload(payloadInput, payload),
      );
    default:
      throw new Error(`Transfer: unknown transfer type '${transferType}'.`);
  }
}

/**
 * @param {Navigator} navigator
 * @param {HTMLElement} transferElement
 */
export default function createLinkTransfer(navigator, transferElement) {
  const payloadInput = transferElement.querySelector(CONSTANTS.PAYLOAD_SELECTOR);
  if (!(payloadInput)) {
    throw new Error('Transfer: payload input not found.');
  }
  Array
    .from(transferElement.querySelectorAll(CONSTANTS.TRANSFER_BUTTON_SELECTOR))
    .forEach((transferButton) => {
      const transferType = transferButton.getAttribute(CONSTANTS.TRANSFER_TYPE_ATTRIBUTE);
      createTransferButton(
        navigator,
        createTransferCallbackForType(navigator, payloadInput, transferType),
        transferButton,
        payloadInput.value ?? '',
      );
    });
}
