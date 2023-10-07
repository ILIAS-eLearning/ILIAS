<?php

/* Copyright (c) 1998-2022 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Container\Content\Filter;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class MemberDBRepo
{
    protected \ilDBInterface $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function filterObjIdsByTutorialSupport(array $obj_ids, string $lastname): array
    {
        $db = $this->db;
        $set = $db->queryF(
            "SELECT DISTINCT(obj_id) FROM obj_members m JOIN usr_data u ON (u.usr_id = m.usr_id) " .
            " WHERE  " . $db->in("m.obj_id", $obj_ids, false, "integer") .
            " AND " . $db->like("u.lastname", "text", $lastname) .
            " AND m.contact = %s",
            array("integer"),
            array(1)
        );
        $result_obj_ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $result_obj_ids[] = $rec["obj_id"];
        }
        return array_intersect($obj_ids, $result_obj_ids);
    }
}
