<?php
namespace  ILIAS\AssessmentQuestion\AuthoringInfrastructure\Persistence;
use ILIAS\Data\Domain\Entity\AggregateId;
use ILIAS\Data\Domain\Event\IsEventSourced;
use ILIAS\Data\Domain\Event\RecordsEvents;
use ILIAS\AssessmentQuestion\Authoring\Domainmodel\Question\Question;
use ILIAS\AssessmentQuestion\Authoring\Domainmodel\Question\QuestionProjection;
use ILIAS\AssessmentQuestion\Authoring\Domainmodel\Question\QuestionEventSourcedAggregateRepositoryRepository;

class QuestionRepository implements QuestionEventSourcedAggregateRepositoryRepository
{
	/**
	 * @var EventStore
	 */
	private $event_store;
	/**
	 * @var QuestionProjection
	 */
	private $projection;

	public function __construct()
	{
		$this->event_store = $event_store;
		$this->projection = $projection;
	}

	/**
	 * @param IdentifiesAggregate $aggregateId
	 * @return IsEventSourced
	 */
	public function get(IdentifiesAggregate $aggregate_id)
	{
		$aggregate_history = $this->eventStore->getAggregateHistoryFor($aggregate_id);
		return Question::reconstituteFrom($aggregate_history);
	}
	/**
	 * @param RecordsEvents $aggregate
	 * @return void
	 */
	public function add(RecordsEvents $aggregate)
	{
		$events = $aggregate->getRecordedEvents();
		$this->event_store->commit($events);
		$this->projection->project($events);
		$aggregate->clearRecordedEvents();
	}
}
