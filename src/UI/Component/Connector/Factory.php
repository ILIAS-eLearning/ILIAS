<?php

namespace ILIAS\UI\Component\Connector;

use ILIAS\UI\Implementation\Component\Connector\TriggerAction;

interface Factory {


	/**
	 * Trigger an action of a component by clicking on the triggerer component
	 *
	 * @param \ILIAS\UI\Component\Component               $triggerer
	 * @param \ILIAS\UI\Component\Connector\TriggerAction $action
	 *
	 * @return ComponentConnection
	 */
	public function onClick(\ILIAS\UI\Component\Component $triggerer, \ILIAS\UI\Component\Connector\TriggerAction $action);

	/**
	 * Trigger an action of a component by hovering over the triggerer component
	 *
	 * @param \ILIAS\UI\Component\Component               $triggerer
	 * @param \ILIAS\UI\Component\Connector\TriggerAction $action
	 *
	 * @return ComponentConnection
	 */
	public function onHover(\ILIAS\UI\Component\Component $triggerer, \ILIAS\UI\Component\Connector\TriggerAction $action);


	/**
	 * Trigger an action of a component when the triggerer receives the the change event
	 *
	 * @param \ILIAS\UI\Component\Component               $triggerer
	 * @param \ILIAS\UI\Component\Connector\TriggerAction $action
	 *
	 * @return ComponentConnection
	 */
	public function onChange(\ILIAS\UI\Component\Component $triggerer, \ILIAS\UI\Component\Connector\TriggerAction $action);

}