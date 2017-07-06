<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Form;

/**
 * This is how a factory for forms looks like.
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
	 * @param	$string		$post_to
	 * @param	\ILIAS\UI\Component\Input\Input[]	$inputs
	 * @return	\ILIAS\UI\Component\Input\Text
	 */
	public function standard(array $inputs);
}
