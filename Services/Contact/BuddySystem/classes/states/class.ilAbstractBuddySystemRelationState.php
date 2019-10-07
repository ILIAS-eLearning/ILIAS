<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAbstractBuddySystemRelationState
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilAbstractBuddySystemRelationState implements ilBuddySystemRelationState
{
    /**
     * @inheritDoc
     */
    public function isInitial() : bool
    {
        return false;
    }

    /**
     * @inheritDoc
     * @throws ilBuddySystemRelationStateException
     */
    public function request(ilBuddySystemRelation $relation) : void
    {
        throw new ilBuddySystemRelationStateException('Invalid state transition: ' . __FUNCTION__);
    }

    /**
     * @inheritDoc
     * @throws ilBuddySystemRelationStateException
     */
    public function ignore(ilBuddySystemRelation $relation) : void
    {
        throw new ilBuddySystemRelationStateException('Invalid state transition: ' . __FUNCTION__);
    }

    /**
     * @inheritDoc
     * @throws ilBuddySystemRelationStateException
     */
    public function link(ilBuddySystemRelation $relation) : void
    {
        throw new ilBuddySystemRelationStateException('Invalid state transition: ' . __FUNCTION__);
    }

    /**
     * @inheritDoc
     * @throws ilBuddySystemRelationStateException
     */
    public function unlink(ilBuddySystemRelation $relation) : void
    {
        throw new ilBuddySystemRelationStateException('Invalid state transition: ' . __FUNCTION__);
    }
}