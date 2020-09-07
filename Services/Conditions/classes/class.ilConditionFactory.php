<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Condition factory
 *
 * @author @leifos.de
 * @ingroup
 */
class ilConditionFactory
{
    /**
     * @var ilConditionObjectAdapterInterface
     */
    protected $cond_obj_adapter;

    /**
     * Constructor
     */
    public function __construct(ilConditionObjectAdapterInterface $cond_obj_adapter = null)
    {
        if (is_null($cond_obj_adapter)) {
            $this->cond_obj_adapter = new ilConditionObjectAdapter();
        }
    }

    /**
     * Repository condition trigger object
     *
     * @param int $a_ref_id ref id of trigger object
     * @return ilConditionTrigger
     */
    public function repositoryTrigger($ref_id)
    {
        $obj_id = $this->cond_obj_adapter->getObjIdForRefId($ref_id);
        $obj_type = $this->cond_obj_adapter->getTypeForObjId($obj_id);
        return new ilConditionTrigger($ref_id, $obj_id, $obj_type);
    }
    
    
    /**
     * Condition set
     *
     * @param ilCondition[] $conditions
     * @return ilConditionSet
     */
    public function set($conditions)
    {
        return new ilConditionSet($conditions);
    }

    /**
     * Condition
     *
     * @param ilConditionTrigger $trigger
     * @param string $operator
     * @param string $value
     * @return ilCondition
     */
    public function condition(ilConditionTrigger $trigger, $operator, $value = null)
    {
        return new ilCondition($trigger, $operator, $value);
    }


    /**
     * Standard operator factory
     *
     * @return ilConditionOperatorFactory
     */
    public function operator()
    {
        return new ilConditionOperatorFactory();
    }
}
