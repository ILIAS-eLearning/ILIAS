<?php

namespace ILIAS\UI\Implementation\Component\Connector;

use ILIAS\UI\Implementation\Component\Connector\ComponentConnection;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class Factory implements \ILIAS\UI\Component\Connector\Factory {


	/**
	 * @inheritdoc
	 */
	public function onClick(\ILIAS\UI\Component\Component $triggerer, \ILIAS\UI\Component\Connector\TriggerAction $action) {
		return new ComponentConnection($triggerer, $action, TriggerAction::EVENT_CLICK);
	}


	/**
	 * @inheritdoc
	 */
	public function onHover(\ILIAS\UI\Component\Component $triggerer, \ILIAS\UI\Component\Connector\TriggerAction $action) {
		return new ComponentConnection($triggerer, $action, TriggerAction::EVENT_HOVER);
	}


	/**
	 * @inheritdoc
	 */
	public function onChange(\ILIAS\UI\Component\Component $triggerer, \ILIAS\UI\Component\Connector\TriggerAction $action) {
		return new ComponentConnection($triggerer, $action, TriggerAction::EVENT_DBLCLICK);
	}
}