<?php

namespace srag\Course\Command\Aggregate\Course;

use srag\IliasComponent\Context\Command\Aggregate\Aggregate;
use srag\IliasComponent\Context\Command\Aggregate\Entity;
use srag\IliasComponent\Context\Command\Event;
use srag\IliasComponentCourse\Course\Command\Event\CourseEvent;

/**
 * Class Course
 *
 * @package srag\IliasComponentCourse\Course\Course\Query\Entity
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class Course implements Entity, Aggregate {

	/**
	 * @var int
	 */
	private $id = 0;
	/**
	 * @var CourseAdmin[]
	 */
	private $admins;
	/**
	 * @var CourseMember[]
	 */
	private $members;
	/**
	 * @var string
	 */
	private $type = "crs";
	/**
	 * @var string
	 */
	private $title;
	/**
	 * @var string
	 */
	private $description;
	/**
	 * @var int
	 */
	private $owner;
	/**
	 * @var \DateTime
	 */
	private $create_date;

	/**
	 * @var \DateTime
	 */
	private $last_update;
	/**
	 * @var string
	 */
	private $import_id = "";
	/**
	 * @var bool
	 */
	private $offline = false;
	/**
	 * Array which contains the domain events.
	 *
	 * @var array
	 */
	private $events = [];


	public function addCourseMember(CourseMember $course_member)
    {
        $this->publish(
            new CourseMemberWasAdded($course_member->getCourseId(),$course_member->getUsrId())
        );
    }

	/**
	 * Clears the events container.
	 */
	public function eraseEvents()
	{
		$this->events = [];
	}
	/**
	 * Gets the recorded domain events.
	 *
	 * @return array
	 */
	public function events()
	{
		return $this->events;
	}
	/**
	 * Publishes the domain event.
	 *
	 * @param CourseEvent $event The domain event
	 */
	protected function publish(CourseEvent $event)
	{
		$this->record($event);
	}
	/**
	 * Saves the given domain event inside event container.
	 *
	 * @param CourseEvent $event The domain event
	 */
	private function record(CourseEvent $event)
	{
		$this->events[] = $event;
	}
}
