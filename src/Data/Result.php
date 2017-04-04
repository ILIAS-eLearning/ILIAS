<?php
/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data;

/**
 * A result encapsulates a value with the possibility of a failure.
 */
interface Result {
	/**
 	 * Get to know if the result is ok.
	 *
	 * @return bool
	 */
	public function isOK();

	/**
	 * Get the encapsulated value.
	 *
	 * @throws Exception    if !isOK, will either throw the contained exception or
     *                      a NotOKException if a string is contained as error.
	 * @return mixed
	 */
	public function value();

	/**
	 * Get to know if the result is an error.
	 *
	 * @return bool
	 */
	public function isError();

	/**
	 * Get the encapsulated error.
	 *
	 * @throws LogicException   if isOK
	 * @return Exception|string
	 */
	public function error();

	/**
	 * Get the encapsulated value or the supplied default if result is an error.
	 *
	 * @param  default
	 * @return mixed
	 */
	public function valueOr($default);

	/**
	 * Modify the contained value.
	 *
	 * Does nothing if !isOK.
	 *
	 * @param	callable $f mixed -> mixed
	 * @return	Result
	 */
	public function map(callable $f);

	/**
	 * Modify the contained value by using it to create a new result.
	 *
	 * Does nothing if !isOK. This is monadic bind.
	 *
	 * @param	callable $f mixed -> Result
	 * @return  Result
	 */
	public function then(callable $f);
}
