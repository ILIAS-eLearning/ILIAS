<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/classes/states/class.ilAbstractBuddySystemRelationState.php';

/**
 * Class ilBuddySystemIgnoredRequestRelationState
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemIgnoredRequestRelationState extends ilAbstractBuddySystemRelationState
{
    /**
     *  {@inheritDoc}
     */
    public function getName()
    {
        return 'IgnoredRequest';
    }

    /**
     *  {@inheritDoc}
     */
    public function getAction()
    {
        return 'ignore';
    }

    /**
     * @return ilBuddySystemCollection|ilBuddySystemRelationState[]
     */
    public function getPossibleTargetStates()
    {
        require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemRelationStateCollection.php';
        return new ilBuddySystemRelationStateCollection(array(
            new ilBuddySystemUnlinkedRelationState(),
            new ilBuddySystemLinkedRelationState()
        ));
    }

    /**
     * @param ilBuddySystemRelation
     */
    public function unlink(ilBuddySystemRelation $relation)
    {
        $relation->setState(new ilBuddySystemUnlinkedRelationState());
    }

    /**
     * @param ilBuddySystemRelation
     */
    public function link(ilBuddySystemRelation $relation)
    {
        $relation->setState(new ilBuddySystemLinkedRelationState());
    }
}
