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

namespace ILIAS\Refinery;

use ILIAS\Data\Result;
use UnexpectedValueException;

/**
 * A constraint encodes some resrtictions on values.
 *
 * Constraints MUST NOT modify the supplied value.
 */
interface Constraint extends Transformation
{
    /**
     * Checks the provided value.
     *
     * Should not throw if accepts($value).
     *
     * @param mixed $value
     * @return null
     * @throws UnexpectedValueException if value does not comply with encoded constraint.
     */
    public function check($value);

    /**
     * Tells if the provided value complies.
     *
     * @param mixed $value
     * @return bool
     */
    public function accepts($value): bool;

    /**
     * Tells what the problem with the provided value is.
     *
     * Should return null if accepts($value).
     *
     * @param mixed $value
     * @return string|null
     */
    public function problemWith($value): ?string;

    /**
     * Restricts a Result.
     *
     * Must do nothing with the result if $result->isError().
     * Must replace the result with an error according to problemWith() if
     * !accepts($result->value()).
     *
     * @param Result $result
     * @return Result
     */
    public function applyTo(Result $result): Result;

    /**
     * Get a constraint like this one with a builder for a custom error
     * message.
     *
     * problemWith() must return an error message according to the new builder for
     * the new constraint.
     *
     * The builder needs to be callable that takes two parameters:
     *
     *
     * @param callable $builder
     * @return self
     */
    public function withProblemBuilder(callable $builder): self;
}
