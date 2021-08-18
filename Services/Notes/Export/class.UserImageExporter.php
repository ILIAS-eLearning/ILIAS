<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Notes\Export;

/**
 * Helper UI class for notes/comments handling in (HTML) exports
 * @author Alexander Killing <killing@leifos.de>
 */
class UserImageExporter
{

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }

    /**
     *
     *
     * @param
     * @return
     */
    public function exportUserImagesForRepObjId($export_dir, $rep_obj_id)
    {
        $db = $this->db;
        $set = $db->queryF(
            "SELECT DISTINCT author FROM note " .
            " WHERE rep_obj_id = %s " .
            " AND type = %s ",
            ["integer", "integer"],
            [$rep_obj_id, 2]
        );
        $user_ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $user_ids[] = $rec["author"];
        }
        $user_export = new \ILIAS\User\Export\UserHtmlExport();
        $user_export->exportUserImages($export_dir, $user_ids);
    }
}
