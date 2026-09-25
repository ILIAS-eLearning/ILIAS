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

export const HIDDEN_CLASS = 'hidden';
export const VISIBLE_CLASS = 'visible';

export const DEFAULT_STATUS = 'default';
export const SUCCESS_STATUS = 'success';
export const FAILURE_STATUS = 'failure';

export const CLIPBOARD_TRANSFER_TYPE = 'clipboard';
export const WEB_SHARE_TRANSFER_TYPE = 'web-share';
export const QR_CODE_TRANSFER_TYPE = 'qr-code';

export const TRANSFER_STATUS_ATTRIBUTE = 'data-transfer-status';
export const TRANSFER_TYPE_ATTRIBUTE = 'data-transfer-type';

export const TRANSFER_SELECTOR = '.c-transfer';
export const PAYLOAD_SELECTOR = `${TRANSFER_SELECTOR}__payload`;
export const TRANSFER_BUTTON_SELECTOR = `[${TRANSFER_TYPE_ATTRIBUTE}]`;
export const DEFAULT_STATUS_SELECTOR = `[${TRANSFER_STATUS_ATTRIBUTE}="${DEFAULT_STATUS}"]`;
export const SUCCESS_STATUS_SELECTOR = `[${TRANSFER_STATUS_ATTRIBUTE}="${SUCCESS_STATUS}"]`;
export const FAILURE_STATUS_SELECTOR = `[${TRANSFER_STATUS_ATTRIBUTE}="${FAILURE_STATUS}"]`;
