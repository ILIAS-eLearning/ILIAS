<?php
namespace ILIAS\AssessmentQuestion\Domain\Question\Event;

use DateTime;
use ILIAS\Data\Domain\DomainEvent;
use ILIAS\Data\Domain\IdentifiesAggregate;

class QuestionCreatedEvent implements DomainEvent
{
	public const NAME = 'question.created';

	public $aggregate_id;
	public $title;
	public $description;
	public $state;
	public function __construct($aggregate_id,  $title, $description, $state)
	{
		$this->aggregate_id = $aggregate_id;
		$this->title = $title;
		$this->description = $description;
		$this->state = $state;
	}


	public function getAggregateId(): IdentifiesAggregate {
		// TODO: Implement getAggregateId() method.
	}


	public function getEventName(): string {
		// TODO: Implement getEventName() method.
	}


	public function getOccuredOn(): DateTime {
		// TODO: Implement getOccuredOn() method.
	}


	public function getInitiatingUserId(): int {
		// TODO: Implement getInitiatingUserId() method.
	}


	public function getEventBody(): string {
		// TODO: Implement getEventBody() method.
	}
}