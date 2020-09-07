<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemRelationStateFilterRuleFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelationStateFilterRuleFactory
{
    /**
     * @var self
     */
    protected static $instance;

    /**
     *
     */
    protected function __construct()
    {
    }

    /**
     * @return self
     */
    public static function getInstance()
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
    public function getFilterRuleByRelation(ilBuddySystemRelation $relation)
    {
        require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemRelationStateInitiatorShouldOnlyBeAbleToCancelRequestRule.php';
        require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemRelationStateReceiverShouldNotBeAbleToCancelRequestRule.php';
        require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemRelationStateInitiatorShouldNotBeAbleToApproveIgnoredRequestRule.php';
        require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemRelationStateReceiverShouldOnlyBeAbleToApproveIgnoredRequestRule.php';
        require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemRelationStateNullFilterRule.php';

        $filters = array(
            new ilBuddySystemRelationStateInitiatorShouldOnlyBeAbleToCancelRequestRule($relation),
            new ilBuddySystemRelationStateReceiverShouldNotBeAbleToCancelRequestRule($relation),
            new ilBuddySystemRelationStateInitiatorShouldNotBeAbleToApproveIgnoredRequestRule($relation),
            new ilBuddySystemRelationStateReceiverShouldOnlyBeAbleToApproveIgnoredRequestRule($relation),
            new ilBuddySystemRelationStateNullFilterRule($relation)
        );
        foreach ($filters as $filter) {
            /**
             * @var $filter ilBuddySystemRelationStateFilterRule
             */
            if ($filter->matches()) {
                return $filter;
            }
        }
        
        return new ilBuddySystemRelationStateNullFilterRule($relation);
    }
}
