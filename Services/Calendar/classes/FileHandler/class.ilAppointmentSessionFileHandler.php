<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Calendar\FileHandler\ilFileProperty;

include_once("./Services/Calendar/interfaces/interface.ilAppointmentFileHandler.php");
include_once("./Services/Calendar/classes/FileHandler/class.ilAppointmentBaseFileHandler.php");

/**
 * Session appointment file handler
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ServicesCalendar
 */
class ilAppointmentSessionFileHandler extends ilAppointmentBaseFileHandler implements ilAppointmentFileHandler
{
    /**
     * Get files (for appointment)
     *
     * @param
     * @return ilFileProperty[]
     */
    public function getFiles() : array
    {
        $cat_info = $this->getCatInfo();

        include_once("./Services/Object/classes/class.ilObjectActivation.php");
        $eventItems = ilObjectActivation::getItemsByEvent($cat_info['obj_id']);
        $files = [];
        foreach ($eventItems as $obj) {
            if ($obj["type"] == "file") {
                if ($this->access->checkAccessOfUser($this->user->getId(), "read", "", $obj['ref_id'])) {
                    $file = new ilObjFile($obj['ref_id']);
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
