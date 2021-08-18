<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemRequestedRelationState
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRequestedRelationState extends ilAbstractBuddySystemRelationState
{
    public function getName() : string
    {
        return 'Requested';
    }

    public function getAction() : string
    {
        return 'request';
    }

    public function getPossibleTargetStates() : ilBuddySystemRelationStateCollection
    {
        return new ilBuddySystemRelationStateCollection([
            new ilBuddySystemLinkedRelationState(),
            new ilBuddySystemIgnoredRequestRelationState(),
            new ilBuddySystemUnlinkedRelationState(),
        ]);
    }

    public function unlink(ilBuddySystemRelation $relation) : void
    {
        $relation->setState(new ilBuddySystemUnlinkedRelationState());
    }

    public function ignore(ilBuddySystemRelation $relation) : void
    {
        $relation->setState(new ilBuddySystemIgnoredRequestRelationState());
    }

    public function link(ilBuddySystemRelation $relation) : void
    {
        $relation->setState(new ilBuddySystemLinkedRelationState());
    }
}
