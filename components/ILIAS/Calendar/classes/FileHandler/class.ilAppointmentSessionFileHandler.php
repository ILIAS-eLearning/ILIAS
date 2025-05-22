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
 * Session appointment file handler
 * @author  Alex Killing <killing@leifos.de>
 * @ingroup ServicesCalendar
 */
class ilAppointmentSessionFileHandler extends ilAppointmentBaseFileHandler implements ilAppointmentFileHandler
{
    /**
     * Get files (for appointment)
     * @param
     * @return ilFileProperty[]
     */
    public function getFiles(): array
    {
        $cat_info = $this->getCatInfo();

        $eventItems = ilObjectActivation::getItemsByEvent($cat_info['obj_id']);
        $files = [];
        foreach ($eventItems as $obj) {
            if ($obj["type"] == "file") {
                if ($this->access->checkAccessOfUser($this->user->getId(), "read", "", (int)$obj['ref_id'])) {
                    $file = new ilObjFile((int)$obj['ref_id']);
                    $file_property = new ilFileProperty();
                    $file_property->setAbsolutePath($file->getFile());
                    $file_property->setFileName($file->getFileName());
                    $files[] = $file_property;
                }
            }
        }
        return $files;
    }
}
