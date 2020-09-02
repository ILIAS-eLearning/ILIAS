<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Online help application class
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 */
class ilHelp
{
    /**
     * Get tooltip for id
     *
     * @param
     * @return
     */
    public static function getTooltipPresentationText($a_tt_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilSetting = $DIC->settings();
        $ilUser = $DIC->user();
        
        
        if ($ilUser->getLanguage() != "de") {
            return "";
        }
        
        if ($ilSetting->get("help_mode") == "1") {
            return "";
        }

        if ($ilUser->getPref("hide_help_tt")) {
            return "";
        }
        
        if (OH_REF_ID > 0) {
            $module_id = 0;
        } else {
            $module_id = (int) $ilSetting->get("help_module");
            if ($module_id == 0) {
                return "";
            }
        }
        
        $set = $ilDB->query(
            "SELECT tt_text FROM help_tooltip " .
            " WHERE tt_id = " . $ilDB->quote($a_tt_id, "text") .
            " AND module_id = " . $ilDB->quote($module_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        if ($rec["tt_text"] != "") {
            $t = $rec["tt_text"];
            if ($module_id == 0) {
                $t .= "<br/><i class='small'>" . $a_tt_id . "</i>";
            }
            return $t;
        } else { // try to get general version
            $fu = strpos($a_tt_id, "_");
            $gen_tt_id = "*" . substr($a_tt_id, $fu);
            $set = $ilDB->query(
                "SELECT tt_text FROM help_tooltip " .
                " WHERE tt_id = " . $ilDB->quote($gen_tt_id, "text") .
                " AND module_id = " . $ilDB->quote($module_id, "integer")
            );
            $rec = $ilDB->fetchAssoc($set);
            if ($rec["tt_text"] != "") {
                $t = $rec["tt_text"];
                if ($module_id == 0) {
                    $t .= "<br/><i class='small'>" . $a_tt_id . "</i>";
                }
                return $t;
            }
        }
        if ($module_id == 0) {
            return "<i>" . $a_tt_id . "</i>";
        }
        return "";
    }

    /**
     * Get object_creation tooltip tab text
     *
     * @param string $a_tab_id tab id
     * @return string tooltip text
     */
    public static function getObjCreationTooltipText($a_type)
    {
        return self::getTooltipPresentationText($a_type . "_create");
    }

    /**
     * Get main menu tooltip
     *
     * @param string $a_mm_id
     * @return string tooltip text
     */
    public static function getMainMenuTooltip($a_item_id)
    {
        return self::getTooltipPresentationText($a_item_id);
    }

    
    /**
     * Get all tooltips
     *
     * @param
     * @return
     */
    public static function getAllTooltips($a_comp = "", $a_module_id = 0)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $q = "SELECT * FROM help_tooltip";
        $q .= " WHERE module_id = " . $ilDB->quote($a_module_id, "integer");
        if ($a_comp != "") {
            $q .= " AND comp = " . $ilDB->quote($a_comp, "text");
        }
        $set = $ilDB->query($q);
        $tts = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $tts[$rec["id"]] = array("id" => $rec["id"], "text" => $rec["tt_text"],
                "tt_id" => $rec["tt_id"]);
        }
        return $tts;
    }
    
    /**
     * Add tooltip
     *
     * @param
     * @return
     */
    public static function addTooltip($a_tt_id, $a_text, $a_module_id = 0)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $fu = strpos($a_tt_id, "_");
        $comp = substr($a_tt_id, 0, $fu);
        
        $nid = $ilDB->nextId("help_tooltip");
        $ilDB->manipulate("INSERT INTO help_tooltip " .
            "(id, tt_text, tt_id, comp,module_id) VALUES (" .
            $ilDB->quote($nid, "integer") . "," .
            $ilDB->quote($a_text, "text") . "," .
            $ilDB->quote($a_tt_id, "text") . "," .
            $ilDB->quote($comp, "text") . "," .
            $ilDB->quote($a_module_id, "integer") .
            ")");
    }
    
    /**
     * Update tooltip
     *
     * @param
     * @return
     */
    public static function updateTooltip($a_id, $a_text, $a_tt_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $fu = strpos($a_tt_id, "_");
        $comp = substr($a_tt_id, 0, $fu);
        
        $ilDB->manipulate(
            "UPDATE help_tooltip SET " .
            " tt_text = " . $ilDB->quote($a_text, "text") . ", " .
            " tt_id = " . $ilDB->quote($a_tt_id, "text") . ", " .
            " comp = " . $ilDB->quote($comp, "text") .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
    }
    
    
    /**
     * Get all tooltip components
     *
     * @param
     * @return
     */
    public static function getTooltipComponents($a_module_id = 0)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $lng = $DIC->language();
        
        $set = $ilDB->query("SELECT DISTINCT comp FROM help_tooltip " .
            " WHERE module_id = " . $ilDB->quote($a_module_id, "integer") .
            " ORDER BY comp ");
        $comps[""] = "- " . $lng->txt("help_all") . " -";
        while ($rec = $ilDB->fetchAssoc($set)) {
            $comps[$rec["comp"]] = $rec["comp"];
        }
        return $comps;
    }
    
    /**
     * Delete tooltip
     *
     * @param
     * @return
     */
    public static function deleteTooltip($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $ilDB->manipulate(
            "DELETE FROM help_tooltip WHERE " .
            " id = " . $ilDB->quote($a_id, "integer")
        );
    }
    
    /**
     * Delete tooltips of module
     *
     * @param
     * @return
     */
    public static function deleteTooltipsOfModule($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $ilDB->manipulate(
            "DELETE FROM help_tooltip WHERE " .
            " module_id = " . $ilDB->quote($a_id, "integer")
        );
    }

    /**
     * Get help lm id
     *
     * @return int help learning module id
     */
    public static function getHelpLMId()
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        $lm_id = 0;

        if (OH_REF_ID > 0) {
            $lm_id = ilObject::_lookupObjId(OH_REF_ID);
        } else {
            $hm = (int) $ilSetting->get("help_module");
            if ($hm > 0) {
                include_once("./Services/Help/classes/class.ilObjHelpSettings.php");
                $lm_id = ilObjHelpSettings::lookupModuleLmId($hm);
            }
        }

        return $lm_id;
    }
}
