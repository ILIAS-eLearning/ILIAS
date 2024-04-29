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

namespace ILIAS\Refinery;

use UnexpectedValueException;

/**
 * This trait is a convenience trait which uses `DeriveApplyToFromTransform`, `DeriveInvokeFromTransform` and `ProblemBuilder`
 * and implements some methods that are always implemented the same.
 * The main purpose of this trait is to reduce duplicated code and make it easier to implement new constraints.
 */
trait DeriveTransformWithProblem
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;
    use ProblemBuilder;

    public function transform($from)
    {
        $this->check($from);
        return $from;
    }

    public function check($value)
    {
        if (!$this->accepts($value)) {
            throw new UnexpectedValueException($this->getErrorMessage($value));
        }
    }

    public function problemWith($value): ?string
    {
        if (!$this->accepts($value)) {
            return $this->getErrorMessage($value);
        }

        return null;
    }
}
