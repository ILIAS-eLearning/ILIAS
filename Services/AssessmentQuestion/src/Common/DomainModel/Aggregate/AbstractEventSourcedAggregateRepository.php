<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate;

use ilGlobalCache;

/**
 * Class AbstractAggregateRepository
 *
 * @package ILIAS\Data\Domain\Repository
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class AbstractEventSourcedAggregateRepository {

	const CACHE_NAME = "CQRS_REPOSITORY_CACHE";
	/**
	 * @var ilGlobalCache
	 */
	private static $cache;
	/**
	 * @var bool
	 */
	private $has_cache = false;


	protected function __construct() {
		if (self::$cache === null) {
			self::$cache = ilGlobalCache::getInstance(self::CACHE_NAME);
			self::$cache->setActive(true);
		}

		$this->has_cache = self::$cache !== null && self::$cache->isActive();
	}


	public function save(AbstractEventSourcedAggregateRoot $aggregate) {
		$events = $aggregate->getRecordedEvents();
		$this->getEventStore()->commit($events);
		$aggregate->clearRecordedEvents();

		if ($this->has_cache) {
			self::$cache->set($aggregate->getAggregateId()->getId(), $aggregate);
		}

		$this->notifyAboutNewEvents();
	}


	public function get(AggregateId $aggregate_id) {
		if ($this->has_cache) {
			return $this->getFromCache($aggregate_id);
		} else {
			$this->reconstituteAggregate($this->getEventStore()->getAggregateHistoryFor($aggregate_id));
		}
	}


	private function getFromCache(AggregateId $aggregate_id) {
		$cache_key = $aggregate_id->getId();
		$aggregate = self::$cache->get($cache_key);
		if ($aggregate === null) {
			$aggregate = $this->reconstituteAggregate($this->getEventStore()->getAggregateHistoryFor($aggregate_id));
			self::$cache->set($cache_key, $aggregate);
		}

		return $aggregate;
	}


	/**
	 * Method called to alert known consumers to a new event
	 */
	public function notifyAboutNewEvents() {
		//Virtual Method
	}


	public abstract static function getInstance();


	protected abstract function getEventStore(): EventStore;
}