<?php
namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Onloadable;
use ILIAS\UI\Component\Triggerable;

/**
 * This describes commonalities between the different modals
 */
interface Modal extends Component, JavaScriptBindable, Triggerable, Onloadable {

	/**
	 * Get the signal to show this modal in the frontend
	 *
	 * Possible options when triggering this signal:
	 *   backdrop: Includes a modal-backdrop element. Alternatively, specify static
	 *             for a backdrop which doesn't close the modal on click. <bool|string> : true
	 *   keyboard: Closes the modal when escape key is pressed. <bool> : true
	 *   ajaxUrl: Load the modal's content via ajax before showing the modal. <string> : ''
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
