<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Item;

/**
 * This describes how an item could be modified during construction of UI.
 */
interface Item extends \ILIAS\UI\Component\Component {
	/**
	 * Gets the title of the item 
	 *
	 * @return string
	 */
	public function getTitle();
}
