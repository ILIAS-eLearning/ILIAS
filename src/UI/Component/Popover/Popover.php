<?php
namespace ILIAS\UI\Component\Popover;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Triggerable;

/**
 * Describes the Popover component
 */
interface Popover extends Component, Triggerable {

	/**
	 * Get the title of the popover
	 *
	 * @return string
	 */
	public function getTitle();

	/**
	 * Get the text (content) of the popover
	 *
	 * @return string
	 */
	public function getText();

	/**
	 * Get the position of the popover: top, bottom, left, right or auto
	 *
	 * @return string
	 */
	public function getPosition();

	/**
	 * Get the signal to show this popover in the frontend
	 *
	 * @return Signal
	 */
	public function getShowSignal();
}