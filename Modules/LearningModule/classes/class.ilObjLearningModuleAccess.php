<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/LearningModule/classes/class.ilObjContentObjectAccess.php");
include_once './Services/Conditions/interfaces/interface.ilConditionHandling.php';
require_once('./Services/WebAccessChecker/interfaces/interface.ilWACCheckingClass.php');
require_once('./Services/Object/classes/class.ilObject2.php');

/**
 * Class ilObjLearningModuleAccess
 *
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesIliasLearningModule
 */
class ilObjLearningModuleAccess extends ilObjContentObjectAccess implements ilConditionHandling, ilWACCheckingClass
{

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        parent::__construct();

        $this->access = $DIC->access();
    }


    protected static $lm_set = null;

    /**
     * Get possible conditions operators
     */
    public static function getConditionOperators()
    {
        // currently only one mode "ilConditionHandler::OPERATOR_LP"
        // which is automatically added by condition handling, if lp is activated
        return array();
    }


    /**
     * check condition
     *
     * @param type $a_svy_id
     * @param type $a_operator
     * @param type $a_value
     * @param type $a_usr_id
     *
     * @return boolean
     */
    public static function checkCondition($a_trigger_obj_id, $a_operator, $a_value, $a_usr_id)
    {
        return true;
    }


    /**
     * @param $a_set
     * @return mixed
     */
    public static function _lookupSetting($a_set)
    {
        if (!is_array(self::$lm_set)) {
            $lm_set = new ilSetting("lm");
            self::$lm_set = $lm_set->getAll();
        }

        return self::$lm_set[$a_set];
    }

    /**
     * get commands
     *
     * this method returns an array of all possible commands/permission combinations
     *
     * example:
     * $commands = array
     *    (
     *        array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
     *        array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
     *    );
     */
    public static function _getCommands()
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


    /**
     * @param ilWACPath $ilWACPath
     *
     * @return bool
     */
    public function canBeDelivered(ilWACPath $ilWACPath)
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
