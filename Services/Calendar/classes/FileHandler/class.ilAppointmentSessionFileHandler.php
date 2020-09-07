<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

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
     * @return
     */
    public function getFiles()
    {
        $cat_info = $this->getCatInfo();

        //$session_obj = new ilObjSession($cat_info['obj_id'],false);

        include_once("./Services/Object/classes/class.ilObjectActivation.php");
        $eventItems = ilObjectActivation::getItemsByEvent($cat_info['obj_id']);
        $files = array();
        if (count($eventItems)) {
            foreach ($eventItems as $obj) {
                if ($obj["type"] == "file") {
                    if ($this->access->checkAccessOfUser($this->user->getId(), "read", "", $obj['ref_id'])) {
                        $file = new ilObjFile($obj['ref_id']);

                        // todo: this should be provided by an interface of ilObjFile
                        // currently this is copy/paste code from ilObjFile->sendFile
                        $filename = $file->getDirectory($file->getVersion()) . "/" . $file->getFileName();
                        if (@!is_file($filename)) {
                            $filename = $file->getDirectory() . "/" . $file->getFileName();
                        }
                        $files[] = $filename;
                    }
                }
            }
        }
        return $files;
    }
}
