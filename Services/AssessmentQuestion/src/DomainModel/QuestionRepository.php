<?php

namespace ILIAS\AssessmentQuestion\DomainModel;



use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractEventSourcedAggregateRepository;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\AggregateRoot;
use ILIAS\AssessmentQuestion\CQRS\Event\DomainEvents;
use ILIAS\AssessmentQuestion\CQRS\Event\EventStore;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\EventStore\QuestionEventStoreRepository;

/**
 * Class QuestionRepository
 *
 * @package ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionRepository extends AbstractEventSourcedAggregateRepository {

	/**
	 * @var EventStore
	 */
	private $event_store;
	/**
	 * @var QuestionRepository
	 */
	private static $instance;

    /**
     * QuestionRepository constructor.
     */
	protected function __construct() {
		parent::__construct();
		$this->event_store = new QuestionEventStoreRepository();
	}

    /**
     * @return QuestionRepository
     */
	public static function getInstance() {
		if (self::$instance === null) {
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
     * @return AggregateRoot
     */
	protected function reconstituteAggregate(DomainEvents $event_history): AggregateRoot {
		return Question::reconstitute($event_history);
	}
	
	/**
	 * @return int
	 */
	public function getNextId() : int {
	    return $this->event_store->getNextId();
	}
}
