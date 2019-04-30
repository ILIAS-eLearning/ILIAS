<?php

namespace ILIAS\RuleEngine\Example\UserOrgUnitToStudyProgramExample;

use ILIAS\RuleEngine\RuleEngine;
use ILIAS\RuleEngine\Specification\SpecificationFactory;
use ILIAS\RuleEngine\Executor\Entity\EntityExecutor;
use ILIAS\RuleEnginge\Target\ilSqlVisitor\ilSqlVisitor;

use ILIAS\RuleEngine\Example\UserOrgUnitToStudyProgramExample\Specification\EmployeeOfOrgUnitSpecification;

class UserOrgUnitToStudyProgramExample {

	public function execute() {

		$org_unit_id = 70;
		//$specification = new EmployeeOfOrgUnitSpecification($org_unit_id);

		$specification = SpecificationFactory::equals('orgu_id', $org_unit_id);

		$rule_engine = new RuleEngine([ new ilSqlVisitor() ], [ new EntityExecutor() ]);

		print_r($rule_engine->filterSpec(new ilOrgUnitUserAssignmentEntity(), $specification));
	}
}
