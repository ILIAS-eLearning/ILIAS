<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Menu;

use \ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * This describes a Level of Drilldowns
 */
interface Sub extends Component, JavaScriptBindable
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
	 * Get the Entries of this level.
	 */
	public function getEntries(): array;

	/**
	 * Set entries of this Submenu.
	 * @param array<Button|Submenu> 	$entries
	 */
	public function withEntries(array $entries): Sub;

	/**
	 * Add an entry to the level.
	 * @param Button|Submenu 	$entry
	 */
	public function withAdditionalEntry($entry): Sub;

	/**
	 * Configure this Submenu to be active when the drilldown is loaded.
	 */
	public function withInitiallyActive(): Sub;

	/**
	 * Is this initially active?
	 */
	public function isInitiallyActive(): bool;
}