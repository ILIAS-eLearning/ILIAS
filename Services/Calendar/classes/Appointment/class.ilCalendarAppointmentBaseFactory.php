<?php

declare(strict_types=1);

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


/**
 * @author  Alex Killing <killing@leifos.com>
 * @ingroup ServicesCalendar
 */
class ilCalendarAppointmentBaseFactory
{
    public static function getClassBaseName($a_appointment): string
    {
        $cat_id = ilCalendarCategoryAssignments::_lookupCategory($a_appointment['event']->getEntryId());
        $cat = ilCalendarCategory::getInstanceByCategoryId($cat_id);
        $cat_info["type"] = $cat->getType();
        $cat_info["obj_id"] = $cat->getObjId();
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

                    case "etal":
                        return "EmployeeTalk";

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
