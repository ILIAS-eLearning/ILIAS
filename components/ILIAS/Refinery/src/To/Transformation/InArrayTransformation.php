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

namespace ILIAS\Refinery\To\Transformation;

use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\DeriveTransformWithProblem;
use ILIAS\Refinery\ProblemBuilder;
use UnexpectedValueException;
use ILIAS\Language\Language;

/**
 * Validates that the value to be transformed is in the set given to this transformation.
 * There is no constraint on the type of the elements in the set.
 *
 * @template A
 * @implements Constraint<A, A>
 */
class InArrayTransformation implements Constraint
{
    use DeriveTransformWithProblem;

    /** @var string|callable */
    private $error;

    /**
     *
     * @param list<A> $valid_members
     */
    public function __construct(private readonly array $valid_members, private readonly Language $lng)
    {
        if (!array_is_list($this->valid_members)) {
            throw new ConstraintViolationException('The valid members MUST be a list.', 'array_not_a_list');
        }
        $this->error = sprintf(
            'The value MUST be one of: %s.',
            join(', ', array_map(json_encode(...), $this->valid_members))
        );
    }

    public function accepts($value): bool
    {
        return in_array($value, $this->valid_members, true);
    }

    public function getError()
    {
        return $this->error;
    }
}
