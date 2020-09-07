<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemRelationStateFilterRule.php';

/**
 * Class ilBuddySystemRelationStateNullFilterRule
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelationStateNullFilterRule extends ilBuddySystemRelationStateFilterRule
{
    /**
     * @return bool
     */
    public function matches()
    {
        return true;
    }

    /**
     * @param ilBuddySystemRelationState $state
     * @return boolean
     */
    public function __invoke(ilBuddySystemRelationState $state)
    {
        return true;
    }
}
