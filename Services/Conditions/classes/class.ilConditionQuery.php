<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Query condition information.
 *
 * Wraps a lot of ilConditionHandler methods (which will become deprecated)
 * Dependency management needs to be improved.
 *
 * @author @leifos.de
 * @ingroup ServicesConditions
 */
class ilConditionQuery
{
	/**
	 * @var ilTree
	 */
	protected $tree;

	/**
	 * @var ilConditionObjectAdapterInterface
	 */
	protected $cond_obj_adapter;

	/**
	 * @var ilObjectDefinition
	 */
	protected $obj_definition;

	/**
	 * Constructor
	 */
	public function __construct(ilConditionObjectAdapterInterface $cond_obj_adapter = null)
	{
		global $DIC;

		if (is_null($cond_obj_adapter))
		{
			$this->cond_obj_adapter = new ilConditionObjectAdapter();
		}

		$this->tree = $DIC->repositoryTree();
		$this->obj_definition = $DIC["objDefinition"];
	}

	/**
	 * Get all valid repository trigger object types
	 *
	 * This holds currently a dependency on $objDefinition and plugin activation
	 *
	 * @return string[]
	 */
	protected function getValidRepositoryTriggerTypes()
	{
		$ch = new ilConditionHandler();
		return $ch->getTriggerTypes();
	}
	
	/**
	 * Get operators for repository trigger object type
	 *
	 * @param string $a_type type
	 * @return string[]
	 */
	protected function getOperatorsForRepositoryTriggerType($a_type)
	{
		$ch = new ilConditionHandler();
		return $ch->getOperatorsByTriggerType($a_type);
	}

	/**
	 * Get set of conditions for repository target. This does not only
	 * get data from persistence layer but also from containers that deliver
	 * condition sets on their own.
	 *
	 * @param int $ref_id ref id
	 * @return ilRepoConditionSet
	 */
	protected function getConditionSetOfRepositoryTarget($ref_id)
	{
		// check if parent takes over control of condition
		$parent = $this->tree->getParentId($ref_id);
		$parent_obj_id = $this->cond_obj_adapter->getObjIdForRefId($parent);
		$parent_type = $this->cond_obj_adapter->getObjIdForRefId($parent_obj_id);

		$class = $this->obj_definition->getClassName($parent_type);
		$class_name = "il".$class."ConditionController";
		$location = $this->obj_definition->getLocation($parent_type);

		// if yes, get from parent
		if (is_file($location."/class.".$class_name.".php"))
		{
			$controller = new $class_name();
			if ($controller->isContainerConditionController($parent))
			{
				return $controller->getConditionSetForRepositoryObject($ref_id);
			}
		}

		// get conditions the old fashioned way
		/*
		foreach (ilConditionHandler::_getConditionsOfTarget($parent, $parent_obj_id, $parent_type) as $c)
		{
			$f = $DIC->conditions()->factory();
			$condition1 = $f->condition(
				$f->repositoryTrigger($trigger_ref_id),
				$f->operator()->passed()
			);
			$condition_set = $f->set(array($condition1));
			return $condition_set;
		}*/
	}

	
}