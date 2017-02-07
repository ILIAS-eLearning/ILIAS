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
     * Get the modal with the given button as action
     *
     * @param Button\Button $button
     * @return Interruptive
     */
    public function withActionButton(Button\Button $button);


    /**
     * Get the modals action button
     *
     * @return Button\Standard $button
     */
    public function getActionButton();

}
