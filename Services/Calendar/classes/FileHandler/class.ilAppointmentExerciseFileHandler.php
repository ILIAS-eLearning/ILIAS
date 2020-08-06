<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Calendar/interfaces/interface.ilAppointmentFileHandler.php");
include_once("./Services/Calendar/classes/FileHandler/class.ilAppointmentBaseFileHandler.php");

/**
 * Exercise appointment file handler
 *
 * @author Jesús López Reyes <lopez@leifos.de>
 * @ingroup ServicesCalendar
 */
class ilAppointmentExerciseFileHandler extends ilAppointmentBaseFileHandler implements ilAppointmentFileHandler
{
    /**
     * Get files (for appointment)
     *
     * @param
     * @return array $files
     */
    public function getFiles()
    {
        global $DIC;

        $user_id = $DIC->user()->getId();

        $ass_id = $this->appointment['event']->getContextId() / 10;			// see ilExAssignment->handleCalendarEntries $dl parameter
        $assignment = new ilExAssignment($ass_id);
        $ass_files = $assignment->getFiles();
        $files = array();
        $state = ilExcAssMemberState::getInstanceByIds($assignment->getId(), $user_id);
        if (count($ass_files) && $state->areInstructionsVisible()) {
            foreach ($ass_files as $ass_file) {
                $files[] = $ass_file['fullpath'];
            }
        }
        return $files;
    }
}
