<?php declare(strict_types=1);

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Calendar\FileHandler\ilFileProperty;

/**
 * Exercise appointment file handler
 * @author  Jesús López Reyes <lopez@leifos.de>
 * @ingroup ServicesCalendar
 */
class ilAppointmentExerciseFileHandler extends ilAppointmentBaseFileHandler implements ilAppointmentFileHandler
{
    /**
     * @inheritDoc
     */
    public function getFiles() : array
    {
        // see ilExAssignment->handleCalendarEntries $dl parameter
        $ass_id = $this->appointment['event']->getContextId() / 10;
        $assignment = new ilExAssignment($ass_id);
        $ass_files = $assignment->getFiles();
        $files = [];
        $state = ilExcAssMemberState::getInstanceByIds($assignment->getId(), $this->user->getId());
        if (count($ass_files) && $state->areInstructionsVisible()) {
            foreach ($ass_files as $ass_file) {
                $file_property = new ilFileProperty();
                $file_property->setAbsolutePath($ass_file['fullpath']);
                $file_property->setFileName($ass_file['name']);

                $files[] = $file_property;
            }
        }
        return $files;
    }
}
