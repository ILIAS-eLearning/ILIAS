<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjSurveyQuestionPoolAccess
 *
 *
 * @author		Helmut Schottmueller <helmut.schottmueller@mac.com>
 * @author 		Alex Killing <alex.killing@gmx.de>
 */
class ilObjSurveyQuestionPoolAccess extends ilObjectAccess
{

    /**
     * get commands
     *
     * this method returns an array of all possible commands/permission combinations
     *
     * example:
     * $commands = array
     *	(
     *		array("permission" => "read", "cmd" => "view", "lang_var" => "show",
     *			"default" => true),
     *		array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
     *	);
     */
    public static function _getCommands()
    {
        $commands = array(
            array("permission" => "read", "cmd" => "questions", "lang_var" => "edit_questions",
                "default" => true),
            array("permission" => "write", "cmd" => "questions", "lang_var" => "edit_questions"),
            array("permission" => "write", "cmd" => "properties", "lang_var" => "settings")
        );
        
        return $commands;
    }

    public static function _checkGoto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        
        $t_arr = explode("_", $a_target);

        if ($ilAccess->checkAccess("visible", "", $t_arr[1]) ||
            $ilAccess->checkAccess("read", "", $t_arr[1])) {
            return true;
        }
        return false;
    }
}
