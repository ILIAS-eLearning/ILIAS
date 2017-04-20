<?php
/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data;

/**
 * Builds data types.
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class Factory {
	/**
 	 * Get an ok result.
	 *
	 * @param  mixed  $value
	 * @return Result 
	 */
	public function ok($value) {
		return new Results\Ok($value);
	}

	/**
	 * Get an error result.
	 *
	 * @param  string|\Exception $error
	 * @return Result
	 */
	public function error($e) {
		return new Results\Error($e);
	}
}
