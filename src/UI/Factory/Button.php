<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Factory;

/**
 * This is how a factory for buttons looks like.
 */
interface Button {
	/**
	 * description:
	 *   purpose:
	 *       The Status counter is used to display information about the
	 *       total number of some items like users active on the system or total
	 *       amount of comments.
	 *   composition:
	 *       The Status Counter is a non-obstrusive Counter.
	 *
	 * rules:
	 *   style:
	 *       1: The Status Counter MUST be displayed on the lower right of the item
	 *          it accompanies.
	 *       2: The Status Counter SHOULD have a non-obstrusive background color,
	 *          such as grey.
	 *
	 * @param   int         $amount
	 * @return  \ILIAS\UI\Component\Button
	 */
	public function def();
}
