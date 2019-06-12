<?php
namespace ILIAS\AssessmentQuestion\Domainmodel\Event;

use ILIAS\Data\Domain\DomainEvent;
use ILIAS\Data\Domain\IdentifiesAggregate;

class QuestionWasCreated implements DomainEvent
{
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

	/**
	 * The Aggregate this event belongs to.
	 *
	 * @return IdentifiesAggregate
	 */
	public function getAggregateId(): IdentifiesAggregate {
		return $this->aggregate_id;
	}
}