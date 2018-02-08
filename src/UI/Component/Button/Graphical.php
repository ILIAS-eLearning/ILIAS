<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */
namespace ILIAS\UI\Component\Button;

/**
 * This describes an graphical button.
 */
interface Graphical extends Button {

	/**
	 * @return ILIAS\UI\Component\Icon\Icon | \ILIAS\UI\Component\Glyph\Glyph
	 */
	public function getIconOrGlyph();

	/**
	 * @param 	bool 	$state
	 * @return 	Graphical
	 */
	public function withEngagedState($state);

	/**
	 * @return 	bool
	 */
	public function isEngaged();

}