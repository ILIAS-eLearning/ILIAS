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

namespace ILIAS\Data\QR;

/**
 * Error correction levels as defined by ISO/IEC 18004.
 *
 * Each level specifies the percentage of codewords that can be
 * restored if the QR code is damaged or partially obscured.
 * Please note that increasing the error correction level will
 * decrease the data capacity of its payload.
 *
 * @see https://www.qrcode.com/en/about/error_correction.html
 */
enum ErrorCorrectionLevel: string
{
    /** ~7% of codewords can be restored. */
    case LOW = 'L';

    /** ~15% of codewords can be restored (most fequently used). */
    case MEDIUM = 'M';

    /** ~25% of codewords can be restored. */
    case QUARTILE = 'Q';

    /** ~30% of codewords can be restored. */
    case HIGH = 'H';
}
