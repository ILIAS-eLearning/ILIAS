<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input;

/**
 * This is how a factory for inputs looks like.
 */
interface Factory {
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      TBD
	 *   composition: >
	 *      TBD
	 *   effect: >
	 *      TBD
	 * context: >
	 *   TBD
	 *
	 * rules: []
	 *
	 * ---
	 *
	 * @param	string      $label
	 * @param	string|null $byline
	 * @return	\ILIAS\UI\Component\Input\Text
	 */
	public function text($label, $byline = null);
}
