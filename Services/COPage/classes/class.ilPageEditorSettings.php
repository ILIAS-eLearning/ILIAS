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
 * Page editor settings
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPageEditorSettings
{
    // settings groups. each group contains one or multiple
    // page parent types
    protected static array $option_groups = array(
        "lm" => array("lm"),
        "wiki" => array("wpg"),
        "scorm" => array("sahs"),
        "glo" => array("gdf"),
        "test" => array("qpl"),
        "rep" => array("cont"),
        "copa" => array("copa"),
        "frm" => array("frm"),
        );

    /**
     * Get all settings groups
     */
    public static function getGroups(): array
    {
        return self::$option_groups;
    }

    /**
     * Write Setting
     */
    public static function writeSetting(
        string $a_grp,
        string $a_name,
        string $a_value
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "DELETE FROM page_editor_settings WHERE " .
            "settings_grp = " . $ilDB->quote($a_grp, "text") .
            " AND name = " . $ilDB->quote($a_name, "text")
        );

        $ilDB->manipulate("INSERT INTO page_editor_settings " .
            "(settings_grp, name, value) VALUES (" .
            $ilDB->quote($a_grp, "text") . "," .
            $ilDB->quote($a_name, "text") . "," .
            $ilDB->quote($a_value, "text") .
            ")");
    }

    /**
     * Lookup setting
     */
    public static function lookupSetting(
        string $a_grp,
        string $a_name,
        string $a_default = '0'
    ): string {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT value FROM page_editor_settings " .
            " WHERE settings_grp = " . $ilDB->quote($a_grp, "text") .
            " AND name = " . $ilDB->quote($a_name, "text")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return $rec["value"];
        }

        return $a_default;
    }

    /**
     * Lookup setting by parent type
     */
    public static function lookupSettingByParentType(
        string $a_par_type,
        string $a_name,
        string $a_default = '0'
    ): string {
        $grp = "";
        foreach (self::$option_groups as $g => $types) {
            if (in_array($a_par_type, $types)) {
                $grp = $g;
            }
        }

        if ($grp != "") {
            return ilPageEditorSettings::lookupSetting($grp, $a_name, $a_default);
        }

        return $a_default;
    }
}
