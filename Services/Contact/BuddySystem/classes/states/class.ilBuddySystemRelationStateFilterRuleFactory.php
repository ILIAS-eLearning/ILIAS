<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemRelationStateFilterRuleFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelationStateFilterRuleFactory
{
    /** @var self */
    protected static $instance;

    /**
     * ilBuddySystemRelationStateFilterRuleFactory constructor.
     */
    protected function __construct()
    {
    }

    /**
     * @return self
     */
    public static function getInstance() : self
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @param ilBuddySystemRelation $relation
     * @return ilBuddySystemRelationStateFilterRule
     */
    public function getFilterRuleByRelation(ilBuddySystemRelation $relation) : ilBuddySystemRelationStateFilterRule
    {
        $filters = [
            new ilBuddySystemRelationStateInitiatorShouldOnlyBeAbleToCancelRequestRule($relation),
            new ilBuddySystemRelationStateReceiverShouldNotBeAbleToCancelRequestRule($relation),
            new ilBuddySystemRelationStateInitiatorShouldNotBeAbleToApproveIgnoredRequestRule($relation),
            new ilBuddySystemRelationStateReceiverShouldOnlyBeAbleToApproveIgnoredRequestRule($relation),
            new ilBuddySystemRelationStateNullFilterRule($relation)
        ];

        foreach ($filters as $filter) {
            if ($filter->matches()) {
                return $filter;
            }
        }

        return new ilBuddySystemRelationStateNullFilterRule($relation);
    }
}