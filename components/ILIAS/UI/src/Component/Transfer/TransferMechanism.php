<?php

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

declare(strict_types=1);

namespace ILIAS\UI\Component\Transfer;

/**
 * Describes different transfer mechanisms which can used to transfer information
 * from one medium or context to another.
 */
enum TransferMechanism: string
{
    /** Transfer information by copying its contents to the computers clipboard. */
    case CLIPBOARD = 'clipboard';

    /** Transfer information to Web Share Target's using the browsers Web Share API. */
    case WEB_SHARE = 'web-share';

    /** Transfer information by embedding its contents in a QR code. */
    case QR_CODE = 'qr-code';
}
