<?php

include_once("./Services/Calendar/classes/Appointment/class.ilCalendarAppointmentBaseFactory.php");

/**
 *
 * @author Alex Killing <killing@leifos.com>
 *
 * @ingroup ServicesCalendar
 */
class ilAppointmentFileHandlerFactory extends ilCalendarAppointmentBaseFactory
{
    public static function getInstance($a_appointment)
    {
        include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
        $cat_id = ilCalendarCategoryAssignments::_lookupCategory($a_appointment['event']->getEntryId());
        $cat = ilCalendarCategory::getInstanceByCategoryId($cat_id);
        $cat_info["type"] = $cat->getType();
        $cat_info["obj_id"] = $cat->getObjId();
        $class_base = self::getClassBaseName($a_appointment);


        // todo: provide more implementations
        if (!in_array($class_base, array("Session", "Course", "ConsultationHours", "Exercise", "BookingPool"))) {
            $class_base = "Dummy";
        }


        $class_name = "ilAppointment" . $class_base . "FileHandler";
        require_once "./Services/Calendar/classes/FileHandler/class." . $class_name . ".php";

        return $class_name::getInstance($a_appointment);
    }
}
