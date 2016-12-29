<?php

namespace ILIAS\UI\Component\Connector;

/**
 * Interface TriggerAction
 *
 */
interface TriggerAction {

	/**
	 * Get the component executing this action
	 *
	 * @return \ILIAS\UI\Component\Component
	 */
	public function getComponent();


//	/**
//	 * Get the event triggering this action
//	 *
//	 * @return string
//	 */
//	public function getEvent();
//
//
//	/**
//	 * Get all supported events that are allowed to trigger this action
//	 *
//	 * @return array
//	 */
//	public function getSupportedEvents();


	/**
	 * Render the javascript needed in order to execute this action. The given $id represents the generated ID in HTML for the component
	 *
	 * @param string $id
	 * @return string
	 */
	public function renderJavascript($id);
}