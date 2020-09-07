<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Calendar/interfaces/interface.ilAppointmentFileHandler.php");
include_once("./Services/Calendar/classes/FileHandler/class.ilAppointmentBaseFileHandler.php");

/**
 * Consultation Hours appointment file handler
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ServicesCalendar
 */
class ilAppointmentConsultationHoursFileHandler extends ilAppointmentBaseFileHandler implements ilAppointmentFileHandler
{
    /**
     * Get files (for appointment)
     *
     * @param
     * @return
     */
    public function getFiles()
    {
        return array();
    }
}
