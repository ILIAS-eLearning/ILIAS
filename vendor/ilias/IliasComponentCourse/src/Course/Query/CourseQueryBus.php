<?php

namespace srag\IliasComponentCourse\Course\Query;

interface CourseQueryBus
{
	/**
	 * Executes the given query.
	 *
	 * @param mixed $query  The query given
	 *
	 */
	public function handle($query);
}