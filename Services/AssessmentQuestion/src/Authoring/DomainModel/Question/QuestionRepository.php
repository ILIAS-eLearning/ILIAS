<?php
namespace  ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence\ilDB\ilDBQuestionEventStore;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractAggregateRoot;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractEventSourcedAggregateRepository;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AggregateRoot;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event\DomainEvents;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event\EventStore;

class QuestionRepository extends AbstractEventSourcedAggregateRepository
{
	/**
	 * @var EventStore
	 */
	private $event_store;
	/**
	 * @var QuestionRepository
	 */
	private static $instance;

	protected function __construct()
	{
		parent::__construct();
		$this->event_store = new ilDBQuestionEventStore();
	}

	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new QuestionRepository();
		}

		return self::$instance;
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
	 * @return \ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AggregateRoot
	 */
	protected function reconstituteAggregate(DomainEvents $event_history): AggregateRoot {
		return Question::reconstitute($event_history);
	}
}
