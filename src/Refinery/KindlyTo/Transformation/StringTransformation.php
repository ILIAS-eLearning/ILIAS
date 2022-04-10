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

class StringTransformation implements Transformation
{
    private const BOOL_TRUE = true;
    private const BOOL_FALSE = false;
    private const BOOL_TRUE_NUMBER = 1;
    private const BOOL_FALSE_NUMBER = 0;
    private const BOOL_TRUE_STRING = 'true';
    private const BOOL_FALSE_STRING = 'false';

    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /**
     * @inheritDoc
     */
    public function transform($from) : string
    {
        if (is_int($from) || is_float($from)) {
            return (string) $from;
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
            sprintf('The value "%s" could not be transformed into a string', var_export($from, true)),
            'not_string',
            $from
        );
    }
}
