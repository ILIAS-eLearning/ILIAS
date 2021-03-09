<?php

use ILIAS\Calendar\FileHandler\ilFileProperty;

/**
 * Appointment file handler interface
 *
 * @author Alex Killing <killing@leifos.de>
 *
 * @ingroup ServicesCalendar
 */
interface ilAppointmentFileHandler
{
    /**
     * @return ilFileProperty[]
     */
    public function getFiles() : array;
}
