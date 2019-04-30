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


	public function getRule() {
		 return ":org_unit_id in org_units_of_employee";
	}

	public function getParameters(): array
	{
		return [
			'orgu_id' => $this->org_unit_id,
		];
	}
}