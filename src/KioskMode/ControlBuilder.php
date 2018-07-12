<?php
/* Copyright (c) 2018 - Richard Klees <richard.klees@concepts-and-training.de> - Extended GPL, see LICENSE */

namespace ILIAS\KioskMode;

use ILIAS\UI;

/**
 * Build controls for the view.
 */
interface ControlBuilder {
	/**
	 * Build an exit button.
	 *
	 * @throws \LogicException if view wants to introduce a second exit button.
	 */
	public function exitButton(string $command) : ControlBuilder;

	/*
	 * Build a next button.
	 *
	 * @throws \LogicException if view wants to introduce a second next button.
	 */
	public function nextButton(string $command, int $parameter) : ControlBuilder;

	/**
	 * Build a previous button.
	 *
	 * @throws \LogicException if view wants to introduce a second previous button.
	 */
	public function previousButton(string $command, int $parameter) : ControlBuilder;

	/**
	 * Build a done button.
	 *
	 * @throws \LogicException if view wants to introduce a second previous button.
	 */
	public function doneButton(string $command, int $parameter) : ControlBuilder;

	/**
	 * Build a generic button.
	 */
	public function button(string $label, string $command, int $parameter) : ControlBuilder;

	/**
	 * Build a toggle.
	 */
	public function toggle(string $label, string $on_command, string $off_command) : ControlBuilder;

	/**
	 * Build a locator.
	 *
	 * The command will be enhanced with a parameter defined in the locator builder.
	 *
	 * @throws \LogicException if view wants to introduce a second locator.
	 */
	public function beginLocator(string $command) : LocatorBuilder;

	/**
	 * Build a nested table of contents.
	 *
	 * The command will be enhanced with a parameter defined here on in the locator builder.
	 *
	 * If a parameter is defined here, the view provides an overview-page.
	 *
	 * @throws \LogicException if view wants to introduce a second TOC.
	 */
	public function beginTOC(string $command, int $parameter = null) : TOCBuilder;
}

