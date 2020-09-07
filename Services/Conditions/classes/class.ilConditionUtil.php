<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Condition utility object
 *
 * Wraps some ilConditionHandler methods (which will become deprecated)
 * Dependency management needs to be improved.
 *
 * @author @leifos.de
 * @ingroup ServicesConditions
 */
class ilConditionUtil
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

        if (is_null($cond_obj_adapter)) {
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
    public function getValidRepositoryTriggerTypes()
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
    public function getOperatorsForRepositoryTriggerType($a_type)
    {
        $ch = new ilConditionHandler();
        return $ch->getOperatorsByTriggerType($a_type);
    }

    /**
     * Check if a ref id is under condition control of its parent
     *
     * @param $ref_id
     * @return bool
     */
    public function isUnderParentControl($ref_id)
    {
        // check if parent takes over control of condition
        $parent = $this->tree->getParentId($ref_id);
        $parent_obj_id = $this->cond_obj_adapter->getObjIdForRefId($parent);
        $parent_type = $this->cond_obj_adapter->getTypeForObjId($parent_obj_id);

        $class = $this->obj_definition->getClassName($parent_type);
        $class_name = "il" . $class . "ConditionController";
        $location = $this->obj_definition->getLocation($parent_type);
        // if yes, get from parent
        if (is_file($location . "/class." . $class_name . ".php")) {
            /** @var ilConditionControllerInterface $controller */
            $controller = new $class_name();
            return $controller->isContainerConditionController($parent);
        }

        return false;
    }
}
