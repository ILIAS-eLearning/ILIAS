<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemRelation
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBuddySystemRelationStateFilterRule
{
    /**
     * @var ilBuddySystemRelation
     */
    protected $relation;

    /**
     * @param ilBuddySystemRelation $relation
     */
    public function __construct(ilBuddySystemRelation $relation)
    {
        $this->relation = $relation;
    }

    /**
     * @return ilBuddySystemCollection|ilBuddySystemRelationState[]
     */
    public function getStates()
    {
        // For PHP >= 5.4.x:
        // 1. Change type hint of \ilBuddySystemCollection::filter to a "Callable"
        // 2. Change the line below to: return $this->relation->getState()->getPossibleTargetStates()->filter($this);
        $self = $this;
        return $this->relation->getState()->getPossibleTargetStates()->filter(function (ilBuddySystemRelationState $state) use ($self) {
            return $self->__invoke($state);
        });
    }

    /**
     * @return bool
     */
    abstract public function matches();

    /**
     * @param ilBuddySystemRelationState $state
     * @return boolean
     */
    abstract public function __invoke(ilBuddySystemRelationState $state);
}
