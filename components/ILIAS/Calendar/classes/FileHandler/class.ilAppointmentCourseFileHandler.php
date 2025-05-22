<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

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
