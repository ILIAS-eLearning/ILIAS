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

use ILIAS\COPage\Editor\Components\PageComponentEditor;

/**
 * COPage PC elements definition handler
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCOPagePCDef
{
    public static array $pc_def = [];
    public static array $pc_def_by_name = [];
    public static array $pc_gui_classes = array();
    public static array $pc_gui_classes_lc = array();
    public static array $pc_def_by_gui_class_cl = array();

    public static function init(): void
    {
        global $DIC;

        $db = $DIC->database();

        if (self::$pc_def == null) {
            $set = $db->query("SELECT * FROM copg_pc_def ORDER BY order_nr");
            while ($rec = $db->fetchAssoc($set)) {
                $rec["pc_class"] = "ilPC" . $rec["name"];
                $rec["pc_gui_class"] = "ilPC" . $rec["name"] . "GUI";
                self::$pc_gui_classes[] = $rec["pc_gui_class"];
                self::$pc_gui_classes_lc[] = strtolower($rec["pc_gui_class"]);
                self::$pc_def[$rec["pc_type"]] = $rec;
                self::$pc_def_by_name[$rec["name"]] = $rec;
                self::$pc_def_by_gui_class_cl[strtolower($rec["pc_gui_class"])] = $rec;
            }
        }
    }

    public static function getPCDefinitions(): array
    {
        self::init();
        return self::$pc_def;
    }

    /**
     * Get PC definition by type
     */
    public static function getPCDefinitionByType(string $a_pc_type): array
    {
        self::init();
        return self::$pc_def[$a_pc_type];
    }

    /**
     * Get PC definition by name
     */
    public static function getPCDefinitionByName(
        string $a_pc_name
    ): array {
        self::init();
        return self::$pc_def_by_name[$a_pc_name];
    }

    /**
     * Get PC definition by name
     */
    public static function getPCDefinitionByGUIClassName(
        string $a_gui_class_name
    ): array {
        self::init();
        $a_gui_class_name = strtolower($a_gui_class_name);
        return self::$pc_def_by_gui_class_cl[$a_gui_class_name];
    }

    public static function isPCGUIClassName(
        string $a_class_name,
        bool $a_lower_case = false
    ): bool {
        if ($a_lower_case) {
            return in_array($a_class_name, self::$pc_gui_classes_lc);
        } else {
            return in_array($a_class_name, self::$pc_gui_classes);
        }
    }

    /**
     * Get instance
     */
    public static function getPCEditorInstanceByName(
        string $a_name
    ): ?PageComponentEditor {
        $pc_def = self::getPCDefinitionByName($a_name);
        $pc_class = "ilPC" . $pc_def["name"] . "EditorGUI";
        if (class_exists($pc_class)) {
            return new $pc_class();
        }
        return null;
    }
}
