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
    public function getFiles()
    {
        $cat_info = $this->getCatInfo();

        //checking permissions of the parent object.
        // get course ref id (this is possible, since courses only have one ref id)
        $refs = ilObject::_getAllReferences($cat_info['obj_id']);
        $crs_ref_id = current($refs);

        $files = array();
        if ($this->access->checkAccessOfUser($this->user->getId(), "read", "", $crs_ref_id)) {
            include_once "./Modules/Course/classes/class.ilCourseFile.php";
            $course_files = ilCourseFile::_readFilesByCourse($cat_info['obj_id']);

            foreach ($course_files as $course_file) {
                $file_name_system_path = $course_file->getAbsolutePath();
                $file_name_web = $course_file->getInfoDirectory() . "/" . $course_file->getFileName();
                $files[$file_name_system_path] = $file_name_web;
            }
        }

        return $files;
    }
}
