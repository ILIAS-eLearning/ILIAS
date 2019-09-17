<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemIgnoredRequestRelationState
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemIgnoredRequestRelationState extends ilAbstractBuddySystemRelationState
{
    /**
     * @inheritDoc
     */
    public function getName() : string
    {
        return 'IgnoredRequest';
    }

    /**
     * @inheritDoc
     */
    public function getAction() : string
    {
        return 'ignore';
    }

    /**
     * @inheritDoc
     */
    public function getPossibleTargetStates() : ilBuddySystemRelationStateCollection
    {
        return new ilBuddySystemRelationStateCollection([
            new ilBuddySystemUnlinkedRelationState(),
            new ilBuddySystemLinkedRelationState()
        ]);
    }

    /**
     * @inheritDoc
     */
    public function unlink(ilBuddySystemRelation $relation) : void
    {
        $relation->setState(new ilBuddySystemUnlinkedRelationState());
    }

    /**
     * @inheritDoc
     */
    public function link(ilBuddySystemRelation $relation) : void
    {
        $relation->setState(new ilBuddySystemLinkedRelationState());
    }
}