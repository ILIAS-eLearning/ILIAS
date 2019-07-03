<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\Common\Model;

use ILIAS\Data\Domain\Entity\AbstractAggregateRoot;

/**
 * An AggregateRoot, that can be reconstituted from an AggregateHistory.
 */
interface IsEventSourced {

	function reconstituteAggregate(DomainEvents $event_history): AggregateRoot;
}
 