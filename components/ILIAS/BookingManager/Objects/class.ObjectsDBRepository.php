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

namespace ILIAS\BookingManager\Objects;

use ILIAS\Exercise\IRSS\IRSSWrapper;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;

/**
 * Repo class for booking objects
 * @author Alexander Killing <killing@leifos.de>
 */
class ObjectsDBRepository
{
    protected const NR_OF_COLORS = 9;
    protected static $color_number = [];
    protected static $pool_objects = [];
    protected static array $raw_data = [];
    protected static array $pool_loaded = [];

    public function __construct(
        protected IRSSWrapper $wrapper,
        protected \ilDBInterface $db
    ) {
    }

    public function loadDataOfPool(int $pool_id): void
    {
        $db = $this->db;

        if (isset(self::$pool_loaded[$pool_id]) && self::$pool_loaded[$pool_id]) {
            return;
        }

        $set = $db->queryF(
            "SELECT * FROM booking_object " .
            " WHERE pool_id = %s ORDER BY title ASC, booking_object_id ASC",
            ["integer"],
            [$pool_id]
        );
        self::$pool_objects[$pool_id] = [];
        $cnt = 0;
        while ($rec = $db->fetchAssoc($set)) {
            self::$raw_data[$rec["booking_object_id"]] = $rec;
            self::$color_number[$rec["booking_object_id"]] = ($cnt % self::NR_OF_COLORS) + 1;
            self::$pool_objects[$pool_id][] = $rec;
            $cnt++;
        }
        self::$pool_loaded[$pool_id] = true;
    }

    public function getNrOfItemsForObject(int $book_obj_id): int
    {
        if (!isset(self::$raw_data[$book_obj_id])) {
            throw new \ilBookingPoolException("Data for booking object $book_obj_id not loaded.");
        }
        return (int) self::$raw_data[$book_obj_id]["nr_of_items"];
    }

    public function getColorNrForObject(int $book_obj_id): int
    {
        if (!isset(self::$raw_data[$book_obj_id])) {
            throw new \ilBookingPoolException("Data for booking object $book_obj_id not loaded.");
        }
        return (int) self::$color_number[$book_obj_id];
    }

    public function getObjectDataForPool(
        int $pool_id
    ): array {
        $this->loadDataOfPool($pool_id);
        return self::$pool_objects[$pool_id] ?? [];
    }

    //
    // Object and booking irss resrouces
    //

    protected function getObjectInfoRidForBookingObjectId(int $booking_object_id): string
    {
        $set = $this->db->queryF(
            "SELECT obj_info_rid FROM booking_object " .
            " WHERE booking_object_id = %s ",
            ["integer"],
            [$booking_object_id]
        );
        $rec = $this->db->fetchAssoc($set);
        return ($rec["obj_info_rid"] ?? "");
    }

    public function hasObjectInfo(int $booking_object_id): bool
    {
        $rid = $this->getObjectInfoRidForBookingObjectId($booking_object_id);
        return ($rid !== "");
    }

    public function deleteObjectInfo(int $booking_object_id): bool
    {
        $rid = $this->getObjectInfoRidForBookingObjectId($booking_object_id);
        if ($rid === "") {
            $this->wrapper->deleteResource($rid);
        }
    }

    public function deliverObjectInfo(int $booking_object_id): void
    {
        $rid = $this->getObjectInfoRidForBookingObjectId($booking_object_id);
        $this->wrapper->deliverFile($rid);
    }

    public function getObjectInfoFilename(int $booking_object_id): string
    {
        $rid = $this->getObjectInfoRidForBookingObjectId($booking_object_id);
        if ($rid !== "") {
            $info = $this->wrapper->getResourceInfo($rid);
            return $info->getTitle();
        }
        return "";
    }


    public function importObjectInfoFromLegacyUpload(
        int $booking_object_id,
        array $file_input,
        ResourceStakeholder $stakeholder
    ): string {
        $rcid = $this->wrapper->importFileFromLegacyUpload(
            $file_input,
            $stakeholder
        );
        if ($rcid !== "") {
            $this->db->update(
                "booking_object",
                [
                    "obj_info_rid" => ["text", $rcid]
                ],
                [    // where
                     "booking_object_id" => ["integer", $booking_object_id]
                ]
            );
        }
        return $rcid;
    }

    protected function getBookingInfoRidForBookingObjectId(int $booking_object_id): string
    {
        $set = $this->db->queryF(
            "SELECT book_info_rid FROM booking_object " .
            " WHERE booking_object_id = %s ",
            ["integer"],
            [$booking_object_id]
        );
        $rec = $this->db->fetchAssoc($set);
        return ($rec["book_info_rid"] ?? "");
    }

    public function hasBookingInfo(int $booking_object_id): bool
    {
        $rid = $this->getBookingInfoRidForBookingObjectId($booking_object_id);
        return ($rid !== "");
    }

    public function deleteBookingInfo(int $booking_object_id): bool
    {
        $rid = $this->getBookingInfoRidForBookingObjectId($booking_object_id);
        if ($rid === "") {
            $this->wrapper->deleteResource($rid);
        }
    }

    public function deliverBookingInfo(int $booking_object_id): void
    {
        $rid = $this->getBookingInfoRidForBookingObjectId($booking_object_id);
        $this->wrapper->deliverFile($rid);
    }

    public function getBookingInfoFilename(int $booking_object_id): string
    {
        $rid = $this->getBookingInfoRidForBookingObjectId($booking_object_id);
        if ($rid !== "") {
            $info = $this->wrapper->getResourceInfo($rid);
            return $info->getTitle();
        }
        return "";
    }


    public function importBookingInfoFromLegacyUpload(
        int $booking_object_id,
        array $file_input,
        ResourceStakeholder $stakeholder
    ): string {
        $rcid = $this->wrapper->importFileFromLegacyUpload(
            $file_input,
            $stakeholder
        );
        if ($rcid !== "") {
            $this->db->update(
                "booking_object",
                [
                    "book_info_rid" => ["text", $rcid]
                ],
                [    // where
                     "booking_object_id" => ["integer", $booking_object_id]
                ]
            );
        }
        return $rcid;
    }

    public function clone(
        int $from_id,
        int $to_id
    ): void {
        $from_rid = $this->getObjectInfoRidForBookingObjectId($from_id);
        $to_rid = $this->wrapper->cloneResource($from_rid);
        if ($to_rid !== "") {
            $this->db->update(
                "booking_object",
                [
                    "obj_info_rid" => ["text", $to_rid]
                ],
                [    // where
                     "booking_object_id" => ["integer", $to_id]
                ]
            );
        }
        $from_rid = $this->getBookingInfoRidForBookingObjectId($from_id);
        $to_rid = $this->wrapper->cloneResource($from_rid);
        if ($to_rid !== "") {
            $this->db->update(
                "booking_object",
                [
                    "book_info_rid" => ["text", $to_rid]
                ],
                [    // where
                     "booking_object_id" => ["integer", $to_id]
                ]
            );
        }
    }
}
