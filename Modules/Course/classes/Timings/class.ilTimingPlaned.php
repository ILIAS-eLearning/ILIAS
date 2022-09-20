<?php

declare(strict_types=0);
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
 * class ilTimingPlaned
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilTimingPlaned
{
    private int $item_id = 0;
    private int $user_id = 0;
    private int $start = 0;
    private int $end = 0;

    protected ilDBInterface $db;

    public function __construct(int $item_id, int $a_usr_id)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->item_id = $item_id;
        $this->user_id = $a_usr_id;
        $this->__read();
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getItemId(): int
    {
        return $this->item_id;
    }

    public function getPlanedStartingTime(): int
    {
        return $this->start;
    }

    public function setPlanedStartingTime(int $a_time): void
    {
        $this->start = $a_time;
    }

    public function getPlanedEndingTime(): int
    {
        return $this->end;
    }

    public function setPlanedEndingTime(int $a_end): void
    {
        $this->end = $a_end;
    }

    public function validate(): bool
    {
        $item = ilObjectActivation::getItem($this->getItemId());
        return true;
    }

    public function update(): bool
    {
        ilTimingPlaned::_delete($this->getItemId(), $this->getUserId());
        $this->create();
        return true;
    }

    public function create(): void
    {
        $query = "INSERT INTO crs_timings_planed (item_id,usr_id,planed_start,planed_end) " .
            "VALUES( " .
            $this->db->quote($this->getItemId(), 'integer') . ", " .
            $this->db->quote($this->getUserId(), 'integer') . ", " .
            $this->db->quote($this->getPlanedStartingTime(), 'integer') . ", " .
            $this->db->quote($this->getPlanedEndingTime(), 'integer') . " " .
            ")";
        $res = $this->db->manipulate($query);
    }

    public function delete(): void
    {
        ilTimingPlaned::_delete($this->getItemId(), $this->getUserId());
    }

    public static function _delete(int $a_item_id, int $a_usr_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "DELETE FROM crs_timings_planed " .
            "WHERE item_id = " . $ilDB->quote($a_item_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }

    public static function _getPlanedTimings(int $a_usr_id, int $a_item_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM crs_timings_planed " .
            "WHERE item_id = " . $ilDB->quote($a_item_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->query($query);
        $data = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $data['planed_start'] = (int) $row->planed_start;
            $data['planed_end'] = (int) $row->planed_end;
        }
        return $data;
    }

    public static function _getPlanedTimingsByItem($a_item_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT * FROM crs_timings_planed " .
            "WHERE item_id = " . $ilDB->quote($a_item_id, 'integer') . " ";
        $res = $ilDB->query($query);
        $data = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $data[(int) $row->usr_id]['start'] = (int) $row->planed_start;
            $data[(int) $row->usr_id]['end'] = (int) $row->planed_end;
        }
        return $data;
    }

    public static function _deleteByItem(int $a_item_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "DELETE FROM crs_timings_planed " .
            "WHERE item_id = " . $ilDB->quote($a_item_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }

    public static function _deleteByUser(int $a_usr_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "DELETE FROM crs_timings_planed " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }

    public function __read(): void
    {
        $query = "SELECT * FROM crs_timings_planed " .
            "WHERE item_id = " . $this->db->quote($this->getItemId(), 'integer') . " " .
            "AND usr_id = " . $this->db->quote($this->getUserId(), 'integer') . " ";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setPlanedStartingTime((int) $row->planed_start);
            $this->setPlanedEndingTime((int) $row->planed_end);
        }
    }
}
