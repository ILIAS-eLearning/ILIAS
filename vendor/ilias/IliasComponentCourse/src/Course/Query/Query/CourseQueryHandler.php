<?php
namespace srag\IliasComponentCourse\Course\Query\Query;

use srag\IliasComponentCourse\Course\Query\CourseReadOnlyRepository;

class CourseQueryHandler
{
	/**
	 * @var CourseReadOnlyRepository
	 */
	private $course_view_repository;

	public function __construct($course_view_repository)
	{
		$this->course_view_repository = $course_view_repository;
	}
	public function handle(CourseQuery $course_query)
	{
		return $this->course_view_repository->get($course_query->getId());
	}
}