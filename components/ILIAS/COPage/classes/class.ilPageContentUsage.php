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
 * Saves usages of page content elements in pages
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPageContentUsage
{
    public static function saveUsage(
        string $a_pc_type,
        int $a_pc_id,
        string $a_usage_type,
        int $a_usage_id,
        int $a_usage_hist_nr = 0,
        string $a_lang = "-"
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->replace("page_pc_usage", array(
            "pc_type" => array("text", $a_pc_type),
            "pc_id" => array("integer", $a_pc_id),
            "usage_type" => array("text", $a_usage_type),
            "usage_id" => array("integer", $a_usage_id),
            "usage_lang" => array("text", $a_lang),
            "usage_hist_nr" => array("integer", $a_usage_hist_nr)
            ), array());
    }

    public static function deleteAllUsages(
        string $a_pc_type,
        string $a_usage_type,
        int $a_usage_id,
        int $a_usage_hist_nr = 0,
        string $a_lang = "-"
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $and_hist = ($a_usage_hist_nr !== 0)
            ? " AND usage_hist_nr = " . $ilDB->quote($a_usage_hist_nr, "integer")
            : "";

        $ilDB->manipulate($q = "DELETE FROM page_pc_usage WHERE usage_type = " .
            $ilDB->quote($a_usage_type, "text") .
            " AND usage_id = " . $ilDB->quote($a_usage_id, "integer") .
            " AND usage_lang = " . $ilDB->quote($a_lang, "text") .
            $and_hist .
            " AND pc_type = " . $ilDB->quote($a_pc_type, "text"));
    }

    /**
     * Get usages
     */
    public static function getUsages(
        string $a_pc_type,
        int $a_pc_id,
        bool $a_incl_hist = true
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT * FROM page_pc_usage " .
            " WHERE pc_type = " . $ilDB->quote($a_pc_type, "text") .
            " AND pc_id = " . $ilDB->quote($a_pc_id, "integer");

        if (!$a_incl_hist) {
            $q .= " AND usage_hist_nr = " . $ilDB->quote(0, "integer");
        }

        $set = $ilDB->query($q);
        $usages = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $usages[] = $rec;
        }
        return $usages;
    }

    /**
     * Get page content usages for page
     */
    public static function getUsagesOfPage(
        int $a_usage_id,
        string $a_usage_type,
        int $a_hist_nr = 0,
        bool $a_all_hist_nrs = false,
        string $a_lang = "-"
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $hist_str = "";
        if (!$a_all_hist_nrs) {
            $hist_str = " AND usage_hist_nr = " . $ilDB->quote($a_hist_nr, "integer");
        }

        $set = $ilDB->query(
            "SELECT pc_type, pc_id FROM page_pc_usage WHERE " .
            " usage_id = " . $ilDB->quote($a_usage_id, "integer") . " AND " .
            " usage_lang = " . $ilDB->quote($a_lang, "text") . " AND " .
            " usage_type = " . $ilDB->quote($a_usage_type, "text") .
            $hist_str
        );

        $usages = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $usages[$rec["pc_type"] . ":" . $rec["pc_id"]] = array(
                "type" => $rec["pc_type"],
                "id" => $rec["pc_id"]
            );
        }
        return $usages;
    }
}
