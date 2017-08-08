<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Table;

/**
 * This describes a Row use in Presentation Table
 */
interface PresentationRow extends \ILIAS\UI\Component\Component {

	/**
	 * Get the name of the field to be used as title.
	 *
	 * @return	string
	 */
	public function getTitleField();

}
