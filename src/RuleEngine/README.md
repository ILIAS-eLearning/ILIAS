# Rule Enginge for ILIAS
This namespace provides a small framework for creating ILIAS specific rules that can be checked synchronized and unsynchronized.

Overview and Glossary for this namespace:

| Name | Description | Example |
|------|-------------|---------|
| Rule / Specification|  | ...
| Target | ... | 
| Executor |... | 


Usage
-----
/**
*
* Filter ILIAS Objects
*
**/
$org_unit_id = 70;
//$specification = new EmployeeOfOrgUnitSpecification($org_unit_id);
$specification = SpecificationFactory::equals('orgu_id', $org_unit_id);

$rule_engine = new RuleEngine([ new ilSqlVisitor() ], [ new EntityExecutor() ]);

print_r($rule_engine->filterSpec(new ilOrgUnitUserAssignmentEntity(), $specification));


/**
*
* Filter Arrays
*
**/
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
 
