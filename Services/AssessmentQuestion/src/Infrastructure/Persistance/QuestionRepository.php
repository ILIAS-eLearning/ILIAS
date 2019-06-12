<?php
namespace  ILIAS\AssessmentQuestion\Infrastructure\Persistence;
use ILIAS\Data\Domain\IdentifiesAggregate;
use ILIAS\Data\Domain\IsEventSourced;
use ILIAS\Data\Domain\RecordsEvents;
use ILIAS\AssessmentQuestion\Domainmodel\Question\Question;
use ILIAS\AssessmentQuestion\Domainmodel\Question\QuestionProjection;
use ILIAS\AssessmentQuestion\Domainmodel\Question\QuestionAggregateRepositoryRepository;

class QuestionRepository implements QuestionAggregateRepositoryRepository
{
	/**
	 * @var EventStore
	 */
	private $event_store;
	/**
	 * @var QuestionProjection
	 */
	private $projection;
	public function __construct($event_store, $projection)
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
