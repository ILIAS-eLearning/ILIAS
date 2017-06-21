<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Cockpit\Bar;

/**
 * Cockpit Bar
 * @package ILIAS\UI\Component\Cockpit
 */
interface Bar extends \ILIAS\UI\Component\Component {

	/**
	 * Get all entries in the bar
	 *
	 * @return \Entry[]
	 */
	public function entries();

}
