**Container as Condition Controller**

If a component `Modules/[Container]` wants to take over the control of conditions for its childs it needs to implement `ilConditionControllerInterface` in a class `il[Container]ConditionController` located under `Modules/[Container]/classes/class.il[Container]ConditionController.php`.

The method `isContainerConditionController($container_ref_id)` MUST return `true` if the container currently controls the conditions, otherwise `false`.

The method `getConditionSetForRepositoryObject` MUST return a valid instance of `ilConditionSet` for a valid `ref_id` for a child of the container.

The instance MUST be created by using the `$DIC->conditions()` service object. Example:

```
function getConditionSetForRepositoryObject($child_ref_id) {
	global $DIC;
	
	...
	// example: $child_ref_id might be a file and
	// a test with $trigger_ref_id must be passed to access
	// the file
	
	$f = $DIC->conditions()->factory();
	$condition1 = $f->condition(
		$f->repositoryTrigger($trigger_ref_id),
		$f->operator()->passed()
	);
	$condition_set = $f->set(array($condition1));
	return $condition_set;
}
```