<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Component/classes/class.ilPlugin.php';

/**
 * Abstract parent class for all calendar custom grid plugin classes.
 * @author  Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
abstract class ilAppointmentCustomGridPlugin extends ilPlugin
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
     * Get the calendar entry (appointment['event'])
     * @return ilCalendarEntry
     */
    public function getAppointment()
    {
        return $this->appointment;
    }

    /**
     * Get the specific start date of the calendar entry, not the appointment starting date.
     * @return ilDateTime
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
     * Get slot name
     * @return string
     */
    final public function getSlot()
    {
        return "AppointmentCustomGrid";
    }

    /**
     * Get slot Id
     * @return string
     */
    final public function getSlotId()
    {
        return "capg";
    }

    /**
     * empty
     */
    final public function slotInit()
    {
        //nothing to do here.
    }

    //Day, Week and Month views.

    /**
     * Replaces the complete content in a calendar Grid.
     * @param $content string html content
     * @return mixed
     */
    abstract public function replaceContent($content);

    /**
     * Add extra content in the grid after the event title
     * @return mixed
     */
    abstract public function addExtraContent();

    /**
     * Add glyph before the appointment title.
     * @return mixed
     */
    abstract public function addGlyph();

    /**
     * Edit the shy button title.
     * @return mixed
     */
    abstract public function editShyButtonTitle();


    //List view.
    /**
     * @param $item
     */
    abstract public function editAgendaItem(\ILIAS\UI\Component\Item\Item $item);
}
