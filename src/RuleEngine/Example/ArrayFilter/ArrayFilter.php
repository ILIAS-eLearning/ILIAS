<?php
namespace ILIAS\RuleEngine\Example\ArrayFilter;

use ILIAS\RuleEngine\RuleEngine;
use ILIAS\RuleEngine\RuleFactory;
use ILIAS\RuleEngine\Specification\Specification;
use ILIAS\RuleEngine\Specification\SpecificationFactory;

class ArrayFilter {

	public function execute() {
		global $DIC;
		$rule_factory = new RuleFactory();

		$specification = SpecificationFactory::andX(
			SpecificationFactory::equals('lastname', 'Doe'),
			SpecificationFactory::moreThan('age', 44),
			SpecificationFactory::equals('gender', 'm')
		);

		$arr_to_filter = [
							['firstname' => 'John', 'lastname' => 'Doe', 'gender' => 'm', 'age' => 50],
							['firstname' => 'Johanne', 'lastname' => 'Doe', 'gender' => 'f', 'age' => 48],
							['firstname' => 'Richard', 'lastname' => 'Doe', 'gender' => 'm', 'age' => 20],
							['firstname' => 'Richard', 'lastname' => 'Miles', 'gender' => 'm','age' => 55],
						 ];


		$rule_engine = new RuleEngine();

		$rule_engine->filterSpec($arr_to_filter,$specification);


	}

}
