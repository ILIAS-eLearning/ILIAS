<?php

/* Copyright (c) 1998-2022 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Container\Content\Filter;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ObjectDBRepo
{
    protected \ilDBInterface $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function filterObjIdsByType(array $obj_ids, string $type): array
    {
        $db = $this->db;
        $set = $db->queryF(
            "SELECT obj_id FROM object_data " .
            " WHERE " . $db->in("obj_id", $obj_ids, false, "integer") .
            " AND type = %s",
            array("text"),
            array($type)
        );
        $result_obj_ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $result_obj_ids[] = $rec["obj_id"];
        }
        return $result_obj_ids;
    }

    public function filterObjIdsByOnline(array $obj_ids): array
    {
        return $this->_filterObjIdsByOnlineOffline($obj_ids, true);
    }

    public function filterObjIdsByOffline(array $obj_ids): array
    {
        return $this->_filterObjIdsByOnlineOffline($obj_ids, false);
    }

    protected function _filterObjIdsByOnlineOffline(array $obj_ids, bool $online = true): array
    {
        $db = $this->db;
        $online_where = ($online)
            ? " (offline <> " . $db->quote(1, "integer") . " OR offline IS NULL) "
            : " offline = " . $db->quote(1, "integer") . " ";
        $result = null;
        $set = $db->queryF(
            "SELECT obj_id FROM object_data " .
            " WHERE  " . $db->in("obj_id", $obj_ids, false, "integer") .
            " AND " . $online_where,
            [],
            []
        );
        $result_obj_ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $result_obj_ids[] = $rec["obj_id"];
        }
        return array_intersect($obj_ids, $result_obj_ids);
    }
}
