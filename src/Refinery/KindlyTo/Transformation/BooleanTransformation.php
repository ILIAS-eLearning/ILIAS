<?php declare(strict_types=1);

/* Copyright (c) 2020 Luka K. A. Stocker, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\ConstraintViolationException;

class BooleanTransformation implements Transformation
{
    const BOOL_TRUE_STRING = 'true';
    const BOOL_FALSE_STRING = 'false';
    const BOOL_TRUE_NUMBER = 1;
    const BOOL_FALSE_NUMBER = 0;
    const BOOL_TRUE_NUMBER_STRING = '1';
    const BOOL_FALSE_NUMBER_STRING = '0';

    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        if (is_bool($from)) {
            return $from;
        }

        if (
            $from === self::BOOL_TRUE_NUMBER
            || $from === self::BOOL_TRUE_NUMBER_STRING
            || (is_string($from) && mb_strtolower($from) === self::BOOL_TRUE_STRING)
        ) {
            return true;
        }

        if (
            $from === self::BOOL_FALSE_NUMBER
            || $from === self::BOOL_FALSE_NUMBER_STRING
            || (is_string($from) && mb_strtolower($from) === self::BOOL_FALSE_STRING)
        ) {
            return false;
        }

        throw new ConstraintViolationException(
            sprintf('The value "%s" could not be transformed into boolean.', var_export($from, true)),
            'not_boolean',
            $from
        );
    }
}
