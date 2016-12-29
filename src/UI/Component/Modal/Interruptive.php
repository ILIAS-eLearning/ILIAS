<?php

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Button;

/**
 * Interface Interruptive
 *
 * @package ILIAS\UI\Component\Modal
 */
interface Interruptive extends Modal
{

	/**
	 * Get the message of this modal, displayed above the items
	 *
	 * @return string
	 */
	public function getMessage();

	/**
	 * Get the title of this modal
	 *
	 * @return string
	 */
	public function getTitle();

    /**
     * Get a modal like this with the given action button in the footer
     *
     * @param Button\Button $button
     * @return Interruptive
     */
    public function withActionButton(Button\Button $button);


	/**
	 * Get a modal like this with the given title
	 *
	 * @param string $title
	 * @return Interruptive
	 */
    public function withTitle($title);


	/**
	 * Get a modal like this with the given message
	 *
	 * @param string $message
	 * @return Interruptive
	 */
    public function withMessage($message);

    /**
     * Get the action button in the footer
     *
     * @return Button\Standard $button
     */
    public function getActionButton();

}
