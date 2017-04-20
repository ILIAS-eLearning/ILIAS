<?php
/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data;

/**
 * Builds data types.
 */
class Factory {
	/**
 	 * Get an ok result.
	 *
	 * @param  mixed  $value
	 * @return Result 
	 */
	public function ok($value);

	/**
	 * Get an error result.
	 *
	 * @param  string|\Exception $error
	 * @return Result
	 */
	public function error($e);
}
