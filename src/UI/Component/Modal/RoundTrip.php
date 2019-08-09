<?php
namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Button;

/**
 * @package ILIAS\UI\Component\Modal
 */
interface RoundTrip extends Modal
{

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
     * Get Modal like this with the provided components representing the content of the modal
     *
     * @param \ILIAS\UI\Component\Component[] $a_content
     * @return RoundTrip
     */
    public function withContent($a_content);


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
     * Get a modal like this with the provided action buttons in the footer.
     * Note that the footer always contains a cancel button closing the modal as last button in the footer (on the right).
     *
     * @param array Button\Button[] $buttons
     * @return RoundTrip
     */
    public function withActionButtons(array $buttons);

    /**
     * Get the modal like this with the provided cancel button string.
     * The closing button has "Cancel" by default
     *
     * @param string $label
     * @return RoundTrip
     */
    public function withCancelButtonLabel($label);

    /**
     * Get the signal to replace the content of this modal.
     *
     * @return \ILIAS\UI\Component\ReplaceSignal
     */
    public function getReplaceSignal();

    /**
     * Init the default signals plus extra signals like Replace
     */
    public function initSignals();
}
