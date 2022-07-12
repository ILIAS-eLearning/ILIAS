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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjLearningModuleAccess extends ilObjContentObjectAccess implements ilConditionHandling, ilWACCheckingClass
{
    protected static ?array $lm_set = null;

    public function __construct()
    {
        global $DIC;
        parent::__construct();

        $this->access = $DIC->access();
    }

    /**
     * Get possible conditions operators
     */
    public static function getConditionOperators() : array
    {
        // currently only one mode "ilConditionHandler::OPERATOR_LP"
        // which is automatically added by condition handling, if lp is activated
        return array();
    }


    public static function checkCondition(int $a_trigger_obj_id, string $a_operator, string $a_value, int $a_usr_id) : bool
    {
        return true;
    }


    public static function _lookupSetting(string $a_set) : ?string
    {
        if (!is_array(self::$lm_set)) {
            $lm_set = new ilSetting("lm");
            self::$lm_set = $lm_set->getAll();
        }

        return self::$lm_set[$a_set] ?? null;
    }

    public static function _getCommands() : array
    {
        if (self::_lookupSetting("lm_starting_point") == "first") {
            $commands = array(
                array("permission" => "read", "cmd" => "view", "lang_var" => "show",
                    "default" => true),
                array("permission" => "read", "cmd" => "continue", "lang_var" => "continue_work")
            );
        } else {
            $commands = array(
                array("permission" => "read", "cmd" => "continue", "lang_var" => "continue_work", "default" => true)
            );
        }
        $commands[] = array("permission" => "write", "cmd" => "edit", "lang_var" => "edit_content");
        $commands[] = array("permission" => "write", "cmd" => "properties", "lang_var" => "settings");

        return $commands;
    }


    public function canBeDelivered(ilWACPath $ilWACPath) : bool
    {
        $ilAccess = $this->access;
        /**
         * @var $ilAccess ilAccessHandler
         */
        preg_match("/lm_data\\/lm_([0-9]*)\\//ui", $ilWACPath->getPath(), $results);
        foreach (ilObject2::_getAllReferences($results[1]) as $ref_id) {
            if ($ilAccess->checkAccess('read', '', $ref_id)) {
                return true;
            }
        }

        return false;
    }
}
