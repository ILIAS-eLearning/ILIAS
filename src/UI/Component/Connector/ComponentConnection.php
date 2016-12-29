<?php

namespace ILIAS\UI\Component\Connector;

/**
 * Interface ComponentConnection
 */
interface ComponentConnection {

	/**
	 * Get the component triggering the action of another component
	 *
	 * @return Triggerer
	 */
	public function getTriggererComponent();


	/**
	 * Get the component whos action is triggered
	 *
	 * @return Triggerable
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