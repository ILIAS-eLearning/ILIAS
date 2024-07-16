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
 * This class escapes all HTML characters that have special meaning in HTML in order to preserve their meaning.
 * Please see https://www.php.net/manual/en/function.htmlspecialchars.php for more information.
 * Given a valid UTF-8 string this class will return a valid HTML string.
 * This class is a wrapper around `htmlspecialchars` but ensures that the correct flags are set.
 */
class HTMLSpecialCharsAsEntities implements Transformation
{
    use DeriveInvokeFromTransform;
    use DeriveApplyToFromTransform;

    public function transform($from)
    {
        if (false === preg_match('//u', $from)) {
            throw new ValueError('Invalid UTF-8 string given.');
        }
        return htmlspecialchars($from, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8');
    }
}
