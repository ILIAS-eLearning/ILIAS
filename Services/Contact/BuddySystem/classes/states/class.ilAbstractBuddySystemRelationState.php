<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystemRelation.php';
require_once 'Services/Contact/BuddySystem/exceptions/class.ilBuddySystemRelationStateException.php';
require_once 'Services/Contact/BuddySystem/interfaces/interface.ilBuddySystemRelationState.php';

/**
 * Class ilAbstractBuddySystemRelationState
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilAbstractBuddySystemRelationState implements ilBuddySystemRelationState
{
    /**
     *  {@inheritDoc}
     */
    public function isInitial()
    {
        return false;
    }

    /**
     * @param ilBuddySystemRelation $relation
     * @throws ilBuddySystemRelationStateException
     */
    public function request(ilBuddySystemRelation $relation)
    {
        throw new ilBuddySystemRelationStateException('Invalid state transition: ' . __FUNCTION__);
    }

    /**
     * @param ilBuddySystemRelation $relation
     * @throws ilBuddySystemRelationStateException
     */
    public function ignore(ilBuddySystemRelation $relation)
    {
        throw new ilBuddySystemRelationStateException('Invalid state transition: ' . __FUNCTION__);
    }

    /**
     * @param ilBuddySystemRelation $relation
     * @throws ilBuddySystemRelationStateException
     */
    public function link(ilBuddySystemRelation $relation)
    {
        throw new ilBuddySystemRelationStateException('Invalid state transition: ' . __FUNCTION__);
    }

    /**
     * @param ilBuddySystemRelation $relation
     * @throws ilBuddySystemRelationStateException
     */
    public function unlink(ilBuddySystemRelation $relation)
    {
        throw new ilBuddySystemRelationStateException('Invalid state transition: ' . __FUNCTION__);
    }
}
