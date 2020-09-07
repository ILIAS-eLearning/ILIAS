<?php

include_once("./Services/Calendar/interfaces/interface.ilAppointmentFileHandler.php");
include_once("./Services/Calendar/classes/FileHandler/class.ilAppointmentBaseFileHandler.php");

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Booking Pool appointment file handler
 *
 * @author Jesús López Reyes <lopez@leifos.com>
 * @ingroup ServicesCalendar
 */

class ilAppointmentBookingPoolFileHandler extends ilAppointmentBaseFileHandler implements ilAppointmentFileHandler
{
    /**
     * Get files (for appointment)*
     * @return array of strings which contain files full path
     */
    public function getFiles()
    {
        // context id is reservation id (see ilObjBookingPoolGUI->processBooking)
        $res_id = $this->appointment['event']->getContextId();
        include_once("./Modules/BookingManager/classes/class.ilBookingReservation.php");
        include_once("./Modules/BookingManager/classes/class.ilBookingObject.php");
        $res = new ilBookingReservation($res_id);
        $b_obj = new ilBookingObject($res->getObjectId());

        return array($b_obj->getFileFullPath(), $b_obj->getPostFileFullPath());
    }
}
