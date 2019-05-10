<?php
/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Transformation;
use ILIAS\Data\Result;

/**
 * A transformation is a function from one datatype to another.
 *
 * It MUST NOT perform any sideeffects, i.e. it must be morally impossible to observe
 * how often the transformation was actually performed. It MUST NOT touch the provided
 * value, i.e. it is allowed to create new values but not to modify existing values.
 * This would be an observable sideeffect.
 */
interface Transformation {
	/**
	 * Perform the transformation.
	 * Please use this for transformations. It's more performant than calling invoke.
	 *
	 * @throws \InvalidArgumentException  if the argument could not be transformed
	 * @param  mixed  $from
	 * @return mixed
	 */
	public function transform($from);

	/**
	 * Perform the transformation and reify possible failures.
	 *
	 * If `$data->isError()`, the method MUST do nothing. It MUST transform the value
	 * in `$data` like it would transform $data provided to `transform`. It must reify
	 * every exception thrown in this process by returning a `Result` that `isError()`
	 * and contains the exception that happened.
	 *
	 * If you simply need to implement a transformation you most probably want to
	 * implement transform and derive this via the trait `DeriveTransformationInterface`.
	 *
	 * If you simply want to call the transformation, you most probably want to use
	 * `transform`, since it simply throws exceptions that occurred while doing the
	 * transformation.
	 *
	 * If you are implementing some entity that performs processing of input data at
	 * some boundary, the reification of exceptions might help you to write cleaner
	 * code.
	 *
	 * @param Result $data
	 * @return Result
	 */
	public function applyTo(Result $data) : Result;

	/**
	 * Transformations should be callable. This MUST do the same as transform.
	 *
	 * @throws \InvalidArgumentException  if the argument could not be transformed
	 * @param  mixed  $from
	 * @return mixed
	 */
	public function __invoke($from);
}
