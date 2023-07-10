<?php

declare(strict_types=1);

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
    public function getFiles(): array;
}
