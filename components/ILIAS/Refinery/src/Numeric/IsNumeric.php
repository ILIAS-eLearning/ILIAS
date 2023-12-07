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

namespace ILIAS\Refinery\Numeric;

use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\ConstraintViolationException;

class IsNumeric implements Constraint
{
    public function problemWith($value)
    {
        if ('' === $value) {
            return new ConstraintViolationException('Not numeric (empty string)', 'not_numeric_empty_string');
        }
        return is_numeric($value) ? null : new ConstraintViolationException('Not numeric', 'not_numeric');
    }
}
