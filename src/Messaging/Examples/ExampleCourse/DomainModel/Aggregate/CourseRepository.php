<?php

namespace ILIAS\Messaging\Example\ExampleCourse\Domainmodel\Aggregate;

use ILIAS\Data\Domain\RecordsEvents;

interface CourseRepository {

	public function add(RecordsEvents $aggregate);

	//TODO UUI


	/**
	 * @param int $course_id
	 *
	 * @return Course
	 */
	public function get(int $course_id);


	public function addAll(array $courses);


	public function remove(Course $course);


	public function removeAll(array $courses);
}
