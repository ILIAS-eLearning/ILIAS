<?php

namespace ILIAS\UI\Component\Modal;

/**
 * Interface Interruptive
 *
 * @package ILIAS\UI\Component\Modal
 */
interface Interruptive extends Modal {

	/**
	 * Get the message of this modal, displayed below the modals title
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
	 * Get a modal like this with the given title
	 *
	 * @param string $title
	 *
	 * @return Interruptive
	 */
	public function withTitle($title);


	/**
	 * Get a modal like this with the given message displayed in the content section
	 *
	 * @param string $message
	 *
	 * @return Interruptive
	 */
	public function withMessage($message);


	/**
	 * Get a modal like this submitting the form to the given form action
	 *
	 * @param string $form_action
	 *
	 * @return Interruptive
	 */
	public function withFormAction($form_action);


	/**
	 * Get a modal like this listing the given items in the content section below the message.
	 * The keys of the passed array should contain a unique identifier for the items, the value a title
	 * or description. The keys are sent via POST to the form action of the modal.
	 *
	 * @param InterruptiveItem[] $items
	 *
	 * @return Interruptive
	 */
	public function withAffectedItems(array $items);


	/**
	 * Get the label of the action button in the footer
	 *
	 * @return string
	 */
	public function getActionButtonLabel();


	/**
	 * Get the label of the cancel button in the footer
	 *
	 * @return string
	 */
	public function getCancelButtonLabel();


	/**
	 * Return the affected items listed in the content by this modal
	 *
	 * @return InterruptiveItem[]
	 */
	public function getAffectedItems();


	/**
	 * Get the form action where the action button is sending the IDs of the affected items
	 *
	 * @return string
	 */
	public function getFormAction();

}
