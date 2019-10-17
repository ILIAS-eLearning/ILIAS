<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\CQRS\Aggregate;

use ilGlobalCache;
use ILIAS\AssessmentQuestion\CQRS\Event\DomainEvents;
use ILIAS\AssessmentQuestion\CQRS\Event\EventStore;

/**
 * Class AbstractEventSourcedAggregateRepository
 *
 * @package ILIAS\Data\Domain\Repository
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class AbstractEventSourcedAggregateRepository implements AggregateRepository {

	const CACHE_NAME = "CQRS_REPOSITORY_CACHE";
	/**
	 * @var ilGlobalCache
	 */
	private static $cache;
	/**
	 * @var bool
	 */
	private $has_cache = false;

	/**
	 * @var array
	 */
	private $request_cache = [];

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

		$this->request_cache[$aggregate->getAggregateId()->getId()] = $aggregate;
		
		if ($this->has_cache) {
			self::$cache->set($aggregate->getAggregateId()->getId(), $aggregate);
		}

		$this->notifyAboutNewEvents();
	}


	public function getAggregateRootById(DomainObjectId $aggregate_id) : AggregateRoot {
	    $id_string = $aggregate_id->getId();
	    
	    if (empty($this->request_cache[$id_string])) {
	        if ($this->has_cache) {
	            $this->request_cache[$id_string] = $this->getFromCache($aggregate_id);
	        } else {
	            $this->request_cache[$id_string] = $this->reconstituteAggregate($this->getEventStore()->getAggregateHistoryFor($aggregate_id));
	        }
	    }
	    
	    return $this->request_cache[$id_string];
	}


	private function getFromCache(DomainObjectId $aggregate_id) {
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


	/**
	 * @param DomainEvents $event_history
	 *
	 * @return AggregateRoot
	 */
	protected abstract function reconstituteAggregate(DomainEvents $event_history): AggregateRoot;
}