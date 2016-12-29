<?php

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Implementation\Component\Connector\TriggerAction;

/**
 * This action closes the modal
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class CloseAction extends TriggerAction {

//	/**
//	 * @inheritdoc
//	 */
//	public function getSupportedEvents() {
//		return array(TriggerAction::EVENT_CLICK);
//	}


	/**
	 * @inheritdoc
	 */
	public function renderJavascript($id) {
		return "$('#{$id}').modal('hide'); return false;";
	}
}