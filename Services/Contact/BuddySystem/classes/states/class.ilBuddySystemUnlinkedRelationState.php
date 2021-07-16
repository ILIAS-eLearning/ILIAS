<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemUnlinkedRelationState
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemUnlinkedRelationState extends ilAbstractBuddySystemRelationState
{
    public function isInitial() : bool
    {
        return true;
    }

    public function getName() : string
    {
        return 'Unlinked';
    }

    public function getAction() : string
    {
        return 'unlink';
    }

    public function getPossibleTargetStates() : ilBuddySystemRelationStateCollection
    {
        return new ilBuddySystemRelationStateCollection([
            new ilBuddySystemRequestedRelationState(),
        ]);
    }

    public function request(ilBuddySystemRelation $relation) : void
    {
        $relation->setState(new ilBuddySystemRequestedRelationState());
    }
}
