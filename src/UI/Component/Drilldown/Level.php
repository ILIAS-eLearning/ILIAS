<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Drilldown;

use \ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * This describes a Level of Drilldowns
 */
interface Level extends Component, JavaScriptBindable
{
	/**
	 * Get the label for this level.
	 */
	public function getLabel(): string;

	/**
	 * @return Icon|Glyph|null
	 */
	public function getIconOrGlyph();

	/**
	 * Add an entry to the level.
	 * @param Button|Level 	$entry
	 */
	public function withAdditionalEntry($entry): Level;

	/**
	 * Get the Entries of this level.
	 */
	public function getEntries(): array;

}