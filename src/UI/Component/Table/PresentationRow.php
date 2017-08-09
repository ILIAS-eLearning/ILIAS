<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Table;

use ILIAS\UI\Component\Triggerable;

/**
 * This describes a Row used in Presentation Table
 */
interface PresentationRow extends \ILIAS\UI\Component\Component, Triggerable {

	/**
	 * Get the name of the field to be used as title.
	 *
	 * @return	string
	 */
	public function getTitleField();

}
