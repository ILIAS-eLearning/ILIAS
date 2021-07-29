<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemRelationStateNullFilterRule
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelationStateNullFilterRule extends ilBuddySystemRelationStateFilterRule
{
    public function matches() : bool
    {
        return true;
    }

    public function __invoke(ilBuddySystemRelationState $state) : bool
    {
        return true;
    }
}
