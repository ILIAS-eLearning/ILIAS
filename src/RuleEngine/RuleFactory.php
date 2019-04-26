<?php

namespace ILIAS\RuleEngine;


use ILIAS\RuleEngine\Specification\Specification;

interface RuleFactory {

	/**
	 * @param array $events {
	 *      array $event {
	 *
	 * @option    string    $a_component    component, e.g. "Modules/Forum" or "Services/User"
	 * @option    string    $a_event        event e.g. "createUser", "updateUser", "deleteUser", ...
	 * @option    array    $a_parameter    parameter array (assoc), array("name" => ..., "phone_office" => ...)
	 *      }
	 * }
	 * @param Specification $specification
	 *
	 * @see ilAppEventListener()
	 *
	 * @return Rule
	 */
	public function createRule(array $events, Specification $specification);
}