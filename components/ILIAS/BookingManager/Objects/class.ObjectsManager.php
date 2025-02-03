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

namespace ILIAS\BookingManager\Objects;

use ILIAS\BookingManager\InternalRepoService;
use ILIAS\BookingManager\InternalDataService;
use ILIAS\BookingManager\InternalDomainService;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ObjectsManager
{
    protected int $pool_id;
    protected ObjectsDBRepository $object_repo;
    protected InternalRepoService $repo;
    protected InternalDataService $data;
    protected InternalDomainService $domain;

    public function __construct(
        InternalDataService $data,
        InternalRepoService $repo,
        InternalDomainService $domain,
        protected ResourceStakeholder $object_info_stakeholder,
        protected ResourceStakeholder $book_info_stakeholder,
        int $pool_id
    ) {
        $this->object_repo = $repo->objects();
        $this->object_repo->loadDataOfPool($pool_id);
        $this->pool_id = $pool_id;
    }

    public function getNrOfItemsForObject(int $book_obj_id): int
    {
        return $this->object_repo->getNrOfItemsForObject($book_obj_id);
    }

    public function getObjectTitles(): array
    {
        $titles = [];
        foreach ($this->object_repo->getObjectDataForPool($this->pool_id) as $d) {
            $titles[$d["booking_object_id"]] = $d["title"];
        }
        return $titles;
    }

    public function getObjectIds(): array
    {
        return array_map(static function ($d) {
            return (int) $d["booking_object_id"];
        }, $this->object_repo->getObjectDataForPool($this->pool_id));
    }

    public function getColorNrForObject(int $book_obj_id): int
    {
        return $this->object_repo->getColorNrForObject($book_obj_id);
    }

    public function getDataArrayFromInputString(string $input): array
    {
        $rows = explode("\n", $input);
        $data = [];
        foreach ($rows as $row) {
            $cells = explode(";", $row);
            if (count($cells) === 1) {
                $cells = explode("\t", $row);
            }
            $data[] = [
                "title" => trim($cells[0] ?? ""),
                "description" => trim($cells[1] ?? ""),
                "nr" => trim($cells[2] ?? ""),
            ];
        }
        return $data;
    }

    public function createObjectsFromBulkInputString(string $input, int $schedule_id): void
    {
        foreach ($this->getDataArrayFromInputString($input) as $data) {
            $object = new \ilBookingObject();
            $object->setTitle($data["title"]);
            $object->setDescription($data["description"]);
            $object->setNrOfItems((int) $data["nr"]);
            $object->setPoolId($this->pool_id);
            if ($schedule_id > 0) {
                $object->setScheduleId($schedule_id);
            }
            $object->save();
        }
    }

    //
    // Object and booking resource management
    //

    public function importObjectInfoFromLegacyUpload(int $booking_obj_id, array $file_input): string
    {
        if (!isset($file_input["tmp_name"])) {
            return "";
        }
        return $this->object_repo->importObjectInfoFromLegacyUpload(
            $booking_obj_id,
            $file_input,
            $this->object_info_stakeholder
        );
    }

    public function importBookingInfoFromLegacyUpload(int $booking_obj_id, array $file_input): string
    {
        if (!isset($file_input["tmp_name"])) {
            return "";
        }
        return $this->object_repo->importBookingInfoFromLegacyUpload(
            $booking_obj_id,
            $file_input,
            $this->book_info_stakeholder
        );
    }

    public function deliverObjectInfo(int $booking_obj_id): void
    {
        if ($this->object_repo->hasObjectInfo($booking_obj_id)) {
            $this->object_repo->deliverObjectInfo($booking_obj_id);
        }
    }

    public function deliverBookingInfo(int $booking_obj_id): void
    {
        if ($this->object_repo->hasBookingInfo($booking_obj_id)) {
            $this->object_repo->deliverBookingInfo($booking_obj_id);
        }
    }

    public function getObjectInfoFilename(int $booking_obj_id): string
    {
        return $this->object_repo->getObjectInfoFilename($booking_obj_id);
    }

    public function getBookingInfoFilename(int $booking_obj_id): string
    {
        return $this->object_repo->getBookingInfoFilename($booking_obj_id);
    }

    public function deleteObjectInfo(int $booking_obj_id): void
    {
        if ($this->object_repo->hasObjectInfo($booking_obj_id)) {
            $this->object_repo->deleteObjectInfo($booking_obj_id);
        }
    }

    public function deleteBookingInfo(int $booking_obj_id): string
    {
        if ($this->object_repo->hasBookingInfo($booking_obj_id)) {
            $this->object_repo->deleteBookingInfo($booking_obj_id);
        }
    }

    public function cloneTo(
        int $from_booking_obj_id,
        int $to_booking_obj_id
    ): void {
        if ($this->object_repo->hasObjectInfo($from_booking_obj_id) ||
            $this->object_repo->hasBookingInfo($from_booking_obj_id)) {
            $this->object_repo->clone($from_booking_obj_id, $to_booking_obj_id);
        }
    }

    public function getObjectInfoPath(int $booking_object_id): int
    {
        return $this->object_repo->getObjectInfoPath($booking_object_id);
    }

    public function getBookingInfoPath(int $booking_object_id): int
    {
        return $this->object_repo->getBookingInfoPath($booking_object_id);
    }

}
