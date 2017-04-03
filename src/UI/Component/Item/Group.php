<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Item;

/**
 * This describes how an item group could be modified during construction of UI.
 */
interface Group extends \ILIAS\UI\Component\Component {
	/**
	 * Gets the title of the group. 
	 *
	 * @return string
	 */
	public function getTitle();
}
