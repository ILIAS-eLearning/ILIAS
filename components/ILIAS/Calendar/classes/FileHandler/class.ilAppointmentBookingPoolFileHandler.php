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
