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

/**
 * @author  Alex Killing <killing@leifos.com>
 * @ingroup ServicesCalendar
 */
class ilAppointmentFileHandlerFactory extends ilCalendarAppointmentBaseFactory
{
    /**
     * @param array $a_appointment
     * @return ilAppointmentFileHandler
     * @todo get rid of appointment array. Refactor to new appointment object
     */
    public static function getInstance(array $a_appointment): ilAppointmentFileHandler
    {
        $cat_id = ilCalendarCategoryAssignments::_lookupCategory($a_appointment['event']->getEntryId());
        $cat = ilCalendarCategory::getInstanceByCategoryId($cat_id);
        $cat_info["type"] = $cat->getType();
        $cat_info["obj_id"] = $cat->getObjId();
        $class_base = self::getClassBaseName($a_appointment);

        if (!in_array($class_base, ["Session", "Course", "ConsultationHours", "Exercise", "BookingPool"])) {
            $class_base = "Dummy";
        }
        $class_name = "ilAppointment" . $class_base . "FileHandler";
        return new $class_name($a_appointment);
    }
}
