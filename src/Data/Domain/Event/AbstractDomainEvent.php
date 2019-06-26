<?php
/* Copyright (c) 2019 Martin Studer <ms@studer-raimann.ch> Extended GPL, see docs/LICENSE - inspired by https://github.com/buttercup-php/protects */

namespace ILIAS\Data\Domain\Event;

use DateTime;
use ilDateTime;
use ILIAS\Data\Domain\Entity\AggregateId;
use ILIAS\Data\Domain\Entity\IsRevisable;

/**
 * Class AbstractDomainEvent
 *
 * @package ILIAS\Data\Domain\Event
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
abstract class AbstractDomainEvent implements DomainEvent {

	/**
	 * @var AggregateId
	 */
	protected $aggregate_id;
	/**
	 * @var ilDateTime;
	 */
	protected $occurred_on;
	/**
	 * @var int
	 */
	protected $initating_user_id;

//todo revision einarbeiten
//IsRevisionable $aggregate_revision,
	public function __construct(AggregateId $aggregate_id, int $initating_user_id) {

		$this->aggregate_id = $aggregate_id;
		$this->occurred_on = new ilDateTime(time(), IL_CAL_UNIX);
		$this->initating_user_id = $initating_user_id;
	}


	/**
	 * The Aggregate this event belongs to.
	 *
	 * @return AggregateId
	 */
	public function getAggregateId(): AggregateId {
		return $this->aggregate_id;
	}

	/**
	 * @return string
	 *
	 * Add a Constant EVENT_NAME to your class: Name it: [aggregate].[event]
	 * e.g. 'question.created'
	 */
	public abstract function getEventName(): string;


	/**
	 * @return DateTime
	 */
	public function getOccurredOn(): ilDateTime {
		return $this->occurred_on;
	}


	/**
	 * @return int
	 */
	public function getInitiatingUserId(): int {
		return $this->initating_user_id;
	}


	/**
	 * @return string
	 */
	public function getEventBody(): string {
		// TODO nice and happy serializer also dont serialize id, creator to data
		return json_encode($this);
	}

	public abstract function restoreEventBody(string $json_data);
}