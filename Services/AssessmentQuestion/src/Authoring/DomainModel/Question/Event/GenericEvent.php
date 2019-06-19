<?php
//TODO wird verwendet um von der History wieder den aktallen Stand herzustellen. prüfen wie dies andere tun!
namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event;

use ILIAS\Data\Domain\Entity\AggregateId;
use ILIAS\Data\Domain\Event\AbstractDomainEvent;

class GenericEvent extends AbstractDomainEvent {

	protected $event_name;


	/**
	 * @return AggregateId
	 */
	public function getAggregateId(): AggregateId {
		return $this->aggregate_id;
	}


	/**
	 * @param AggregateId $aggregate_id
	 */
	public function setAggregateId(AggregateId $aggregate_id): void {
		$this->aggregate_id = $aggregate_id;
	}


	/**
	 * @return int
	 */
	public function getOccurredOn(): int {
		return $this->occurred_on;
	}


	/**
	 * @param int $occurred_on
	 */
	public function setOccurredOn(int $occurred_on): void {
		$this->occurred_on = $occurred_on;
	}


	/**
	 * @return int
	 */
	public function getInitatingUserId(): int {
		return $this->initating_user_id;
	}


	/**
	 * @param int $initating_user_id
	 */
	public function setInitatingUserId(int $initating_user_id): void {
		$this->initating_user_id = $initating_user_id;
	}

	/**
	 * @return string
	 *
	 * Add a Constant EVENT_NAME to your class: Name it: [aggregate].[event]
	 * e.g. 'question.created'
	 */
	public function getEventName(): string {
		return $this->event_name;
	}

	/**
	 * @return string
	 *
	 * Add a Constant EVENT_NAME to your class: Name it: [aggregate].[event]
	 * e.g. 'question.created'
	 */
	public function setEventName($event_name) {
		$this->event_name = $event_name;
	}

	public function setEventBody($event_body) {
		$this->event_body = $event_body;
	}


	/**
	 * @return string
	 */
	public function getEventBody():string {
		return $this->event_body;
	}

}