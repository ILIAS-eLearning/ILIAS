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
 * Utility class for pages, that is e.g. used in the repository to avoid
 * including the whole large page object class
 */
class ilPageUtil
{
    /**
    * checks whether page exists and is not empty (may return true on some empty pages)
    *
    * @param	string		$a_parent_type	parent type
    * @param	int			$a_id			page id
    */
    public static function _existsAndNotEmpty(
        string $a_parent_type,
        int $a_id,
        string $a_lang = "-"
    ) : bool {
        global $DIC;

        $ilDB = $DIC->database();
        
        // language must be set at least to "-"
        if ($a_lang == "") {
            $a_lang = "-";
        }
        $and_lang = " AND lang = " . $ilDB->quote($a_lang, "text");
        
        $query = "SELECT page_id, is_empty FROM page_object WHERE page_id = " . $ilDB->quote($a_id, "integer") . " " .
            "AND parent_type= " . $ilDB->quote($a_parent_type, "text") . $and_lang;

        $set = $ilDB->query($query);
        if ($row = $ilDB->fetchAssoc($set)) {
            if ($row["is_empty"] != 1) {
                return true;
            }
        }
        return false;
    }
}
