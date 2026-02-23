<?php

/**
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
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Conditions\Export;

use ilCondition;
use ilConditionSet;

class Info
{
    protected int $object_id;
    protected int $reference_id;
    protected string $object_type;
    protected ilConditionSet $condition_set;

    public function __construct()
    {
    }

    public function getObjectId(): int
    {
        return $this->object_id;
    }

    public function getReferenceId(): int
    {
        return $this->reference_id;
    }

    public function getObjectType(): string
    {
        return $this->object_type;
    }

    public function getConditionSet(): ilConditionSet
    {
        return $this->condition_set;
    }

    public function withConditionSet(
        ilConditionSet $condition_set
    ): Info {
        $clone = clone $this;
        $clone->condition_set = $condition_set;
        return $clone;
    }

    public function withObjectId(
        int $object_id
    ): Info {
        $clone = clone $this;
        $clone->object_id = $object_id;
        return $clone;
    }

    public function withObjectType(
        string $object_type
    ): Info {
        $clone = clone $this;
        $clone->object_type = $object_type;
        return $clone;
    }

    public function withReferenceId(
        int $reference_id
    ): Info {
        $clone = clone $this;
        $clone->reference_id = $reference_id;
        return $clone;
    }

    public function __toString(): string
    {
        $msg = sprintf("Info (OID:%s, RID%s:, TYPE:%s", $this->object_id, $this->reference_id, $this->object_type);
        $msg .= sprintf("\nNumberObligatory: %s\nAllObligatory: %s\nHidden: %s", $this->condition_set->getNumObligatory(), ($this->condition_set->getAllObligatory() ? 'true' : 'false'), ($this->condition_set->getHiddenStatus() ? 'true' : 'false'));
        foreach ($this->condition_set->getConditions() as $condition) {
            $msg .= sprintf("\n- Condition(ID:%s, Operator:%s, Obligatory: %s):\n-- Value: %s", $condition->getId(), $condition->getOperator(), ($condition->getObligatory() ? 'true' : 'false'), $condition->getValue());
            $msg .= sprintf("\n-- Trigger: RID:%s OID:%s Type:%s", $condition->getTrigger()->getRefId(), $condition->getTrigger()->getObjId(), $condition->getTrigger()->getType());
        }
        return $msg;
    }
}
