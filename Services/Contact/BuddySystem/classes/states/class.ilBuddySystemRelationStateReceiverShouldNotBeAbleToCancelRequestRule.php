<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemRelationStateFilterRule.php';

/**
 * Class ilBuddySystemRelationStateReceiverShouldNotBeAbleToCancelRequestRule
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelationStateReceiverShouldNotBeAbleToCancelRequestRule extends ilBuddySystemRelationStateFilterRule
{
    /**
     * @return bool
     */
    public function matches()
    {
        if (!$this->relation->isRequested()) {
            return false;
        }

        if ($this->relation->isOwnedByRequest()) {
            return false;
        }

        return true;
    }

    /**
     * @param ilBuddySystemRelationState $state
     * @return boolean
     */
    public function __invoke(ilBuddySystemRelationState $state)
    {
        if ($state instanceof ilBuddySystemUnlinkedRelationState) {
            return false;
        }

        return true;
    }
}
