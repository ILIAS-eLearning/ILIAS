<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\CQRS\Event;

use Exception;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;

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
	 * @var DomainObjectId
	 */
	private $aggregate_Id;


	/**
	 * AggregateHistory constructor.
	 *
	 * @param DomainObjectId   $aggregate_Id
	 * @param DomainEvent[] $events
	 *
	 * @throws Exception
	 */
	public function __construct(DomainObjectId $aggregate_Id, array $events) {
		/** @var $event DomainEvent */
		foreach ($events as $event) {
			if (!$event->getAggregateId()->equals($aggregate_Id)) {
				throw new Exception();
			}
		}
		parent::__construct();

		foreach($events as $event) {
		    $this->addEvent($event);
        }

		$this->aggregate_Id = $aggregate_Id;
	}


	/**
	 * @return DomainObjectId
	 */
	public function getAggregateId(): DomainObjectId {
		return $this->aggregate_Id;
	}


	/**
	 * @param DomainEvent $domainEvent
	 *
	 * @throws Exception
	 */
	public function append(DomainEvent $domainEvent) {
		$this->addEvent($domainEvent);
	}
}