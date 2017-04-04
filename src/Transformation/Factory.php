<?php
/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Transformation;

/**
 * Factory for basic transformations.
 */
interface Factory {
	/**
	 * Create a custom transformation.
	 *
	 * @param	callable $f	mixed -> mixed
	 * @return  Transformation
	 */
	public function custom(callable $f);
}
