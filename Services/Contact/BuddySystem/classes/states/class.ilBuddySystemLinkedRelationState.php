<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemLinkedState
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemLinkedRelationState extends ilAbstractBuddySystemRelationState
{
    public function getName() : string
    {
        return 'Linked';
    }

    public function getAction() : string
    {
        return 'link';
    }

    public function getPossibleTargetStates() : ilBuddySystemRelationStateCollection
    {
        return new ilBuddySystemRelationStateCollection([
            new ilBuddySystemUnlinkedRelationState(),
        ]);
    }

    public function unlink(ilBuddySystemRelation $relation) : void
    {
        $relation->setState(new ilBuddySystemUnlinkedRelationState());
    }
}
