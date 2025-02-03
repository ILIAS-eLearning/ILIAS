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

use ILIAS\UI\Component\Item\Item;

/**
 * @author  Jesús López Reyes <lopez@leifos.com>
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationFactory extends ilCalendarAppointmentBaseFactory
{
    public static function getInstance(
        array $a_appointment,
        ?ilInfoScreenGUI $a_info_screen,
        ?ilToolbarGUI $a_toolbar,
        ?Item $a_list_item
    ) {
        $class_base = self::getClassBaseName($a_appointment);
        $class_name = "ilAppointmentPresentation" . $class_base . "GUI";
        /** @noinspection PhpUndefinedMethodInspection */
        return $class_name::getInstance($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);
    }
}
