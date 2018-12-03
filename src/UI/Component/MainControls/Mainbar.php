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
	public function withAdditionalEntry(string $id, $entry): Mainbar;

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
	public function withAdditionalToolEntry(string $id, $entry): Mainbar;

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
	 * This sets the label for the tools-trigger.
	 */
	public function withToolsLabel(string $label): Mainbar;

	/**
	 * This returns the label of the tools-trigger.
	 */
	public function getToolsLabel(): string;

	/**
	 * Get the signal that is triggered when any entry in the bar is clicked.
	 */
	public function getEntryClickSignal(): Signal;

	/**
	 * Get the signal that is triggered when any entry in the tools-button is clicked.
	 */
	public function getToolsClickSignal(): Signal;

	/**
	 * Get the signal that is used for removing a tool.
	 */
	public function getToolsRemovalSignal(): Signal;

	/**
	 * This signal disengages all slates when triggered.
	 */
	public function getDisengageAllSignal(): Signal;

}
