<?php
namespace  ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event;

use ilDateTime;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AggregateId;

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
	public function commit(DomainEvents $domain_events) : void;

	/**
	 * @param AggregateId $id
	 *
	 * @return DomainEvents
	 */
	public function getAggregateHistoryFor(AggregateId $id) : DomainEvents;
}