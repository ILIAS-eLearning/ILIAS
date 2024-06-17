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
 * This class expects a valid UTF-8 string. Conversion between encodings is not the responsibility of this class.
 *
 * This class is a wrapper around `json_encode` but ensures that the correct flags are set.
 * These flags ensure that the encoded JSON string can be included in (X)HTML embedded JS too.
 * Please see https://www.php.net/manual/en/function.json-encode.php for more information.
 */
class Json implements Transformation
{
    use DeriveInvokeFromTransform;
    use DeriveApplyToFromTransform;

    public function transform($from)
    {
        return json_encode($from, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_THROW_ON_ERROR);
    }
}
