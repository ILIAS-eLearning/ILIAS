<?php

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Modal\CloseAction;
use ILIAS\UI\Implementation\Component\Modal\ShowAction;

/**
 * This describes commonalities between the different modals
 */
interface Modal extends Component, JavaScriptBindable
{

    /**
     * Get the action to show this modal in the frontend
     *
     * @return ShowAction
     */
    public function getShowAction();


    /**
     * Get the action to close this modal in the frontend
     *
     * @return CloseAction
     */
    public function getCloseAction();

}
