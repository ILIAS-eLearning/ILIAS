<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Condition factory
 * @author @leifos.de
 */
class ilConditionFactory
{
    protected ilConditionObjectAdapterInterface $cond_obj_adapter;

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
     */
    public function repositoryTrigger(int $ref_id) : ilConditionTrigger
    {
        $obj_id = $this->cond_obj_adapter->getObjIdForRefId($ref_id);
        $obj_type = $this->cond_obj_adapter->getTypeForObjId($obj_id);
        return new ilConditionTrigger($ref_id, $obj_id, $obj_type);
    }

    /**
     * Condition set
     * @param ilCondition[] $conditions
     * @return ilConditionSet
     */
    public function set(array $conditions) : ilConditionSet
    {
        return new ilConditionSet($conditions);
    }

    public function condition(ilConditionTrigger $trigger, string $operator, ?string $value = null) : ilCondition
    {
        return new ilCondition($trigger, $operator, $value);
    }

    public function operator() : ilConditionOperatorFactory
    {
        return new ilConditionOperatorFactory();
    }
}
