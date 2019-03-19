<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Drilldown;

use \ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * This describes a Drilldown Menu Control
 */
interface Menu extends Component, JavaScriptBindable
{
	/**
	 * Get the label for the root-level of Drilldown.
	 */
	public function getLabel(): string;

	/**
	 * @return Icon|Glyph|null
	 */
	public function getIconOrGlyph();

	/**
	 * Get the Entries of this drilldown.
	 */
	public function getEntries(): array;

	/**
	 * Set entries of this Menu.
	 * @param array<Button|Submenu> 	$entries
	 */
	public function withEntries(array $entries): Menu;

	/**
	 * Add an entry to the Drilldown.
	 * @param Button|Submenu 	$entry
	 */
	public function withAdditionalEntry($entry): Menu;
}