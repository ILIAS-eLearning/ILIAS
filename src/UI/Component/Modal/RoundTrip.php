<?php

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Button;

/**
 * @package ILIAS\UI\Component\Modal
 */
interface RoundTrip extends Modal {

	/**
	 * Get the title of the modal
	 *
	 * @return string
	 */
	public function getTitle();


	/**
	 * Get the components representing the content of the modal
	 *
	 * @return \ILIAS\UI\Component\Component[]
	 */
	public function getContent();


	/**
	 * Get all action buttons in the footer of the modal
	 *
	 * @return \ILIAS\UI\Component\Button\Button[]
	 */
	public function getActionButtons();


	/**
	 * Get the label of the cancel button in the footer, as language key
	 *
	 * @return string
	 */
	public function getCancelButtonLabel();


	/**
	 * Get a modal like this with another title
	 *
	 * @param string $title
	 * @return RoundTrip
	 */
	public function withTitle($title);


	/**
	 * Get a modal like this with another content
	 *
	 * @param \ILIAS\UI\Component\Component|\ILIAS\UI\Component\Component[] $content
	 * @return RoundTrip
	 */
	public function withContent($content);


	/**
	 * Get a modal like this with the provided action buttons in the footer.
	 * Note that the footer always contains a cancel button as last button in the footer (on the right).
	 *
	 * @param array Button\Button[] $buttons
	 * @return RoundTrip
	 */
	public function withActionButtons(array $buttons);
}
