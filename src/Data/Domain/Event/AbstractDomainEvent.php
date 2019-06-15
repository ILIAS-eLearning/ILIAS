<?php
/* Copyright (c) 2019 Martin Studer <ms@studer-raimann.ch> Extended GPL, see docs/LICENSE - inspired by https://github.com/buttercup-php/protects */

namespace ILIAS\Data\Domain\Event;

use DateTime;
use ILIAS\Data\Domain\Entity\AggregateId;
use ILIAS\Data\Domain\Entity\AggregateRevision;

/**
 * Class AbstractDomainEvent
 *
 * @package ILIAS\Data\Domain\Event
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
abstract class
AbstractDomainEvent implements DomainEvent {

	const EVENT_NAME = 'TO IMPLEMENT';
	/**
	 * @var AggregateId
	 */
	protected $aggregate_id;
	/**
	 * @var
	 */
	//protected $AggregateRevision aggregate_revision;
	/**
	 * @return string
	 */
	protected $event_name;
	/**
	 * @var DateTime;
	 */
	protected $occured_on;
	/**
	 * @var int
	 */
	protected $initating_user_id;
	/**
	 * @var string
	 */
	protected $event_body;

//todo revision einarbeiten
//AggregateRevision $aggregate_revision,
	public function __construct(AggregateId $aggregate_id,  $initating_user_id, $event_body) {

		$this->aggregate_id = $aggregate_id;
		$this->aggregate_revision = $aggregate_revision;
		$this->occured_on = "2019-02-02 08:20:00";//TODO;
		$this->initating_user_id = $initating_user_id;
		$this->event_body = $event_body;
	}


	/**
	 * The Aggregate this event belongs to.
	 *
	 * @return AggregateId
	 */
	public function getAggregateId(): AggregateId {
		$this->getAggregateId();
	}


	/**
	 * @return AggregateRevision
	 */
	/*public function getRevision(): AggregateRevision {
		return $this->getRevision();
	}*/


	/**
	 * @return string
	 *
	 * Add a Constant EVENT_NAME to your class: Name it: [aggregate].[event]
	 * e.g. 'question.created'
	 */
	public function getEventName(): string {
		return self::EVENT_NAME;
	}


	/**
	 * @return DateTime
	 */
	public function getOccuredOn(): DateTime {
		$this->getOccuredOn();
	}


	/**
	 * @return int
	 */
	public function getInitiatingUserId(): int {
		return $this->getInitiatingUserId();
	}


	/**
	 * @return string
	 */
	public function getEventBody(): string {
		return $this->getEventBody();
	}
}