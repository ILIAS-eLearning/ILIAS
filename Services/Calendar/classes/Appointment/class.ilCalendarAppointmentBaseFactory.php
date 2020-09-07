<?php
/**
 *
 * @author Alex Killing <killing@leifos.com>
 * @version $Id$
 *
 *
 * @ingroup ServicesCalendar
 */
class ilCalendarAppointmentBaseFactory
{
    public static function getClassBaseName($a_appointment)
    {
        include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
        $cat_id = ilCalendarCategoryAssignments::_lookupCategory($a_appointment['event']->getEntryId());
        $cat = ilCalendarCategory::getInstanceByCategoryId($cat_id);
        $cat_info["type"] = $cat->getType();
        $cat_info["obj_id"] = $cat->getObjId();

        if ($a_appointment['event']->isMilestone()) {
            return "Milestone";
        }

        switch ($cat_info['type']) {
            case ilCalendarCategory::TYPE_OBJ:
                $type = ilObject::_lookupType($cat_info['obj_id']);
                switch ($type) {
                    case "crs":
                        return "Course";

                    case "grp":
                        return "Group";

                    case "sess":
                        return "Session";

                    case "exc":
                        return "Exercise";

                    default:
                        return "";
                }
                break;
            case ilCalendarCategory::TYPE_USR:
                return "User";

            case ilCalendarCategory::TYPE_GLOBAL:
                return "Public";

            case ilCalendarCategory::TYPE_CH:
                return "ConsultationHours";

            case ilCalendarCategory::TYPE_BOOK:
                return "BookingPool";

            default:
                return "";
        }
    }
}
