<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Button;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\ReplaceSignal;

/**
 * @package ILIAS\UI\Component\Modal
 */
interface RoundTrip extends Modal
{
    /**
     * Get the title of the modal
     */
    public function getTitle() : string;

    /**
     * Get the components representing the content of the modal
     *
     * @return Component[]
     */
    public function getContent() : array;

    /**
     * Get Modal like this with the provided components representing the content of the modal
     *
     * @param Component[] $content
     */
    public function withContent(array $content) : RoundTrip;

    /**
     * Get all action buttons in the footer of the modal
     *
     * @return Button\Button[]
     */
    public function getActionButtons() : array;

    /**
     * Get the label of the cancel button in the footer, as language key
     */
    public function getCancelButtonLabel() : string;

    /**
     * Get a modal like this with the provided action buttons in the footer.
     * Note that the footer always contains a cancel button closing the modal as last button in the footer (on the right).
     *
     * @param array Button\Button[] $buttons
     */
    public function withActionButtons(array $buttons) : RoundTrip;

    /**
     * Get the modal like this with the provided cancel button string.
     * The closing button has "Cancel" by default
     */
    public function withCancelButtonLabel(string $label) : RoundTrip;

    /**
     * Get the signal to replace the content of this modal.
     */
    public function getReplaceSignal() : ReplaceSignal;

    /**
     * Init the default signals plus extra signals like Replace
     */
    public function initSignals() : void;
}
