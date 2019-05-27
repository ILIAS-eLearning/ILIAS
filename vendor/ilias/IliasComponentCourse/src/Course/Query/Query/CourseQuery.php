<?php
namespace srag\IliasComponentCourse\Course\Query\Query;

class CourseQuery
{
	/**
	 * @var string
	 */
	private $id;
	public function __construct($id)
	{
		$this->id = $id;
	}
	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}
}