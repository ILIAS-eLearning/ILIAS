<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Condition factory
 * @author @leifos.de
 */
class ilConditionFactory
{
    protected ilConditionObjectAdapterInterface $cond_obj_adapter;

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
