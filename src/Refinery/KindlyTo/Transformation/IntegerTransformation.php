<?php declare(strict_types=1);

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

namespace ILIAS\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\ConstraintViolationException;

class IntegerTransformation implements Transformation
{
    private const REG_INT = '/^\s*(0|(-?[1-9]\d*))\s*$/';

    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /**
     * @inheritDoc
     */
    public function transform($from) : int
    {
        if (is_int($from)) {
            return $from;
        }

        if ($from !== INF && $from !== -INF && is_float($from) && !is_nan($from)) {
            $from = round($from);
            return (int) $from;
        }

        if (is_bool($from)) {
            return (int) $from;
        }

        if (is_string($from) && preg_match(self::REG_INT, $from)) {
            $int = (int) $from;
            // This is supposed to guard against PHP_MIN_INT and PHP_MAX_INT.
            // We only return the value if it looks the same when transforming it
            // back to string. This won't be the case for too big or too small
            // values.
            if (trim($from) === (string) $int) {
                return $int;
            }
        }

        throw new ConstraintViolationException(
            sprintf('The value "%s" can not be transformed into an integer', var_export($from, true)),
            'not_integer',
            $from
        );
    }
}
