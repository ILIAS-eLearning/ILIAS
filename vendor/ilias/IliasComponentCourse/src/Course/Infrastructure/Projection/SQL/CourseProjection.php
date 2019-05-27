<?php
namespace srag\IliasComponentCourse\Course\Query\Projection\SQL;

use ilObjCourse;
use srag\IliasComponentCourse\Course\Command\Event\CourseMemberWasAdded;
use srag\IliasComponentCourse\Course\Command\Event\CourseEventBus;
use srag\Course\Command\Aggregate\Course;

class CourseProjection
{

	/**
	 * Constructor.
	 *
	 * @param \PDO              $aPdo       The pdo instance
	 * @param CourseEventBus|null $event_bus
	 */
	public function __construct($event_bus)
	{
		$this->event_bus = $event_bus;
	}


	public function persist(Course $course)
	{
		if ($this->event_bus instanceof CourseEventBus) {
			$this->handle($course->events());
		}
	}

	public function handle($events) {
		foreach ($events as $event) {
			$this->event_bus->handle($event);
		}
	}


	public function ProjectCourseMemberWasAdded(CourseMemberWasAdded $event) {

		//TODO -> Queries!

		$course = new ilObjCourse($event->getId(), false);

		$course->getMembersObject()->add($event->getUsrId(), IL_CRS_MEMBER);
	}
}