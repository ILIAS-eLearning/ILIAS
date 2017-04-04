<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation;

use ILIAS\Data\Result;

/**
 * A restriction encodes some constraint on values.
 *
 * Restrictions MUST NOT modify the supplied value.
 */
interface Restriction {
	/**
	 * Checks the provided value.
     *
	 * Should not throw if appliesTo($value).
	 *
	 * @throws  \InvalidArgumentException if value does not comply with encoded restriction.
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
	public function appliesTo($value);

	/**
	 * Tells what the problem with the provided value is.
	 *
	 * Should return null if appliesTo($value).
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
	 * !appliesTo($result->value()).
	 *
	 * @param	Result $value
	 * @return	Result
	 */
	public function restrict(Result $result);

	/**
	 * Get a restriction like this one with a builder for a custom error
	 * message.
	 *
	 * problemWith() must return an error message according to the new builder for
	 * the new restriction.
	 *
	 * @param	callable  $builder  mixed -> string
	 * @return  Restriction
	 */
	public function withProblemBuilder(callable $builder);
}
