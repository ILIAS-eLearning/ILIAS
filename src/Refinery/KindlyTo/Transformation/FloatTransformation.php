<?php declare(strict_types=1);

/* Copyright (c) 2020 Luka K. A. Stocker, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\ConstraintViolationException;

class FloatTransformation implements Transformation
{
    private const REG_STRING = '/^\s*(0|(-?[1-9]\d*([.,]\d+)?))\s*$/';
    private const REG_STRING_FLOATING = '/^\s*-?\d+[eE]-?\d+\s*$/';

    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        if ($from !== INF && $from !== -INF && is_float($from) && !is_nan($from)) {
            return $from;
        }

        if (is_int($from)) {
            return (float) $from;
        }

        if (is_bool($from)) {
            return (float) $from;
        }

        if (is_string($from)) {
            $preg_match_string = preg_match(self::REG_STRING, $from, $RegMatch);
            if ($preg_match_string) {
                return (float) str_replace(',', '.', $from);
            }

            $preg_match_floating_string = preg_match(self::REG_STRING_FLOATING, $from, $RegMatch);
            if ($preg_match_floating_string) {
                return (float) $from;
            }

            throw new ConstraintViolationException(
                sprintf('The value "%s" could not be transformed into an float', var_export($from, true)),
                'not_float',
                $from
            );
        }

        throw new ConstraintViolationException(
            sprintf('The value "%s" could not be transformed into an float', var_export($from, true)),
            'not_float',
            $from
        );
    }
}
