<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Saves usages of page content elements in pages
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup
*/
class ilPageContentUsage
{
    /**
    * Save usages
    */
    public static function saveUsage($a_pc_type, $a_pc_id, $a_usage_type, $a_usage_id, $a_usage_hist_nr = 0, $a_lang = "-")
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $ilDB->replace("page_pc_usage", array(
            "pc_type" => array("text", $a_pc_type),
            "pc_id" => array("integer", (int) $a_pc_id),
            "usage_type" => array("text", $a_usage_type),
            "usage_id" => array("integer", (int) $a_usage_id),
            "usage_lang" => array("text", $a_lang),
            "usage_hist_nr" => array("integer", (int) $a_usage_hist_nr)
            ), array());
    }

    /**
    * Delete all usages
    */
    public static function deleteAllUsages($a_pc_type, $a_usage_type, $a_usage_id, $a_usage_hist_nr = 0, $a_lang = "-")
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $and_hist = ($a_usage_hist_nr !== false)
            ? " AND usage_hist_nr = " . $ilDB->quote((int) $a_usage_hist_nr, "integer")
            : "";
        
        $ilDB->manipulate($q = "DELETE FROM page_pc_usage WHERE usage_type = " .
            $ilDB->quote($a_usage_type, "text") .
            " AND usage_id = " . $ilDB->quote((int) $a_usage_id, "integer") .
            " AND usage_lang = " . $ilDB->quote($a_lang, "text") .
            $and_hist .
            " AND pc_type = " . $ilDB->quote($a_pc_type, "text"));
    }
    
    /**
    * Get usages
    */
    public static function getUsages($a_pc_type, $a_pc_id, $a_incl_hist = true)
    {
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
     *
     * @param
     * @return
     */
    public static function getUsagesOfPage($a_usage_id, $a_usage_type, $a_hist_nr = 0, $a_all_hist_nrs = false, $a_lang = "-")
    {
        global $DIC;

        $ilDB = $DIC->database();

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
