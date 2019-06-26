<?php
namespace  ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence\ilDB\ilDBQuestionEventStore;
use ILIAS\Data\Domain\Entity\AggregateRoot;
use ILIAS\Data\Domain\Event\{DomainEvents, EventStore, IsEventSourced, RecordsEvents};
use ILIAS\AssessmentQuestion\Authoring\Domainmodel\Question\QuestionProjection;
use ILIAS\AssessmentQuestion\Authoring\Domainmodel\Question\QuestionEventSourcedAggregateRepositoryRepository;
use ILIAS\Data\Domain\Repository\AggregateRepository;

class QuestionRepository extends AggregateRepository
{
	/**
	 * @var EventStore
	 */
	private $event_store;

	public function __construct()
	{
		parent::__construct();
		$this->event_store = new ilDBQuestionEventStore();
	}

	/**
	 * @return EventStore
	 */
	protected function getEventStore(): EventStore {
		return $this->event_store;
	}


	/**
	 * @param DomainEvents $event_history
	 *
	 * @return AggregateRoot
	 */
	protected function reconstituteAggregate(DomainEvents $event_history): AggregateRoot {
		return Question::reconstitute($event_history);
	}
}
