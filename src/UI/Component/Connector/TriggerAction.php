<?php

namespace ILIAS\UI\Component\Connector;

/**
 * Interface TriggerAction
 * A TriggerAction represents a action executed by a component and triggered on an event (click, hover etc.) by
 * another component acting as triggerer. A class implementing this interface is responsible to output the needed
 * javascript for the action.
 */
interface TriggerAction {

	/**
	 * Get the component executing this action
	 *
	 * @return \ILIAS\UI\Component\Component
	 */
	public function getComponent();


	/**
	 * Render the javascript needed in order to execute this action.
	 * The given $id represents the generated ID in HTML for the component where this action belongs to.
	 *
	 * @param string $id
	 *
	 * @return string
	 */
	public function renderJavascript($id);
}