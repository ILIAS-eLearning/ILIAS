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
        $identifier = \ilMDCopyrightSelectionEntry::createIdentifier($copyright_id);
        $default_identifier = \ilMDCopyrightSelectionEntry::createIdentifier(
            \ilMDCopyrightSelectionEntry::getDefault()
        );

        if ($identifier === $default_identifier) {
            return $this->filterObjIdsByDefaultCopyright($obj_ids, $default_identifier);
        }

        $db = $this->db;
        $set = $db->queryF(
            "SELECT DISTINCT(rbac_id) FROM il_meta_rights " .
            " WHERE  " . $db->in("rbac_id", $obj_ids, false, "integer") .
            " AND description = %s ",
            array("text"),
            array($identifier)
        );
        $result_obj_ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $result_obj_ids[] = $rec["rbac_id"];
        }
        return array_intersect($obj_ids, $result_obj_ids);
    }

    protected function filterObjIdsByDefaultCopyright(
        array $obj_ids,
        string $default_identifier
    ): array {
        /*
         * Objects with no entry in il_meta_rights need to be treated like they
         * have the default copyright.
         */
        $db = $this->db;
        $set = $db->queryF(
            "SELECT DISTINCT(rbac_id) FROM il_meta_rights " .
            " WHERE  " . $db->in("rbac_id", $obj_ids, false, "integer") .
            " AND NOT description = %s ",
            array("text"),
            array($default_identifier)
        );
        $filtered_out_obj_ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $filtered_out_obj_ids[] = $rec["rbac_id"];
        }
        return array_diff($obj_ids, $filtered_out_obj_ids);
    }
}
