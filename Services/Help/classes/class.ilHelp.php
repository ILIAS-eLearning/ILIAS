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
 * Online help application class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilHelp
{
    public static function getTooltipPresentationText(
        string $a_tt_id
    ) : string {
        global $DIC;

        $ilDB = $DIC->database();
        $ilSetting = $DIC->settings();
        $ilUser = $DIC->user();
        
        
        if ($ilUser->getLanguage() !== "de") {
            return "";
        }
        
        if ($ilSetting->get("help_mode") === "1") {
            return "";
        }

        if ($ilUser->getPref("hide_help_tt")) {
            return "";
        }
        
        if (defined('OH_REF_ID') && (int) OH_REF_ID > 0) {
            $module_id = 0;
        } else {
            $module_id = (int) $ilSetting->get("help_module");
            if ($module_id === 0) {
                return "";
            }
        }
        
        $set = $ilDB->query(
            "SELECT tt_text FROM help_tooltip " .
            " WHERE tt_id = " . $ilDB->quote($a_tt_id, "text") .
            " AND module_id = " . $ilDB->quote($module_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        if (is_array($rec) && $rec["tt_text"] != "") {
            $t = $rec["tt_text"];
            if ($module_id === 0) {
                $t .= "<br/><i>(" . $a_tt_id . ")</i>";
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
            if (is_array($rec) && $rec["tt_text"] != "") {
                $t = $rec["tt_text"];
                if ($module_id === 0) {
                    $t .= "<br/><i>(" . $a_tt_id . ")</i>";
                }
                return $t;
            }
        }
        if ($module_id === 0) {
            return "<i>" . $a_tt_id . "</i>";
        }
        return "";
    }

    /**
     * Get object_creation tooltip tab text
     */
    public static function getObjCreationTooltipText(
        string $a_type
    ) : string {
        return self::getTooltipPresentationText($a_type . "_create");
    }

    /**
     * @return string tooltip text
     */
    public static function getMainMenuTooltip(
        string $a_item_id
    ) : string {
        return self::getTooltipPresentationText($a_item_id);
    }

    public static function getAllTooltips(
        string $a_comp = "",
        int $a_module_id = 0
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();
        
        $q = "SELECT * FROM help_tooltip";
        $q .= " WHERE module_id = " . $ilDB->quote($a_module_id, "integer");
        if ($a_comp !== "") {
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
    
    public static function addTooltip(
        string $a_tt_id,
        string $a_text,
        int $a_module_id = 0
    ) : void {
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
    
    public static function updateTooltip(
        int $a_id,
        string $a_text,
        string $a_tt_id
    ) : void {
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
     */
    public static function getTooltipComponents(
        int $a_module_id = 0
    ) : array {
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
    
    public static function deleteTooltip(
        int $a_id
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();
        
        $ilDB->manipulate(
            "DELETE FROM help_tooltip WHERE " .
            " id = " . $ilDB->quote($a_id, "integer")
        );
    }
    
    public static function deleteTooltipsOfModule(
        int $a_id
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();
        
        $ilDB->manipulate(
            "DELETE FROM help_tooltip WHERE " .
            " module_id = " . $ilDB->quote($a_id, "integer")
        );
    }

    /**
     * Get help lm id
     * @return int help learning module id
     */
    public static function getHelpLMId() : int
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        $lm_id = 0;

        if ((int) OH_REF_ID > 0) {
            $lm_id = ilObject::_lookupObjId((int) OH_REF_ID);
        } else {
            $hm = (int) $ilSetting->get("help_module");
            if ($hm > 0) {
                $lm_id = ilObjHelpSettings::lookupModuleLmId($hm);
            }
        }

        return $lm_id;
    }
}
