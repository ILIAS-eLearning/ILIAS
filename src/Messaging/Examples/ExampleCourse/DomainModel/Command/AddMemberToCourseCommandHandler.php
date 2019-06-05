<?php

namespace ILIAS\Messaging\Example\ExampleCourse\Command;

class addCourseMemberToCourseCommandHandler implements CommandHandler {

	/**
	 * @var CourseRepository
	 */
	private $course_repository;


	public function __construct($course_repository) {


		$this->course_repository();
		//TODO
		/*
	$mongo_manager = new  MongoDB\Driver\Manager("mongodb://localhost:27017");

	$collection = new MongoDB\Collection($mongo_manager, "ilias", "course", array());


	$this->course_repository = new InMemoryCourseRepository(new InMemoryEventStore(),new CourseProjection($collection));
		*/
	}


	/**
	 * @param addCourseMemberToCourseCommand $command
	 */
	public function handle(Command $command) {
		$course = $this->course_repository->get($command->course_id);

		$course->addCourseMember($command->user_id);
		$this->course_repository->add($course);
	}
}
