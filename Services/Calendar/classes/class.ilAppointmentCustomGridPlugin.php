<?php declare(strict_types=1);

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Item\Item as UiComponentItem;

/**
 * Abstract parent class for all calendar custom grid plugin classes.
 * @author  Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
abstract class ilAppointmentCustomGridPlugin extends ilPlugin
{
    protected ?ilCalendarEntry $appointment;
    protected ?ilDateTime $start_date;

    public function setAppointment(ilCalendarEntry $a_appointment, ilDateTime $a_start_date) : void
    {
        $this->appointment = $a_appointment;
        $this->start_date = $a_start_date;
    }

    /**
     * Get the calendar entry (appointment['event'])
     */
    public function getAppointment() : ?ilCalendarEntry
    {
        return $this->appointment;
    }

    /**
     * Get the specific start date of the calendar entry, not the appointment starting date.
     */
    public function getStartDate() : ?ilDateTime
    {
        return $this->start_date;
    }

    /**
     * Replaces the complete content in a calendar Grid.
     */
    abstract public function replaceContent(string $content) : string;

    /**
     * Add extra content in the grid after the event title
     */
    abstract public function addExtraContent() : string;

    /**
     * Add glyph before the appointment title.
     */
    abstract public function addGlyph() : string;

    /**
     * Edit the shy button title.
     */
    abstract public function editShyButtonTitle() : string;

    /**
     * Modify the agenda item
     */
    abstract public function editAgendaItem(UiComponentItem $item) : UiComponentItem;
}
