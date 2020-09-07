<?php

include_once("./Services/Calendar/classes/Appointment/class.ilCalendarAppointmentBaseFactory.php");

/**
 *
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 *
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationFactory extends ilCalendarAppointmentBaseFactory
{
    public static function getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item)
    {
        global $DIC;

        $lng = $DIC['lng'];

        include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');

        //get object info
        $cat_id = ilCalendarCategoryAssignments::_lookupCategory($a_appointment['event']->getEntryId());
        //echo "---";
        //var_dump($cat_id);
        //$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);
        $cat = ilCalendarCategory::getInstanceByCategoryId($cat_id);
        $cat_info["type"] = $cat->getType();
        $cat_info["obj_id"] = $cat->getObjId();
        //var_dump($cat_info['obj_id']);
        //var_dump(ilObject::_lookupType($cat_info['obj_id']));
        //ilUtil::printBacktrace(10); exit;

        $class_base = self::getClassBaseName($a_appointment);

        $class_name = "ilAppointmentPresentation" . $class_base . "GUI";
        require_once "./Services/Calendar/classes/AppointmentPresentation/class." . $class_name . ".php";

        return $class_name::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
    }
}
