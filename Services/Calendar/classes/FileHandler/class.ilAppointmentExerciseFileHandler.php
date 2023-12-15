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
 * Exercise appointment file handler
 * @author  Jesús López Reyes <lopez@leifos.de>
 * @ingroup ServicesCalendar
 */
class ilAppointmentExerciseFileHandler extends ilAppointmentBaseFileHandler implements ilAppointmentFileHandler
{
    /**
     * @inheritDoc
     */
    public function getFiles(): array
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
                $file_property->setFileRId($ass_file['rid']);
                $files[] = $file_property;
            }
        }
        return $files;
    }
}
