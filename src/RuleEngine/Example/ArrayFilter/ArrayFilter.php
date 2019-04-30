<?php
namespace ILIAS\RuleEngine\Example\ArrayFilter;

use ILIAS\Data\Scalar\IntegerHandler;
use ILIAS\Data\Scalar\StringHandler;
use ILIAS\RuleEngine\Executor\Entity\ArrayExecutor;
use ILIAS\RuleEngine\RuleEngine;
use ILIAS\RuleEngine\Specification\SpecificationFactory;
use ILIAS\RuleEnginge\Target\ArrayVisitor\ArrayTarget;

class ArrayFilter {

	public function execute() {

		$specification = SpecificationFactory::andX([
			SpecificationFactory::equals(new StringHandler('lastname'), new StringHandler('Doe')),
			SpecificationFactory::moreThan(new StringHandler('age'), new IntegerHandler(44)),
			SpecificationFactory::equals(new StringHandler('gender'), new StringHandler('m'))]
		);

		$arr_to_filter = [
							['firstname' => 'John', 'lastname' => 'Doe', 'gender' => 'm', 'age' => 50],
							['firstname' => 'Johanne', 'lastname' => 'Doe', 'gender' => 'f', 'age' => 48],
							['firstname' => 'Richard', 'lastname' => 'Doe', 'gender' => 'm', 'age' => 20],
							['firstname' => 'Richard', 'lastname' => 'Miles', 'gender' => 'm','age' => 55],
						 ];


		$rule_engine = new RuleEngine([ new ArrayTarget() ], [ new ArrayExecutor() ]);

		$rule_engine->filterSpec($arr_to_filter,$specification);
	}

}
