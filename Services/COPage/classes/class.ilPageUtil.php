<?php

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
    public static function _existsAndNotEmpty($a_parent_type, $a_id, $a_lang = "-")
    {
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
