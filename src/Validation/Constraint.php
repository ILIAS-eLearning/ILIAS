<?php
/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation;

use ILIAS\Data\Result;

/**
 * A constraint encodes some resrtictions on values.
 *
 * Constraints MUST NOT modify the supplied value.
 */
interface Constraint
{
    /**
     * Checks the provided value.
     *
     * Should not throw if accepts($value).
     *
     * @throws  \UnexpectedValueException if value does not comply with encoded constraint.
     * @param   mixed  $value
     * @return  null
     */
    public function check($value);

    /**
     * Tells if the provided value complies.
     *
     * @param   mixed $value
     * @return  bool
     */
    public function accepts($value);

    /**
     * Tells what the problem with the provided value is.
     *
     * Should return null if accepts($value).
     *
     * @param   mixed $value
     * @return  string|null
     */
    public function problemWith($value);

    /**
     * Restricts a Result.
     *
     * Must do nothing with the result if $result->isError().
     * Must replace the result with an error according to problemWith() if
     * !accepts($result->value()).
     *
     * @param   Result $result
     * @return  Result
     */
    public function restrict(Result $result);

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
     * @param   callable  $builder
     * @return  Constraint
     */
    public function withProblemBuilder(callable $builder);
}
