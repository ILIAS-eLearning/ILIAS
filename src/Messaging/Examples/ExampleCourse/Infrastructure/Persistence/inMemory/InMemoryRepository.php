<?php
namespace  ILIAS\Messaging\Example\ExampleCourse\Infrastructure\Persistence\InMemory;

use ILIAS\Data\Domain\RecordsEvents;
use ILIAS\Messaging\Example\ExampleCourse\Domainmodel\Aggregate\Course;
use ILIAS\Messaging\Example\ExampleCourse\Domainmodel\Aggregate\CourseRepository;

class InMemoryRepository implements CourseRepository {

	public function add(RecordsEvents $aggregate) {
		// TODO: Implement add() method.
	}


	public function get(int $course_id) {
		// TODO: Implement get() method.
	}


	public function addAll(array $courses) {
		// TODO: Implement addAll() method.
	}


	public function remove(Course $course) {
		// TODO: Implement remove() method.
	}


	public function removeAll(array $courses) {
		// TODO: Implement removeAll() method.
	}
}