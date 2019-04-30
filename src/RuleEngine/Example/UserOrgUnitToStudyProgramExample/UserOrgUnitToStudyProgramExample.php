<?php

namespace ILIAS\RuleEngine\Example\UserOrgUnitToStudyProgramExample;

use ILIAS\Data\Scalar\IntegerHandler;
use ILIAS\Data\Scalar\StringHandler;
use ILIAS\RuleEngine\RuleEngine;
use ILIAS\RuleEngine\Specification\SpecificationFactory;
use ILIAS\RuleEngine\Executor\Entity\EntityExecutor;
use ILIAS\RuleEnginge\Target\SqlTarget\SqlTarget;

class UserOrgUnitToStudyProgramExample {

	public function execute() {

		$org_unit_id = 70;

		$specification = SpecificationFactory::equals(new StringHandler('orgu_id'), new IntegerHandler($org_unit_id));

		$rule_engine = new RuleEngine([ new SqlTarget() ], [ new EntityExecutor() ]);

		print_r($rule_engine->filterSpec(new ilOrgUnitUserAssignmentEntity(), $specification));
	}
}
