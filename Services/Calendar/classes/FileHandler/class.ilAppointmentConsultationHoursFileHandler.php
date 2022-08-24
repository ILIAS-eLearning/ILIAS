<?php

declare(strict_types=1);

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Consultation Hours appointment file handler
 * @author  Alex Killing <killing@leifos.de>
 * @ingroup ServicesCalendar
 */
class ilAppointmentConsultationHoursFileHandler extends ilAppointmentBaseFileHandler implements ilAppointmentFileHandler
{
    /**
     * @inheritDoc
     */
    public function getFiles(): array
    {
        return [];
    }
}
