<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

namespace ILIAS\BookingManager\Schedule;

/**
 * Repo class for schedules
 * @author Alexander Killing <killing@leifos.de>
 */
class SchedulesDBRepository
{
    protected \ilDBInterface $db;
    protected static array $pool_loaded = [];
    protected static array $pool_schedules = [];
    protected static array $raw_data = [];

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

        $set = $db->query(
            'SELECT s.booking_schedule_id,s.title,' .
            'MAX(o.schedule_id) AS object_has_schedule' .
            ' FROM booking_schedule s' .
            ' LEFT JOIN booking_object o ON (s.booking_schedule_id = o.schedule_id)' .
            ' WHERE s.pool_id = ' . $db->quote($pool_id, 'integer') .
            ' GROUP BY s.booking_schedule_id,s.title' .
            ' ORDER BY s.title'
        );

        self::$pool_schedules[$pool_id] = [];

        while ($row = $db->fetchAssoc($set)) {
            if (!$row['object_has_schedule']) {
                $row['is_used'] = false;
            } else {
                $row['is_used'] = true;
            }
            self::$raw_data[$row["booking_schedule_id"]] = $row;
            self::$pool_schedules[$pool_id][] = $row;
        }

        self::$pool_loaded[$pool_id] = true;
    }

    protected function getScheduleDataForPool(
        int $pool_id
    ) : array {
        $this->loadDataOfPool($pool_id);
        return self::$pool_schedules[$pool_id] ?? [];
    }

    public function hasSchedules(
        int $pool_id
    ) : bool {
        $this->loadDataOfPool($pool_id);
        return count(self::$pool_schedules[$pool_id] ?? []) > 0;
    }

    public function getScheduleList(int $pool_id) : array
    {
        $list = [];
        foreach ($this->getScheduleDataForPool($pool_id) as $data) {
            $list[$data["booking_schedule_id"]] = $data["title"];
        }
        return $list;
    }

    public function getScheduleData(int $pool_id) : array
    {
        $schedules = [];
        foreach ($this->getScheduleDataForPool($pool_id) as $data) {
            $schedules[$data["booking_schedule_id"]] = $data;
        }
        return $schedules;
    }

}