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
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Refinery\Encode\Transformation;

use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ValueError;

/**
 * Inspired by: Laminas escaper: https://github.com/laminas/laminas-escaper.
 * This class expects a valid UTF-8 string. Conversion between encodings is not the responsibility of this class.
 *
 * This class encodes a given string as as an URL according to RFC 3986 (http://www.faqs.org/rfcs/rfc3986.html).
 * Please see https://www.php.net/manual/en/function.rawurlencode.php for further information.
 */
class URL implements Transformation
{
    use DeriveInvokeFromTransform;
    use DeriveApplyToFromTransform;

    public function transform($from)
    {
        if (false === preg_match('//u', $from)) {
            throw new ValueError('Invalid UTF-8 string given.');
        }
        return rawurlencode($from);
    }
}
