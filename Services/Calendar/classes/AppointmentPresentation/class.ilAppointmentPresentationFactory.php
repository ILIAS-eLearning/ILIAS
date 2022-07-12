<?php declare(strict_types=1);

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
