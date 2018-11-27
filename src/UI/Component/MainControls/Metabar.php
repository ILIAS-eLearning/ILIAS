<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\MainControls;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * This describes the Metabar.
 */
interface Metabar extends Component, JavaScriptBindable
{
	public function getLogo(): Image;

	/**
	 * Append an entry.
	 *
	 * @param string $id
	 * @param Bulky|Slate $entry
	 * @throws InvalidArgumentException 	if $id is already taken
	 */
	public function withEntry(string $id, $entry): Metabar;

	/**
	 * @return array <string, Bulky|Slate>
	 */
	public function getEntries(): array;

	public function getEntryClickSignal(): Signal;
}
