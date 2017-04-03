<?php
/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\DI;

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
     *                      a NoResultException if a string is contained as error.
	 * @return mixed
	 */
	public function value();

	/**
	 * Get the encapsulated error.
	 *
	 * @throws LogicException   if isOK
	 * @return Exception|string
	 */
	public function error();

	/**
	 * Modify the contained value.
	 *
	 * Does nothing if !isOK.
	 *
	 * @param	\Closure    $transformation		mixed -> mixed
	 * @return	Result
	 */
	public function map(\Closure $transformation);

	/**
	 * Modify the contained value by using it to create a new result.
	 *
	 * Does nothing if !isOK. This is monadic bind.
	 *
	 * @param	\Closure    $transformation 	mixed -> Result
	 * @return Result
	 */
	public function ifOK(\Closure $transformation);
}
