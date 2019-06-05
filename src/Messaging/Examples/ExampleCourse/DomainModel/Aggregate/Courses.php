<?php

namespace ILIAS\Messaging\Example\ExampleCourse\Domainmodel\Aggregate;

use ilException;
use ILIAS\Data\Domain\AggregateRoot;
use ILIAS\Data\Domain\DomainEvent;
use ILIAS\Messaging\Example\ExampleCourse\Command\Events\CourseMemberWasAdded;
use ILIAS\Data\Domain\RecordsEvents;
use ILIAS\Data\Domain\AggregateHistory;
use ILIAS\Data\Domain\DomainEvents;
use ILIAS\Data\Domain\IdentifiesAggregate;

class Course implements AggregateRoot {

	/** @var DomainEvent[] */
	private $recorded_events = [];
	/**
	 * @var IdentifiesAggregate
	 */
	private $course_id;
	/**
	 * @var CourseMember[]
	 */
	private $course_members;


	public function __construct(IdentifiesAggregate $course_id, array $course_members = []) {
		$this->course_id = $course_id;
		$this->course_members = $course_members;
	}


	/**
	 * @param $usr_id
	 */
	public function addCourseMember($usr_id) {
		if (!$this->hasManageMemberPermission()) {
			//TODO
			new ilException('No Permission');
		}

		$this->course_members[] = new CourseMember($this, $usr_id);

		$this->recordThat(new CourseMemberWasAdded($this->course_id, $usr_id));
	}


	private function hasManageMemberPermission(): bool {
		return true;
	}


	public function course_id() {
		return $this->course_id;
	}


	public function course_members() {
		return $this->course_members;
	}


	public static function reconstituteFrom(AggregateHistory $aggregate_history): RecordsEvents {
		$aggregate = static::createInstanceForGivenHistory($aggregate_history);
		foreach ($aggregate_history as $event) {
			$aggregate->apply($event);
		}

		return $aggregate;
	}


	private function apply($anEvent) {
		$method = 'apply' . short($anEvent);
		$this->$method($anEvent);
	}


	private function applyCourseMemberWasAdded(CourseMemberWasAdded $event) {
		$this->course_id = $event->getAggregateId();
		//$this->course_members[] = $event->getUsrId();
	}


	// ---------------------------------------------------------------------

	protected static function createInstanceForGivenHistory(AggregateHistory $aggregate_history) {
		return new static($aggregate_history->getAggregateId(), array());
	}


	public function getRecordedEvents(): DomainEvents {
		return new DomainEvents($this->recorded_events);
	}


	public function clearRecordedEvents(): void {
		$this->recorded_events = [];
	}


	protected function recordThat(DomainEvent $domainEvent): void {
		$this->recorded_events[] = $domainEvent;
		$this->apply($domainEvent);
	}


	public function getAggregateId(): IdentifiesAggregate {
		// TODO: Implement getAggregateId() method.
	}


	public function hasChanges(): bool {
		return false;
		// TODO: Implement hasChanges() method.
	}
}


/*
	 *
	 *
	 * https://github.com/jkuchar/talk-EventSourcing101-Vienna-2019-02-21/blob/master/src/EventSourcing/AbstractAggregate.php
	 *https://github.com/slavkoss/fwphp/blob/181f989a0ef215469734699835f8dea5f95349f9/fwphp/glomodul4/help_sw/test/ddd/blog/src/CQRSBlog/BlogEngine/DomainModel/Post.php
	 */
