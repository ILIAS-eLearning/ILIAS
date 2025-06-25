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
        global $DIC;

        // context id is reservation id (see ilObjBookingPoolGUI->processBooking)
        $res_id = $this->appointment['event']->getContextId();
        $res = new ilBookingReservation($res_id);
        $b_obj = new ilBookingObject($res->getObjectId());
        $objects_manager = $DIC->bookingManager()->internal()->domain()->objects($b_obj->getPoolId());

        $files = [];

        if ($objects_manager->hasObjectInfo($b_obj->getId())) {
            $file_property = new ilFileProperty();
            $file_property->setAbsolutePath($objects_manager->getObjectInfoPath($b_obj->getId()));
            $file_property->setFileName($objects_manager->getObjectInfoFilename($b_obj->getId()));
            $files[] = $file_property;
        }

        if ($objects_manager->hasBookingInfo($b_obj->getId())) {
            $file_property = new ilFileProperty();
            $file_property->setAbsolutePath($objects_manager->getBookingInfoPath($b_obj->getId()));
            $file_property->setFileName($objects_manager->getBookingInfoFilename($b_obj->getId()));
            $files[] = $file_property;
        }

        return $files;
    }
}
