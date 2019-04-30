<?php
namespace ILIAS\RuleEngine\Example\ArrayFilter;

use ILIAS\RuleEngine\Executor\Entity\ArrayExecutor;
use ILIAS\RuleEngine\RuleEngine;
use ILIAS\RuleEngine\Specification\SpecificationFactory;
use ILIAS\RuleEnginge\Target\ArrayVisitor\ArrayVisitor;

class ArrayFilter {

	public function execute() {

		$specification = SpecificationFactory::andX([
			SpecificationFactory::equals('lastname', 'Doe'),
			SpecificationFactory::moreThan('age', 44),
			SpecificationFactory::equals('gender', 'm')]
		);

		$arr_to_filter = [
							['firstname' => 'John', 'lastname' => 'Doe', 'gender' => 'm', 'age' => 50],
							['firstname' => 'Johanne', 'lastname' => 'Doe', 'gender' => 'f', 'age' => 48],
							['firstname' => 'Richard', 'lastname' => 'Doe', 'gender' => 'm', 'age' => 20],
							['firstname' => 'Richard', 'lastname' => 'Miles', 'gender' => 'm','age' => 55],
						 ];


		$rule_engine = new RuleEngine([ new ArrayVisitor() ], [ new ArrayExecutor() ]);

		$rule_engine->filterSpec($arr_to_filter,$specification);
	}

}
