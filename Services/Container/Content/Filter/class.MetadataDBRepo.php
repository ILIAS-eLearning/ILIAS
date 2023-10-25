<?php

/* Copyright (c) 1998-2022 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Container\Content\Filter;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class MetadataDBRepo
{
    protected \ilDBInterface $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function filterObjIdsByCopyright(array $obj_ids, string $copyright_id): array
    {
        $db = $this->db;
        $set = $db->queryF(
            "SELECT DISTINCT(rbac_id) FROM il_meta_rights " .
            " WHERE  " . $db->in("rbac_id", $obj_ids, false, "integer") .
            " AND description = %s ",
            array("text"),
            array('il_copyright_entry__' . IL_INST_ID . '__' . $copyright_id)
        );
        $result_obj_ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $result_obj_ids[] = $rec["rbac_id"];
        }
        return array_intersect($obj_ids, $result_obj_ids);
    }
}
