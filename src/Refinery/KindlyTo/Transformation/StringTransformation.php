<?php declare(strict_types=1);

/* Copyright (c) 2020 Luka K. A. Stocker, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\ConstraintViolationException;

class StringTransformation implements Transformation
{
    const BOOL_TRUE = true;
    const BOOL_FALSE = false;
    const BOOL_TRUE_NUMBER = 1;
    const BOOL_FALSE_NUMBER = 0;
    const BOOL_TRUE_STRING = 'true';
    const BOOL_FALSE_STRING = 'false';

    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        if (is_int($from) || is_float($from) || is_double($from)) {
            return strval($from);
        }

        if (is_bool($from) || $from === self::BOOL_TRUE_NUMBER || $from === self::BOOL_FALSE_NUMBER) {
            if ($from === self::BOOL_TRUE || $from === self::BOOL_TRUE_NUMBER) {
                return self::BOOL_TRUE_STRING;
            }
            if ($from === self::BOOL_FALSE || $from === self::BOOL_FALSE_NUMBER) {
                return self::BOOL_FALSE_STRING;
            }
        }

        if (is_string($from)) {
            return $from;
        }

        if (is_object($from) && method_exists($from, '__toString')) {
            return (string) $from;
        }

        throw new ConstraintViolationException(
            sprintf('The value "%s" could not be transformed into a string', $from),
            'not_string',
            $from
        );
    }
}
