<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\MainControls;

use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Button;
use ILIAS\UI\Component\MainControls\Slate;
use ILIAS\UI\Component\JavaScriptBindable;


/**
 * This describes the Mainbar
 */
interface Mainbar extends \ILIAS\UI\Component\Component, JavaScriptBindable
{
	/**
	 * Append an entry.
	 *
	 * @param string $id
	 * @param Button\Bulky|Slate $entry
	 * @throws InvalidArgumentException 	if $id is already taken
	 */
	public function withEntry(string $id, $entry): Mainbar;

	/**
	 * @return array <string, Button\Bulky|Slate>
	 */
	public function getEntries(): array;

	/**
	 * Append a tool-entry.
	 *
	 * @param string $id
	 * @param Button\Bulky|Slate $entry
	 * @throws InvalidArgumentException 	if $id is already taken
	 */
	public function withToolEntry(string $id, $entry): Mainbar;

	/**
	 * @return array <string, Button\Bulky|Slate>
	 */
	public function getToolEntries();

	/**
	 * @throws InvalidArgumentException 	if $active is not an element-identifier in entries
	 */
	public function withActive(string $active): Mainbar;

	/**
	 * @return string|null
	 */
	public function getActive();

	/**
	 * Label for the tools-trigger.
	 */
	public function withToolsLabel(string $label): Mainbar;

	public function getToolsLabel(): string;

	public function getEntryClickSignal(): Signal;

	public function getToolsClickSignal(): Signal;

	public function getToolsRemovalSignal(): Signal;

	public function getDisengageAllSignal(): Signal;

}
