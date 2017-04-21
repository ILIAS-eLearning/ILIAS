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
	 * Get the components representing the content of the popover
	 *
	 * @return Component[]
	 */
	public function getContent();

	/**
	 * Get the position of the popover: auto, vertical or horizontal
	 *
	 * @return string
	 */
	public function getPosition();

	/**
	 * Get the url returning the rendered content, if the popovers content is rendered via ajax
	 *
	 * @return string
	 */
	public function getAsyncContentUrl();

	/**
	 * Get the same popover being rendered at the specified position:
	 *
	 * auto: Popover placement is determined automatically based on the available space
	 * vertical: Popover is placed below or above the triggerer, based on the available space
	 * horizontal: Popover is placed right or left of the triggerer, based on the available space
	 *
	 * @param string $position auto|vertical|horizontal
	 * @return Popover
	 */
	public function withPosition($position);

	/**
	 * Get a popover like this who's content is rendered via ajax by the given $url before the popover is shown
	 *
	 * Means: After the show signal has been triggered but before the popover is displayed to the user,
	 * an ajax request is sent to this url. The request MUST return the rendered content for the popover.
	 *
	 * @param string $url
	 * @return $this
	 */
	public function withAsyncContentUrl($url);

	/**
	 * Get the signal to show this popover in the frontend
	 *
	 * @return Signal
	 */
	public function getShowSignal();
}