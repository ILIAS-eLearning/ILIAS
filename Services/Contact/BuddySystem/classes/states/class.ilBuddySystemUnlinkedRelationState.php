<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemUnlinkedRelationState
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemUnlinkedRelationState extends ilAbstractBuddySystemRelationState
{
    /**
     * @inheritDoc
     */
    public function isInitial() : bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getName() : string
    {
        return 'Unlinked';
    }

    /**
     * @inheritDoc
     */
    public function getAction() : string
    {
        return 'unlink';
    }

    /**
     * @inheritDoc
     */
    public function getPossibleTargetStates() : ilBuddySystemRelationStateCollection
    {
        return new ilBuddySystemRelationStateCollection([
            new ilBuddySystemRequestedRelationState()
        ]);
    }

    /**
     * @inheritDoc
     */
    public function request(ilBuddySystemRelation $relation) : void
    {
        $relation->setState(new ilBuddySystemRequestedRelationState());
    }
}