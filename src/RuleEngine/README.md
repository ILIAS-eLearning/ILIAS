# Rule Enginge for ILIAS - WORK IN PROGRESS
This namespace provides a small framework for creating ILIAS specific rules that can be checked ad hoc and continuously. Rules can optional execute actions after rule execution.

Overview and Glossary for this namespace:

| Name | Description | Example |
|------|-------------|---------|
| Rule / Specification|A rule like ((lastname = Doe) AND age > 44)) OR User is emmployee of orgunit with id 77  | SpecificationFactory::equals(new StringHandler('orgu_id'), new IntegerHandler($org_unit_id));
| Target | Defines evaluation location and the possible operators | 
| Executor |Responsible for rule condition evaluation and action execution | 
| RuleRepository | Saved Rules to check continuously
| RuleExecutionLog | Execution Log of Last rule executions to check for circular reference  
| RuleActions | Actions for the rule


Usage
-----
Filter ILIAS Objects
--
```
$org_unit_id = 70;

$specification = SpecificationFactory::equals(new StringHandler('orgu_id'), new IntegerHandler($org_unit_id));

$rule_engine = new RuleEngine([ new SqlTarget() ], [ new EntityExecutor() ]);

print_r($rule_engine->filterSpec(new ilOrgUnitUserAssignmentEntity(), $specification));
```



Filter Arrays
----
```
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
 ```
