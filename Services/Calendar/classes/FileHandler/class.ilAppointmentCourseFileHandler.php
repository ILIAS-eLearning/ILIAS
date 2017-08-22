<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Calendar/interfaces/interface.ilAppointmentFileHandler.php");
include_once("./Services/Calendar/classes/FileHandler/class.ilAppointmentBaseFileHandler.php");

/**
 * Course appointment file handler
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ServicesCalendar
 */
class ilAppointmentCourseFileHandler extends ilAppointmentBaseFileHandler implements ilAppointmentFileHandler
{
	/**
	 * Get files (for appointment)
	 *
	 * @param
	 * @return
	 */
	function getFiles()
	{
		include_once "./Modules/Course/classes/class.ilCourseFile.php";
		$cat_info = $this->getCatInfo();
		$course_files = ilCourseFile::_readFilesByCourse($cat_info['obj_id']);

		$files = array();
		foreach ($course_files as $course_file)
		{
			$course_file['ref_id']
			//TODO check user access permission
			//if ($this->access->checkAccessOfUser($this->user->getId(), "read", "", $obj['ref_id']))
			//{
				$files[] = $course_file->getInfoDirectory()."/".$course_file->getFileName();
			//}
		}
		return $files;
	}

}