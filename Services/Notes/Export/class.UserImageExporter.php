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
 */

namespace ILIAS\Notes\Export;

/**
 * Helper UI class for notes/comments handling in (HTML) exports
 * @author Alexander Killing <killing@leifos.de>
 */
class UserImageExporter
{
    protected \ilDBInterface $db;

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }

    public function exportUserImagesForRepObjId(
        string $export_dir,
        int $rep_obj_id
    ) : void {
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
            $user_ids[] = (int) $rec["author"];
        }
        $this->exportUserImages($export_dir, $user_ids);
    }

    public function exportUserImages(string $export_dir, array $user_ids) : void
    {
        $user_export = new \ILIAS\User\Export\UserHtmlExport();
        $user_export->exportUserImages($export_dir, $user_ids);
    }
}
