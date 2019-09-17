<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemRelationStateInitiatorShouldNotBeAbleToApproveIgnoredRequestRule
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelationStateInitiatorShouldNotBeAbleToApproveIgnoredRequestRule extends ilBuddySystemRelationStateFilterRule
{
    /**
     * @inheritDoc
     */
    public function matches() : bool
    {
        if (!$this->relation->isIgnored()) {
            return false;
        }

        if (!$this->relation->isOwnedByActor()) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ilBuddySystemRelationState $state) : bool
    {
        if ($state instanceof ilBuddySystemLinkedRelationState) {
            return false;
        }

        return true;
    }
}