<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event;

use ilDateTime;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Shared\DomainObjectId;

/**
 * Interface EventStore
 *
 * @package ILIAS\Data\Domain\Event
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface EventStore {

	/**
	 * @param DomainEvents $domain_events
	 */
	public function commit(DomainEvents $domain_events): void;


	/**
	 * @param DomainObjectId $id
	 *
	 * @return DomainEvents
	 */
	public function getAggregateHistoryFor(DomainObjectId $id): DomainEvents;
}