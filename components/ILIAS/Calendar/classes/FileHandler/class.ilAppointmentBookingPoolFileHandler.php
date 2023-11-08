<?php

declare(strict_types=1);

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Calendar\FileHandler\ilFileProperty;

/**
 * Booking Pool appointment file handler
 * @author  Jesús López Reyes <lopez@leifos.com>
 * @ingroup ServicesCalendar
 */
class ilAppointmentBookingPoolFileHandler extends ilAppointmentBaseFileHandler implements ilAppointmentFileHandler
{
    /**
     * @inheritDoc
     */
    public function getFiles(): array
    {
        // context id is reservation id (see ilObjBookingPoolGUI->processBooking)
        $res_id = $this->appointment['event']->getContextId();
        $res = new ilBookingReservation($res_id);
        $b_obj = new ilBookingObject($res->getObjectId());

        $files = [];

        if ($b_obj->getFile() !== "") {
            $file_property = new ilFileProperty();
            $file_property->setAbsolutePath($b_obj->getFileFullPath());
            $file_property->setFileName($b_obj->getFile());
            $files[] = $file_property;
        }

        if ($b_obj->getPostFile() !== "") {
            $file_property = new ilFileProperty();
            $file_property->setAbsolutePath($b_obj->getPostFileFullPath());
            $file_property->setFileName($b_obj->getPostFile());
            $files[] = $file_property;
        }

        return $files;
    }
}
