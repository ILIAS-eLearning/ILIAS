<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\CQRS\Event;

use ilDateTime;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;

/**
 * Class AbstractDomainEvent
 *
 * @package ILIAS\Data\Domain\Event
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class AbstractDomainEvent implements DomainEvent {

	/**
	 * @var DomainObjectId
	 */
	protected $aggregate_id;
	/**
	 * @var ilDateTime;
	 */
	protected $occurred_on;
	/**
	 * @var int
	 */
	protected $initiating_user_id;



    /**
     * AbstractDomainEvent constructor.
     *
     * @param DomainObjectId $aggregate_id
     * @param int            $initiating_user_id
     *
     * @throws \ilDateTimeException
     */
	public function __construct(DomainObjectId $aggregate_id, int $initiating_user_id) {

		$this->aggregate_id = $aggregate_id;
		$this->occurred_on = new ilDateTime(time(), IL_CAL_UNIX);
		$this->initiating_user_id = $initiating_user_id;
	}


	/**
	 * The Aggregate this event belongs to.
	 *
	 * @return DomainObjectId
	 */
	public function getAggregateId(): DomainObjectId {
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
	 * @return ilDateTime
	 */
	public function getOccurredOn(): ilDateTime {
		return $this->occurred_on;
	}


	/**
	 * @return int
	 */
	public function getInitiatingUserId(): int {
		return $this->initiating_user_id;

	}


	/**
	 * @return string
	 */
	public function getEventBody(): string {
		return json_encode($this);
	}


	public abstract function restoreEventBody(string $json_data);
}