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

namespace ILIAS\MediaObjects\Setup;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaObjectsDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $db = $this->db;
        if (!$db->tableColumnExists('media_item', 'duration')) {
            $db->addTableColumn('media_item', 'duration', array(
                "type" => "integer",
                "notnull" => true,
                "length" => 4,
                "default" => 0
            ));
        }
    }

    public function step_2(): void
    {
        $db = $this->db;
        $set = $db->queryF(
            "SELECT * FROM settings " .
            " WHERE module = %s AND keyword = %s ",
            ["text", "text"],
            ["mobs", "black_list_file_types"]
        );
        $black_list_str = "";
        while ($rec = $db->fetchAssoc($set)) {
            $black_list_str = $rec["value"] ?? "";
        }
        $black_list = explode(",", $black_list_str);
        $new_black_list = [];
        foreach ($black_list as $type) {
            $type = strtolower(trim($type));
            switch ($type) {
                case "html": $type = "text/html";
                    break;
                case "mp4": $type = "video/mp4";
                    break;
                case "webm": $type = "video/webm";
                    break;
                case "mp3": $type = "audio/mpeg";
                    break;
                case "png": $type = "image/png";
                    break;
                case "jpeg":
                case "jpg": $type = "image/jpeg";
                    break;
                case "gif": $type = "image/gif";
                    break;
                case "webp": $type = "image/webp";
                    break;
                case "svg": $type = "image/svg+xml";
                    break;
                case "pdf": $type = "application/pdf";
                    break;
            }
            if (in_array($type, ["video/vimeo", "video/youtube", "video/mp4", "video/webm", "audio/mpeg",
                                 "image/png", "image/jpeg", "image/gif", "image/webp", "image/svg+xml",
                                 "text/html", "application/pdf"])) {
                if (!in_array($type, $new_black_list)) {
                    $new_black_list[] = $type;
                }
            }
        }
        $db->update(
            "settings",
            [
            "value" => ["text", implode(",", $new_black_list)]
        ],
            [    // where
                "module" => ["text", "mobs"],
                "keyword" => ["text", "black_list_file_types"]
            ]
        );
    }

}
