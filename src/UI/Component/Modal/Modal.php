<?php

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Triggerable;
use ILIAS\UI\Implementation\Component\Modal\CloseAction;
use ILIAS\UI\Implementation\Component\Modal\ShowAction;
use ILIAS\UI\Implementation\Component\Modal\ShowAsyncAction;

/**
 * This describes commonalities between the different modals
 */
interface Modal extends Component, JavaScriptBindable, Triggerable {

	/**
	 * Get the signal to show this modal in the frontend
	 *
	 * @return string
	 */
	public function getShowSignal();


	/**
	 * Get the signal to close this modal in the frontend
	 *
	 * @return string
	 */
	public function getCloseSignal();

}
