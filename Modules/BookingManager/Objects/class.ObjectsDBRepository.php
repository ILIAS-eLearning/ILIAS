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

/**
 * Repo class for booking objects
 * @author Alexander Killing <killing@leifos.de>
 */
class ObjectsDBRepository
{
    protected const NR_OF_COLORS = 9;
    protected static $color_number = [];
    protected static $pool_objects = [];
    protected \ilDBInterface $db;
    protected static array $raw_data = [];
    protected static array $pool_loaded = [];

    public function __construct(
        \ilDBInterface $db
    ) {
        $this->db = $db;
    }

    public function loadDataOfPool(int $pool_id) : void
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

    public function getNrOfItemsForObject(int $book_obj_id) : int
    {
        if (!isset(self::$raw_data[$book_obj_id])) {
            throw new \ilBookingPoolException("Data for booking object $book_obj_id not loaded.");
        }
        return (int) self::$raw_data[$book_obj_id]["nr_of_items"];
    }

    public function getColorNrForObject(int $book_obj_id) : int
    {
        if (!isset(self::$raw_data[$book_obj_id])) {
            throw new \ilBookingPoolException("Data for booking object $book_obj_id not loaded.");
        }
        return (int) self::$color_number[$book_obj_id];
    }

    public function getObjectDataForPool(
        int $pool_id
    ) : array {
        $this->loadDataOfPool($pool_id);
        return self::$pool_objects[$pool_id] ?? [];
    }

}
