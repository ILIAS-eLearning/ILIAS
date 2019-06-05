<?php
namespace ILIAS\Messaging\Example\ExampleCourse\Infrastructure\Persistence\InMemory;
use ILIAS\Data\Domain\RecordsEvents;

class CourseRepository implements CourseRepositoryI {

	/**
	 * @var Course[]
	 */
	private $courses = [];

	/**
	 * @var EventStore
	 */
	private $event_store;
	/**
	 * @var courseProjection
	 */
	private $course_projection;

	public function __construct($event_store, $course_projection)
	{
		$this->event_store = $event_store;
		$this->course_projection = $course_projection;

	}


	public function add(RecordsEvents $aggregate) {

		$events = $aggregate->getRecordedEvents();
		$this->event_store->commit($events);
		$this->course_projection->project($events);

	}


	public function remove(Course $course) {
		//unset($this->courses[$course->id()->id()]);
	}


	/**
	 * @param int $course_id
	 *
	 * @return Course
	 */
	public function get(int $course_id) {
		//TODO
		if(is_null($this->courses[$course_id])) {
			$this->courses[$course_id] = new Course($course_id, array());
		}

		return $this->courses[$course_id];
	}


	private function filterCourses(callable $fn) {
		return array_values(array_filter($this->courses, $fn));
	}


	public function nextIdentity() {
		//return new ();
	}


	public function addAll(array $courses) {
		// TODO: Implement addAll() method.
	}


	public function removeAll(array $courses) {
		// TODO: Implement removeAll() method.
	}
}
