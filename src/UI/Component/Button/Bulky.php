<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

/**
 * This describes a bulky button.
 */
interface Bulky extends Button, Engageable
{
	/**
	 * Get the icon or glyph the button was created with.
	 *
	 * @return ILIAS\UI\Component\Symbol\Symbol
	 */
	public function getIconOrGlyph();
}
