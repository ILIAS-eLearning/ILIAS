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

namespace ILIAS\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\Transformable;
use ILIAS\Refinery\ConstraintViolationException;

class BooleanTransformation implements Transformable
{
    private const BOOL_TRUE_STRING = 'true';
    private const BOOL_FALSE_STRING = 'false';
    private const BOOL_TRUE_NUMBER = 1;
    private const BOOL_FALSE_NUMBER = 0;
    private const BOOL_TRUE_NUMBER_STRING = '1';
    private const BOOL_FALSE_NUMBER_STRING = '0';

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
