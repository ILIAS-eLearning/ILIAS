<?php
namespace ILIAS\RuleEngine\Example\UserOrgUnitToStudyProgramExample;

use ILIAS\RuleEngine\RuleEngine;
use ILIAS\RuleEngine\RuleFactory;
use ILIAS\RuleEngine\Executor\Entity\EntityExecutor;
use ILIAS\RuleEnginge\Target\ilSqlVisitor\ilSqlVisitor;

class UserOrgUnitToStudyProgramExample {

	public function execute() {
		global $DIC;
		$rule_factory = new RuleFactory();

		$org_unit_id = 9999;
		$specification = new EmployeeOfOrgUnitSpecification($org_unit_id);

		$rule_engine = new RuleEngine([new ilSqlVisitor()],[new EntityExecutor()]);

		$rule_engine->filterSpec(new ilOrgUnitUserAssignmentEntity(),$specification);


	}

}
