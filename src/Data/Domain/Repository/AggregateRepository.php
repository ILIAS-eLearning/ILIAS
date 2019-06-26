<?php


namespace ILIAS\Data\Domain\Repository;

use ilGlobalCache;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Shared\QuestionId;
use ILIAS\Data\Domain\Entity\AggregateId;
use ILIAS\Data\Domain\Entity\AggregateRoot;
use ILIAS\Data\Domain\Entity\IsRevisable;
use ILIAS\Data\Domain\Entity\RevisionId;
use ILIAS\Data\Domain\Event\DomainEvents;
use ILIAS\Data\Domain\Event\EventStore;

/**
 * Interface AggregateRepository
 * @package ILIAS\Data\Domain
 */
abstract class AggregateRepository {

	const CACHE_NAME = "CQRS REPOSITORY CACHE";

	/**
	 * @var ilGlobalCache
	 */
	private static $cache;
	/**
	 * @var bool
	 */
	private $has_cache = false;

	public function __construct() {
		if (self::$cache === null)
		{
			self::$cache = ilGlobalCache::getInstance(self::CACHE_NAME);
			self::$cache->setActive(true);
		}

		$this->has_cache = self::$cache !== null && self::$cache->isActive();
	}



	public function save(AggregateRoot $aggregate) {
		$events = $aggregate->getRecordedEvents();
		$this->getEventStore()->commit($events);
		$aggregate->clearRecordedEvents();

		if($this->has_cache)
		{
			self::$cache->set($aggregate->getAggregateId()->getId(), $aggregate);
		}
	}

	public function get(AggregateId $aggregate_id)
	{
		if ($this->has_cache) {
			return $this->getFromCache($aggregate_id);
		}
		else {
			$this->reconstituteAggregate($this->getEventStore()->getAggregateHistoryFor($aggregate_id));
		}
	}



	private function getFromCache(AggregateId $aggregate_id)
	{
		$cache_key = $aggregate_id->getId();
		if (self::$cache->exists($cache_key)) {
			$aggregate = $this->reconstituteAggregate($this->getEventStore()->getAggregateHistoryFor($aggregate_id));
			self::$cache->set($cache_key, $aggregate);
			return $aggregate;
		}
		return self::$cache->get($cache_key);
	}

	protected abstract function getEventStore() : EventStore;

	protected abstract function reconstituteAggregate(DomainEvents $event_history) : AggregateRoot;
}