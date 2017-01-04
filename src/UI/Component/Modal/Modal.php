<?php

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Modal\CloseAction;
use ILIAS\UI\Implementation\Component\Modal\ShowAction;
use ILIAS\UI\Implementation\Component\Modal\ShowAsyncAction;

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


	/**
	 * Get the action to show this modal in the frontend, after the complete
	 * modal content has been loaded via ajax by the given URL
	 *
	 * @param string $ajax_url
	 *
	 * @return ShowAsyncAction
	 */
    public function getShowAsyncAction($ajax_url);

}
