<?php declare(strict_types=1);

/* Copyright (c) 2020 Luka K. A. Stocker, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\ConstraintViolationException;

class IntegerTransformation implements Transformation
{
    const REG_INT = '/^\s*(0|(-?[1-9]\d*))\s*$/';

    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        if (is_int($from)) {
            return $from;
        }

        if (is_float($from) && !is_nan($from) && $from !== INF && $from !== -INF) {
            $from = round($from);
            return intval($from);
        }

        if (is_bool($from)) {
            return (int) $from;
        }

        if (is_string($from) && preg_match(self::REG_INT, $from)) {
            $int = intval($from);
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
