<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemIgnoredRequestRelationState
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemIgnoredRequestRelationState extends ilAbstractBuddySystemRelationState
{
    public function getName() : string
    {
        return 'IgnoredRequest';
    }

    public function getAction() : string
    {
        return 'ignore';
    }

    public function getPossibleTargetStates() : ilBuddySystemRelationStateCollection
    {
        return new ilBuddySystemRelationStateCollection([
            new ilBuddySystemUnlinkedRelationState(),
            new ilBuddySystemLinkedRelationState(),
        ]);
    }

    public function unlink(ilBuddySystemRelation $relation) : void
    {
        $relation->setState(new ilBuddySystemUnlinkedRelationState());
    }

    public function link(ilBuddySystemRelation $relation) : void
    {
        $relation->setState(new ilBuddySystemLinkedRelationState());
    }
}
