<?php
/* Copyright (c) 2018 - Richard Klees <richard.klees@concepts-and-training.de> - Extended GPL, see LICENSE */

namespace ILIAS\KioskMode;

use ILIAS\UI;

/**
 * Build a nested table of contents for the view.
 */
interface TOCBuilder {
	/**
	 * Finish building the TOC.
	 *
	 * @return	ControlBuilder|TOCBuilder depending on the nesting level.
	 */
	public function endTOC(string $command);

	/**
	 * Build a sub tree in the TOC.
	 *
	 * If a parameter is provided, the node in the TOC can be accessed itself.
	 */
	public function beginTOC($label, int $parameter = null) : TOCBuilder;

	/**
	 * Build an entry in the TOC.
	 *
	 * The parameter will be appended to the command when updating the state.
	 */
	public function entry(string $label, int $parameter);
}

