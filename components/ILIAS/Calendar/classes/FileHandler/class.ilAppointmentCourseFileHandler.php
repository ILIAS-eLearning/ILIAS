<?php

declare(strict_types=1);

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Calendar\FileHandler\ilFileProperty;

/**
 * Course appointment file handler
 * @author  Alex Killing <killing@leifos.de>
 * @ingroup ServicesCalendar
 */
class ilAppointmentCourseFileHandler extends ilAppointmentBaseFileHandler implements ilAppointmentFileHandler
{
    /**
     * @inheritDoc
     */
    public function getFiles(): array
    {
        $cat_info = $this->getCatInfo();

        //checking permissions of the parent object.
        // get course ref id (this is possible, since courses only have one ref id)
        $refs = ilObject::_getAllReferences($cat_info['obj_id']);
        $crs_ref_id = current($refs);

        $files = [];
        if ($this->access->checkAccessOfUser($this->user->getId(), "read", "", $crs_ref_id)) {
            $course_files = ilCourseFile::_readFilesByCourse($cat_info['obj_id']);
            foreach ($course_files as $course_file) {
                $file_property = new ilFileProperty();
                $file_property->setAbsolutePath($course_file->getAbsolutePath());
                $file_property->setFileName($course_file->getFileName());
                $files[] = $file_property;
            }
        }
        return $files;
    }
}
