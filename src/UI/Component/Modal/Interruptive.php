<?php

namespace ILIAS\UI\Component\Modal;

/**
 * Interface Interruptive
 *
 * @package ILIAS\UI\Component\Modal
 */
interface Interruptive extends Modal
{

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
     * Get a modal like this submitting the form to the given form action
     *
     * @param string $form_action
     *
     * @return Interruptive
     */
    public function withFormAction($form_action);


    /**
     * Get a modal like this listing the given items in the content section below the message.
     * The IDs of the interruptive items are sent via POST to the form action of this modal.
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
