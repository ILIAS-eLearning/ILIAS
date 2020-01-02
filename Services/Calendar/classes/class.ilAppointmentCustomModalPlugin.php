<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Component/classes/class.ilPlugin.php';

/**
 * Abstract parent class for all calendar custom modals plugin classes.
 * @author  Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
abstract class ilAppointmentCustomModalPlugin extends ilPlugin
{
    /**
     * @var ilCalendarEntry $appointment
     */
    protected $appointment;

    /**
     * @var DateTime $start_date
     */
    protected $start_date;

    /**
    * @param ilCalendarEntry $a_appointment
    * @param ilDateTime $a_start_date
    */
    public function setAppointment(ilCalendarEntry $a_appointment, ilDateTime $a_start_date)
    {
        $this->appointment = $a_appointment;
        $this->start_date = $a_start_date;
    }

    /**
     * @return ilCalendarEntry
     */
    public function getAppointment()
    {
        return $this->appointment;
    }

    /**
     * @return ilDateTime
     * This is the date of the calendar entry, it's not the appointment start date.
     * This is important because an appointment can be recursive (e.g. 11 july, 12 july, 13, july)
     * The appointment start date is always 11 July but for an entry it can be 11,12 or 13)
     * When routing it is used to set up the parameter "dt"
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * Get component type
     * @return string
     */
    final public function getComponentType()
    {
        return IL_COMP_SERVICE;
    }

    /**
     * Get component Name
     * @return string
     */
    final public function getComponentName()
    {
        return "Calendar";
    }

    /**
     * Get slot
     * @return string
     */
    final public function getSlot()
    {
        return "AppointmentCustomModal";
    }

    /**
     * Get Slot id
     * @return string
     */
    final public function getSlotId()
    {
        return "capm";
    }

    /**
     * empty
     */
    final public function slotInit()
    {
        //nothing to do here.
    }

    /**
     * Replace the content inside the modal.
     * @return mixed
     */
    abstract public function replaceContent();

    /**
     * Add content after the Infoscreen
     * @return mixed
     */
    abstract public function addExtraContent();

    /**
     * Add elements in the infoscreen
     * @param ilInfoScreenGUI $a_info
     * @return mixed
     */
    abstract public function infoscreenAddContent(ilInfoScreenGUI $a_info);

    /**
     * Add elements in the toolbar
     * @param ilToolbarGUI $a_toolbar
     * @return ilToolbarGUI
     */
    abstract public function toolbarAddItems(ilToolbarGUI $a_toolbar);

    /**
     * Replace the toolbar for another one.
     * @return mixed
     */
    abstract public function toolbarReplaceContent();

    /**
     * @param string $current_title
     * @return string
     */
    abstract public function editModalTitle($current_title);
}
