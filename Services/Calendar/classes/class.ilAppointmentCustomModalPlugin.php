<?php declare(strict_types=1);

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
 * Abstract parent class for all calendar custom modals plugin classes.
 * @author  Jesús López Reyes <lopez@leifos.com>
 * @ingroup ServicesCalendar
 */
abstract class ilAppointmentCustomModalPlugin extends ilPlugin
{
    protected ?ilCalendarEntry $appointment;
    protected ?ilDateTime $start_date;

    public function setAppointment(ilCalendarEntry $a_appointment, ilDateTime $a_start_date) : void
    {
        $this->appointment = $a_appointment;
        $this->start_date = $a_start_date;
    }

    public function getAppointment() : ?ilCalendarEntry
    {
        return $this->appointment;
    }

    /**
     * This is the date of the calendar entry, it's not the appointment start date.
     * This is important because an appointment can be recursive (e.g. 11 july, 12 july, 13, july)
     * The appointment start date is always 11 July but for an entry it can be 11,12 or 13)
     * When routing it is used to set up the parameter "dt"
     */
    public function getStartDate() : ?ilDateTime
    {
        return $this->start_date;
    }

    /**
     * Replace the content inside the modal.
     */
    abstract public function replaceContent() : string;

    /**
     * Add content after the Infoscreen
     */
    abstract public function addExtraContent() : string;

    /**
     * Add elements in the infoscreen
     */
    abstract public function infoscreenAddContent(ilInfoScreenGUI $a_info) : ?ilInfoScreenGUI;

    /**
     * Add elements in the toolbar
     */
    abstract public function toolbarAddItems(ilToolbarGUI $a_toolbar) : ?ilToolbarGUI;

    /**
     * Replace the toolbar for another one.
     */
    abstract public function toolbarReplaceContent() : ?ilToolbarGUI;

    abstract public function editModalTitle($current_title) : string;
}
