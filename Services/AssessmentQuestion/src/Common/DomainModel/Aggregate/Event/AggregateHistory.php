<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\Common\Event;


use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AggregateId;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event\DomainEvent;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event\DomainEvents;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Exception\DomainExceptionCorruptAggregateHistory;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Exception\DomainExceptionMissingImplementation;

/**
 * Class AggregateHistory
 *
 * @package ILIAS\AssessmentQuestion\Common\Event
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
final class AggregateHistory extends DomainEvents {

	/**
	 * @var AggregateId
	 */
	private $aggregate_Id;


	/**
	 * AggregateHistory constructor.
	 *
	 * @param AggregateId $aggregate_Id
	 * @param DomainEvent[] $events
	 *
	 * @throws DomainExceptionCorruptAggregateHistory
	 */
	public function __construct(AggregateId $aggregate_Id, array $events) {
		/** @var $event DomainEvent */
		foreach ($events as $event) {
			if (!$event->getAggregateId()->equals($aggregate_Id)) {
				throw new DomainExceptionCorruptAggregateHistory;
			}
		}
		parent::__construct($events);
		$this->aggregate_Id = $aggregate_Id;
	}


	/**
	 * @return AggregateId
	 */
	public function getAggregateId(): AggregateId {
		return $this->aggregate_Id;
	}


	/**
	 * @param DomainEvent $domainEvent
	 *
	 * @throws DomainExceptionMissingImplementation
	 */
	public function append(DomainEvent $domainEvent) {
		throw new DomainExceptionMissingImplementation("@todo  Implement append() method.");
	}
}