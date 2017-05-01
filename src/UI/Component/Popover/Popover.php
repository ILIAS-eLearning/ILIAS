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
	 * Get the title of the popover.
	 *
	 * @return string
	 */
	public function getTitle();

	/**
	 * Get the components representing the content of the popover.
	 *
	 * @return Component[]
	 */
	public function getContent();

	/**
	 * Get the position of the popover.
	 *
	 * @return string
	 */
	public function getPosition();

	/**
	 * Get the url returning the rendered content, if the popovers content is rendered via ajax.
	 *
	 * @return string
	 */
	public function getAsyncContentUrl();

	/**
	 * Get the same popover being rendered below or above the triggerer, based on the available space.
	 *
	 * @return Popover
	 */
	public function withVerticalPosition();

	/**
	 * Get the same popover being rendered to the left or right of the triggerer, based on the available space.
	 *
	 * @return Popover
	 */
	public function withHorizontalPosition();

	/**
	 * Get the same popover displaying a title above the content.
	 *
	 * @param string $title
	 * @return Popover
	 */
	public function withTitle($title);

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