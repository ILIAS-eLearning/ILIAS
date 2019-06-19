<?php
namespace  ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Shared\QuestionId;
use ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence\ilDB\ilDBQuestionEventStore;
use ILIAS\Data\Domain\Entity\AggregateId;
use ILIAS\Data\Domain\Entity\AggregateRevision;
use ILIAS\Data\Domain\Event\IsEventSourced;
use ILIAS\Data\Domain\Event\RecordsEvents;
use ILIAS\AssessmentQuestion\Authoring\Domainmodel\Question\Question;
use ILIAS\AssessmentQuestion\Authoring\Domainmodel\Question\QuestionProjection;
use ILIAS\AssessmentQuestion\Authoring\Domainmodel\Question\QuestionEventSourcedAggregateRepositoryRepository;
use  ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event;

class QuestionRepository
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
		$this->event_store = new ilDBQuestionEventStore();
		// TODO projection
		//$this->projection = $projection;
	}

	public function save(Question $question) {
		$events = $question->getRecordedEvents();
		$this->event_store->commit($events);
		//$this->projection->project($events);
		$question->clearRecordedEvents();
	}

	public function byId(QuestionId $id)
	{
		return Question::reconstitute(
			$this->event_store->getEventsFor($id)
		);
	}

/*
	public function byAggregateAndRevisionId(QuestionId $aggregate_id, AggregateRevision $aggregate_revision) {
		// TODO: Implement byAggregateAndRevisionId() method.
	}*/
}
