<?php

namespace ILIAS\UI\Component\Connector;

use ILIAS\UI\Component\Component;

/**
 * Interface ComponentConnection
 *
 * Connects two components: The first component is acting as triggerer, triggering an action of the second component
 * on a given event (click, hover etc.)
 */
interface ComponentConnection {

	/**
	 * Get the component triggering the action of another component
	 *
	 * @return Component
	 */
	public function getTriggererComponent();


	/**
	 * Get the component whos action is triggered
	 *
	 * @return Component
	 */
	public function getTriggeredComponent();


	/**
	 * Get the triggered action
	 *
	 * @return TriggerAction
	 */
	public function getTriggerAction();


	/**
	 * Get the event triggering the action
	 *
	 * @return string
	 */
	public function getEvent();

}