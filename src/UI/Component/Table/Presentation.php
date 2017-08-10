<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Table;

/**
 * This describes a Presentation Table
 */
interface Presentation extends \ILIAS\UI\Component\Component {

	/**
	 * Get the title of the table.
	 *
	 * @return string
	 */
	public function getTitle();

	/**
	 * Get all rows of the table.
	 *
	 * @return ILIAS\UI\Component\Table\PresentationRow[]
	 */
	public function getRows();

	/**
	 * Get view controls to be shown in the header of the table.
	 *
	 * @return ILIAS\UI\Component\ViewControl[]
	 */
	public function getViewControls();

}
