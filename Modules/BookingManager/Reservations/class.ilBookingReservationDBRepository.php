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

/**
 * Repo class for reservations
 * Acts on tables booking_reservation (rw), booking_reservation_group (rw) and booking_object (r)
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookingReservationDBRepository
{
    protected ilDBInterface $db;
    protected ?array $preloaded_by_context_list = null;

    /**
     * Do not call this constructor directly,
     * use ilBookingReservationDBRepositoryFactory instead
     */
    public function __construct(
        ilDBInterface $db,
        ?array $preload_context_obj_ids = null
    ) {
        if (is_array($preload_context_obj_ids)) {
            $this->preloadByContextIds($preload_context_obj_ids);
        }
        $this->db = $db;
    }

    /**
     * Get reservation data for id
     * @return string[]
     */
    public function getForId(int $id): array
    {
        $ilDB = $this->db;
        $set = $ilDB->query('SELECT *' .
            ' FROM booking_reservation' .
            ' WHERE booking_reservation_id = ' . $ilDB->quote($id, 'integer'));
        return $ilDB->fetchAssoc($set);
    }

    /**
     * @return int new reservation id
     */
    public function create(
        int $user_id,
        int $assigner_id,
        int $object_id,
        int $context_obj_id,
        int $from,
        int $to,
        int $status,
        int $group_id
    ): int {
        $ilDB = $this->db;

        $id = $ilDB->nextId('booking_reservation');
        $ilDB->manipulate('INSERT INTO booking_reservation' .
            ' (booking_reservation_id,user_id,assigner_id,object_id,context_obj_id,date_from,date_to,status,group_id)' .
            ' VALUES (' . $ilDB->quote($id, 'integer') .
            ',' . $ilDB->quote($user_id, 'integer') .
            ',' . $ilDB->quote($assigner_id, 'integer') .
            ',' . $ilDB->quote($object_id, 'integer') .
            ',' . $ilDB->quote($context_obj_id, 'integer') .
            ',' . $ilDB->quote($from, 'integer') .
            ',' . $ilDB->quote($to, 'integer') .
            ',' . $ilDB->quote($status, 'integer') .
            ',' . $ilDB->quote($group_id, 'integer') . ')');
        return $id;
    }

    /**
     * @return int number of affected records
     */
    public function update(
        int $id,
        int $user_id,
        int $assigner_id,
        int $object_id,
        int $context_obj_id,
        int $from,
        int $to,
        int $status,
        int $group_id
    ): int {
        $ilDB = $this->db;
        return $ilDB->manipulate('UPDATE booking_reservation' .
            ' SET object_id = ' . $ilDB->quote($object_id, 'text') .
            ', user_id = ' . $ilDB->quote($user_id, 'integer') .
            ', assigner_id = ' . $ilDB->quote($assigner_id, 'integer') .
            ', date_from = ' . $ilDB->quote($from, 'integer') .
            ', date_to = ' . $ilDB->quote($to, 'integer') .
            ', status = ' . $ilDB->quote($status, 'integer') .
            ', group_id = ' . $ilDB->quote($group_id, 'integer') .
            ', context_obj_id = ' . $ilDB->quote($context_obj_id, 'integer') .
            ' WHERE booking_reservation_id = ' . $ilDB->quote($id, 'integer'));
    }

    public function delete(int $id): void
    {
        $ilDB = $this->db;

        if ($id) {
            $ilDB->manipulate('DELETE FROM booking_reservation' .
                ' WHERE booking_reservation_id = ' . $ilDB->quote($id, 'integer'));
        }
    }

    /**
     * @return int group id
     */
    public function getNewGroupId(): int
    {
        return $this->db->nextId('booking_reservation_group');
    }


    /**
     * Get number of uncancelled reservations in time frame
     * @param int[] $ids booking object ids
     */
    public function getNumberOfReservations(
        array $ids,
        int $from,
        int $to,
        bool $only_not_over_yet = false
    ): array {
        $ilDB = $this->db;

        $from = $ilDB->quote($from, 'integer');
        $to = $ilDB->quote($to, 'integer');

        $date = $only_not_over_yet
            ? ' AND date_to > ' . $ilDB->quote(time(), "integer")
            : "";

        $set = $ilDB->query('SELECT count(*) cnt, object_id' .
            ' FROM booking_reservation' .
            ' WHERE ' . $ilDB->in('object_id', $ids, '', 'integer') . $date .
            ' AND (status IS NULL OR status <> ' . $ilDB->quote(
                ilBookingReservation::STATUS_CANCELLED,
                'integer'
            ) . ')' .
            ' AND date_from <= ' . $to . ' AND date_to >= ' . $from .
            ' GROUP BY object_id');
        $res = [];
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["object_id"]] = $row;
        }
        return $res;
    }

    /**
     * List all reservations by date
     */
    public function getListByDate(
        bool $a_has_schedule,
        array $a_object_ids = null,
        array $filter = null,
        array $a_pool_ids = null
    ): array {
        $ilDB = $this->db;

        $res = array();

        $sql = 'SELECT r.*, o.title, o.pool_id' .
            ' FROM booking_reservation r' .
            ' JOIN booking_object o ON (o.booking_object_id = r.object_id)';

        $where = [];
        if ($a_pool_ids !== null) {
            $where = array($ilDB->in('pool_id', $a_pool_ids, '', 'integer'));
        }

        if ($a_object_ids !== null) {
            $where = array($ilDB->in('object_id', $a_object_ids, '', 'integer'));
        }

        if (isset($filter['context_obj_ids']) && count($filter['context_obj_ids']) > 0) {
            $where = array($ilDB->in('context_obj_id', $filter['context_obj_ids'], '', 'integer'));
        }

        if ($filter['status']) {
            if ($filter['status'] > 0) {
                $where[] = 'status = ' . $ilDB->quote($filter['status'], 'integer');
            } else {
                $where[] = '(status != ' . $ilDB->quote(-$filter['status'], 'integer') .
                    ' OR status IS NULL)';
            }
        }
        if (isset($filter['title']) && is_string($filter['title'])) {
            $where[] = '(' . $ilDB->like('title', 'text', '%' . $filter['title'] . '%') .
                ' OR ' . $ilDB->like('description', 'text', '%' . $filter['title'] . '%') . ')';
        }
        if ($a_has_schedule) {
            if (isset($filter['from']) && is_string($filter['from'])) {
                $where[] = 'date_from >= ' . $ilDB->quote($filter['from'], 'integer');
            }
            if (isset($filter['to']) && is_string($filter['to'])) {
                $where[] = 'date_to <= ' . $ilDB->quote($filter['to'], 'integer');
            }
            if (!isset($filter['past']) || !$filter['past']) {
                $where[] = 'date_to > ' . $ilDB->quote(time(), 'integer');
            }
        }
        if (isset($filter['user_id']) && is_numeric($filter['user_id'])) { // #16584
            $where[] = 'user_id = ' . $ilDB->quote($filter['user_id'], 'integer');
        }
        if (count($where) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        if ($a_has_schedule) {
            $sql .= ' ORDER BY date_from DESC';
        } else {
            // #16155 - could be cancelled and re-booked
            $sql .= ' ORDER BY status';
        }

        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $obj_id = $row["object_id"];
            $user_id = $row["user_id"];

            if ($a_has_schedule) {
                $slot = $row["date_from"] . "_" . $row["date_to"];
                $idx = $obj_id . "_" . $user_id . "_" . $slot;
            } else {
                $idx = $obj_id . "_" . $user_id;
            }
            $idx .= "_" . $row["context_obj_id"];

            if ($a_has_schedule && ($filter["slot"] ?? false)) {
                $slot_idx = date("w", $row["date_from"]) . "_" . date("H:i", $row["date_from"]) .
                    "-" . date("H:i", $row["date_to"] + 1);
                if ($filter["slot"] != $slot_idx) {
                    continue;
                }
            }

            if (!isset($res[$idx])) {
                $uname = ilObjUser::_lookupName($user_id);

                $res[$idx] = array(
                    "object_id" => $obj_id
                ,"title" => $row["title"]
                ,"pool_id" => $row["pool_id"]
                ,"context_obj_id" => (int) $row["context_obj_id"]
                ,"user_id" => $user_id
                ,"counter" => 1
                ,"user_name" => $uname["lastname"] . ", " . $uname["firstname"] // #17862
                );

                if ($a_has_schedule) {
                    $res[$idx]["booking_reservation_id"] = $idx;
                    $res[$idx]["date"] = date("Y-m-d", $row["date_from"]);
                    $res[$idx]["slot"] = date("H:i", $row["date_from"]) . " - " .
                        date("H:i", $row["date_to"] + 1);
                    $res[$idx]["week"] = date("W", $row["date_from"]);
                    $res[$idx]["weekday"] = date("w", $row["date_from"]);
                    $res[$idx]["can_be_cancelled"] = ($row["status"] != ilBookingReservation::STATUS_CANCELLED &&
                        $row["date_from"] > time());
                    $res[$idx]["_sortdate"] = $row["date_from"];

                    // this currently means: has any cancelled reservations (it is not grouped by this info)
                    $res[$idx]["status"] = $row["status"];
                } else {
                    $res[$idx]["booking_reservation_id"] = $row["booking_reservation_id"];
                    $res[$idx]["status"] = $row["status"];
                    $res[$idx]["can_be_cancelled"] = ($row["status"] != ilBookingReservation::STATUS_CANCELLED);
                }
            } else {
                $res[$idx]["counter"]++;
            }
        }

        return $res;
    }

    ////
    //// Preloading by context
    ////

    /**
     * Preload reservation information for context obj ids
     * @param int[] $context_obj_ids
     */
    protected function preloadByContextIds(
        array $context_obj_ids
    ): void {
        $filter = ["context_obj_ids" => ($context_obj_ids)];
        $filter['past'] = true;
        $filter['status'] = -ilBookingReservation::STATUS_CANCELLED;
        $f = new ilBookingReservationDBRepositoryFactory();
        $repo = $f->getRepo();
        $list = $repo->getListByDate(true, null, $filter);
        $list = ilArrayUtil::sortArray($list, "slot", "asc", true);
        $list = ilArrayUtil::stableSortArray($list, "date", "asc", true);
        $list = ilArrayUtil::stableSortArray($list, "object_id", "asc", true);
        $this->preloaded_by_context_list = ilArrayUtil::stableSortArray($list, "pool_id", "asc", true);
    }

    /**
     * Get context object properties info
     * @throws ilBookingReservationException
     */
    public function getCachedContextObjBookingInfo(
        int $context_obj_id
    ): array {
        if (!is_array($this->preloaded_by_context_list)) {
            throw new ilBookingReservationException("Repo not initilialized.");
        }
        return array_filter($this->preloaded_by_context_list, static function ($row) use ($context_obj_id) {
            return ($row["context_obj_id"] == $context_obj_id);
        });
    }
}
