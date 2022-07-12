<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilBuddySystemRelationStateFilterRuleFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelationStateFilterRuleFactory
{
    protected static ?self $instance = null;

    protected function __construct()
    {
    }

    public static function getInstance() : self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getFilterRuleByRelation(ilBuddySystemRelation $relation) : ilBuddySystemRelationStateFilterRule
    {
        $filters = [
            new ilBuddySystemRelationStateInitiatorShouldOnlyBeAbleToCancelRequestRule($relation),
            new ilBuddySystemRelationStateReceiverShouldNotBeAbleToCancelRequestRule($relation),
            new ilBuddySystemRelationStateInitiatorShouldNotBeAbleToApproveIgnoredRequestRule($relation),
            new ilBuddySystemRelationStateReceiverShouldOnlyBeAbleToApproveIgnoredRequestRule($relation),
            new ilBuddySystemRelationStateNullFilterRule($relation),
        ];

        foreach ($filters as $filter) {
            if ($filter->matches()) {
                return $filter;
            }
        }

        return new ilBuddySystemRelationStateNullFilterRule($relation);
    }
}
