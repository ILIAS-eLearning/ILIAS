<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Table;

/**
 * This describes a Presentation Table
 */
interface Presentation extends \ILIAS\UI\Component\Component {

	/**
	 * Get the title of the Table.
	 *
	 * @return	string
	 */
	public function getTitle();

}