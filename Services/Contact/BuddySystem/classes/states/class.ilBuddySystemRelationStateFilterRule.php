<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemRelation
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBuddySystemRelationStateFilterRule
{
    protected ilBuddySystemRelation $relation;

    public function __construct(ilBuddySystemRelation $relation)
    {
        $this->relation = $relation;
    }

    public function getStates() : ilBuddySystemRelationStateCollection
    {
        return $this->relation->getState()->getPossibleTargetStates()->filter($this);
    }

    abstract public function matches() : bool;

    abstract public function __invoke(ilBuddySystemRelationState $state) : bool;
}
