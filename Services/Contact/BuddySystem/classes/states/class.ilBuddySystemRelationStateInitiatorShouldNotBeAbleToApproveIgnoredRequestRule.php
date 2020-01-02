<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemRelationStateFilterRule.php';

/**
 * Class ilBuddySystemRelationStateInitiatorShouldNotBeAbleToApproveIgnoredRequestRule
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelationStateInitiatorShouldNotBeAbleToApproveIgnoredRequestRule extends ilBuddySystemRelationStateFilterRule
{
    /**
     * @return bool
     */
    public function matches()
    {
        if (!$this->relation->isIgnored()) {
            return false;
        }

        if (!$this->relation->isOwnedByRequest()) {
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
        if ($state instanceof ilBuddySystemLinkedRelationState) {
            return false;
        }

        return true;
    }
}
