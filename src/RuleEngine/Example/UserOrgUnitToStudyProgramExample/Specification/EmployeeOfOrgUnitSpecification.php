<?php

namespace ILIAS\RuleEngine\Example\UserOrgUnitToStudyProgramExample\Specification;

use ILIAS\RuleEngine\Specification\AbstractSpecification;


/**
 * Class EmployeeOfOrgUnitSpecification
 *
 * @package ILIAS\RuleEngine\Example\UserOrgUnitToStudyProgramExample
 */
class EmployeeOfOrgUnitSpecification extends AbstractSpecification {

	private $org_unit_id;

	public function __construct($org_unit_id) {
		$this->org_unit_id = $org_unit_id;
	}


	public function getKey() {
		// TODO: Implement getKey() method.
	}


	public function getValue() {
		// TODO: Implement getValue() method.
	}


	public function getOperator() {
		// TODO: Implement getOperator() method.
	}
}