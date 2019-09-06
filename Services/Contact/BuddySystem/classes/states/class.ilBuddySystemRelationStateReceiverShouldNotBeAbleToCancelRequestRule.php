<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemRelationStateReceiverShouldNotBeAbleToCancelRequestRule
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelationStateReceiverShouldNotBeAbleToCancelRequestRule extends ilBuddySystemRelationStateFilterRule
{
    /**
     * @inheritDoc
     */
    public function matches() : bool
    {
        if (!$this->relation->isRequested()) {
            return false;
        }

        if ($this->relation->isOwnedByActor()) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ilBuddySystemRelationState $state) : bool
    {
        if ($state instanceof ilBuddySystemUnlinkedRelationState) {
            return false;
        }

        return true;
    }
}