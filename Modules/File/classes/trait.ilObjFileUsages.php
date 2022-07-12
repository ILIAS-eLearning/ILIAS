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
 * Trait ilObjFileUsages
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait ilObjFileUsages
{
    /**
     * @param        $a_type
     * @param        $a_id
     * @deprecated
     */
    // FSX
    public static function _deleteAllUsages($a_type, $a_id, int $a_usage_hist_nr = 0, string $a_usage_lang = "-")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $and_hist = ($a_usage_hist_nr !== false) ? " AND usage_hist_nr = "
            . $ilDB->quote($a_usage_hist_nr, "integer") : "";

        $file_ids = array();
        $set = $ilDB->query("SELECT id FROM file_usage" . " WHERE usage_type = "
            . $ilDB->quote($a_type, "text") . " AND usage_id= "
            . $ilDB->quote($a_id, "integer") . " AND usage_lang= "
            . $ilDB->quote($a_usage_lang, "text") . $and_hist);
        while ($row = $ilDB->fetchAssoc($set)) {
            $file_ids[] = $row["id"];
        }

        $ilDB->manipulate("DELETE FROM file_usage WHERE usage_type = "
            . $ilDB->quote($a_type, "text") . " AND usage_id = "
            . $ilDB->quote((int) $a_id, "integer") . " AND usage_lang= "
            . $ilDB->quote($a_usage_lang, "text") . " AND usage_hist_nr = "
            . $ilDB->quote($a_usage_hist_nr, "integer"));
    }

    /**
     * @param        $a_file_id
     * @param        $a_type
     * @param        $a_id
     * @deprecated
     */
    public static function _saveUsage($a_file_id, $a_type, $a_id, int $a_usage_hist_nr = 0, string $a_usage_lang = "-")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // check if file really exists
        if (ilObject::_lookupType($a_file_id) != "file") {
            return;
        }
        // #15143
        $ilDB->replace("file_usage", array(
            "id" => array("integer", (int) $a_file_id),
            "usage_type" => array("text", (string) $a_type),
            "usage_id" => array("integer", (int) $a_id),
            "usage_hist_nr" => array("integer", $a_usage_hist_nr),
            "usage_lang" => array("text", $a_usage_lang),
        ), array());
    }

    /**
     * get all usages of file object
     * @return array<int, array<string, mixed>>
     */
    public function getUsages() : array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // get usages in learning modules
        $q = "SELECT * FROM file_usage WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $us_set = $ilDB->query($q);
        $ret = array();
        while ($us_rec = $ilDB->fetchAssoc($us_set)) {
            $ret[] = array(
                "type" => $us_rec["usage_type"],
                "id" => $us_rec["usage_id"],
                "lang" => $us_rec["usage_lang"],
                "hist_nr" => $us_rec["usage_hist_nr"],
            );
        }

        return $ret;
    }

    /**
     * @deprecated
     */
    public static function _getFilesOfObject(string $a_type, int $a_id, int $a_usage_hist_nr = 0, string $a_usage_lang = "-") : array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $lstr = "";
        if ($a_usage_lang != "") {
            $lstr = "usage_lang = " . $ilDB->quote($a_usage_lang, "text") . " AND ";
        }

        // get usages in learning modules
        $q = "SELECT * FROM file_usage WHERE " . "usage_id = " . $ilDB->quote($a_id, "integer")
            . " AND " . "usage_type = " . $ilDB->quote($a_type, "text") . " AND " . $lstr
            . "usage_hist_nr = " . $ilDB->quote($a_usage_hist_nr, "integer");
        $file_set = $ilDB->query($q);
        $ret = array();
        while ($file_rec = $ilDB->fetchAssoc($file_set)) {
            $ret[$file_rec["id"]] = $file_rec["id"];
        }

        return $ret;
    }
}
