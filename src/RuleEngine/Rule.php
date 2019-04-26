<?php

namespace ILIAS\RuleEngine;

use ILIAS\Context\Context;
use ILIAS\RuleEngine\Specification\Specification;
use ILIAS\DomainObject\DomainObject;

/**
 * Interface Rule
 *
 * @author Martin Studer ms@studer-raimann.ch
 */
interface Rule {

	/**
	 * Events triggered if the evaluation of the rule is true
	 *
	 * @return array $events {
	 *      array $event {
	 *
	 * @option    string    $a_component    component, e.g. "Modules/Forum" or "Services/User"
	 * @option    string    $a_event        event e.g. "createUser", "updateUser", "deleteUser", ...
	 * @option    array    $a_parameter    parameter array (assoc), array("name" => ..., "phone_office" => ...)
	 *      }
	 * }
	 * @see       ilAppEventListener()
	 */
	public function getEvents(): array;


	/**
	 * @return Specification
	 *
	 */
	public function getSpecification(): Specification;


	/**
	 * @param Context $context
	 */
	public function isSatisfiedBy(Context $context): bool;
}