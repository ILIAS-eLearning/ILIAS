<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

/**
 * Fetches infos about the course templates a user could use to create courses.
 */
interface CourseTemplateDB {
	/**
	 * @param	int	$user_id
	 * @return CourseTemplateInfo[]
	 */
	public function getCreatableCourseTemplates($user_id);
}
