<?php

declare(strict_types=1);

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

class FloatTransformation implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;
    private const REG_STRING = '/^\s*(-?(0|([1-9]\d*)))([.,]\d*)?\s*$/';
    private const REG_STRING_FLOATING = '/^\s*-?\d+[eE]-?\d+\s*$/';

    /**
     * @inheritDoc
     */
    public function transform($from): float
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
