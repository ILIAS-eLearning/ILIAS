<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Drilldown;

use \ILIAS\UI\Component\Component;

/**
 * This describes a Drilldown Control
 */
interface Drilldown extends Component
{
	/**
	 * Configure the number of backlinks to be shown (default=1).
	 */
	public function withStackingLength(int $stacking): Drilldown;

	/**
	 * Configure the backlinks to feature this symbol.
	 */
	public function withGeneralBackIcon($icon_or_glyph): Drilldown;

	/**
	 * @return Icon|Glyph|null
	 */
	public function getGeneralBackIcon();

	/**
	 * Get the label for the root-level of Drilldown.
	 */
	public function getLabel(): string;

	/**
	 * @return Icon|Glyph|null
	 */
	public function getIconOrGlyph();

	/**
	 * Add an entry to the Drilldown.
	 * @param Button|Level 	$entry
	 */
	public function withAdditionalEntry($entry): Drilldown;

	/**
	 * Get the Entries of this drilldown.
	 */
	public function getEntries(): array;
}