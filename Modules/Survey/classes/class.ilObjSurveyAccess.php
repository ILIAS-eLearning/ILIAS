<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

include_once "./Services/Object/classes/class.ilObjectAccess.php";
include_once './Services/Conditions/interfaces/interface.ilConditionHandling.php';

/**
* Class ilObjSurveyAccess
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ingroup ModulesSurvey
*/
class ilObjSurveyAccess extends ilObjectAccess implements ilConditionHandling
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilAccessHandler
     */
    protected $access;


    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->access = $DIC->access();
    }

    
    /**
     * Get possible conditions operators
     */
    public static function getConditionOperators()
    {
        include_once './Services/Conditions/classes/class.ilConditionHandler.php';
        return array(
            ilConditionHandler::OPERATOR_FINISHED
        );
    }
    
    
    /**
     * check condition
     * @param type $a_svy_id
     * @param type $a_operator
     * @param type $a_value
     * @param type $a_usr_id
     * @return boolean
     */
    public static function checkCondition($a_svy_id, $a_operator, $a_value, $a_usr_id)
    {
        switch ($a_operator) {
            case ilConditionHandler::OPERATOR_FINISHED:
                include_once("./Modules/Survey/classes/class.ilObjSurveyAccess.php");
                if (ilObjSurveyAccess::_lookupFinished($a_svy_id, $a_usr_id)) {
                    return true;
                } else {
                    return false;
                }
                break;

            default:
                return true;
        }
        return true;
    }
    
    /**
    * Checks wether a user may invoke a command or not
    * (this method is called by ilAccessHandler::checkAccess)
    *
    * Please do not check any preconditions handled by
    * ilConditionHandler here.
    *
    * @param	string		$a_cmd		command (not permission!)
    * @param	string		$a_permission	permission
    * @param	int			$a_ref_id	reference id
    * @param	int			$a_obj_id	object id
    * @param	int			$a_user_id	user id (if not provided, current user is taken)
    *
    * @return	boolean		true, if everything is ok
    */
    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $rbacsystem = $this->rbacsystem;
        $ilAccess = $this->access;

        if ($a_user_id == "") {
            $a_user_id = $ilUser->getId();
        }
        
        $is_admin = $rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id);
        
        switch ($a_permission) {
            case "visible":
            case "read":
                if (!ilObjSurveyAccess::_lookupCreationComplete($a_obj_id) &&
                    !$is_admin) {
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("warning_survey_not_complete"));
                    return false;
                }
                break;
        }

        switch ($a_cmd) {
            case "run":
                if (!ilObjSurveyAccess::_lookupCreationComplete($a_obj_id)) {
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("warning_survey_not_complete"));
                    return false;
                }
                break;

            case "evaluation":
                if (!ilObjSurveyAccess::_lookupCreationComplete($a_obj_id)) {
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("warning_survey_not_complete"));
                    return false;
                }
                if ($rbacsystem->checkAccess("write", $a_ref_id) || ilObjSurveyAccess::_hasEvaluationAccess($a_obj_id, $a_user_id)) {
                    return true;
                } else {
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("status_no_permission"));
                    return false;
                }
                break;
        }

        return true;
    }
    
    
    /**
     * get commands
     *
     * this method returns an array of all possible commands/permission combinations
     *
     * example:
     * $commands = array
     *	(
     *		array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
     *		array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
     *	);
     */
    public static function _getCommands()
    {
        $commands = array(
            array("permission" => "read", "cmd" => "infoScreen", "lang_var" => "svy_run", "default" => true),
            array("permission" => "write", "cmd" => "questionsrepo", "lang_var" => "edit_questions"),
            array("permission" => "write", "cmd" => "properties", "lang_var" => "settings"),
            array("permission" => "read", "cmd" => "evaluation", "lang_var" => "svy_results")
        );
        
        return $commands;
    }

    //
    // object specific access related methods
    //

    /**
    * checks wether all necessary parts of the survey are given
    */
    public static function _lookupCreationComplete($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $result = $ilDB->queryF(
            "SELECT * FROM svy_svy WHERE obj_fi=%s",
            array('integer'),
            array($a_obj_id)
        );

        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
        }
        if (!$row["complete"]) {
            return false;
        }
        return true;
    }

    /**
    * get evaluation access
    */
    public static function _lookupEvaluationAccess($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $result = $ilDB->queryF(
            "SELECT * FROM svy_svy WHERE obj_fi=%s",
            array('integer'),
            array($a_obj_id)
        );
        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
        }

        return $row["evaluation_access"];
    }
    
    public static function _isSurveyParticipant($user_id, $survey_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $result = $ilDB->queryF(
            "SELECT finished_id FROM svy_finished WHERE user_fi = %s AND survey_fi = %s",
            array('integer','integer'),
            array($user_id, $survey_id)
        );
        return ($result->numRows() == 1) ? true : false;
    }
    
    public static function _lookupAnonymize($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $result = $ilDB->queryF(
            "SELECT anonymize FROM svy_svy WHERE obj_fi = %s",
            array('integer'),
            array($a_obj_id)
        );
        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
            return $row["anonymize"];
        } else {
            return 0;
        }
    }
    
    public static function _hasEvaluationAccess($a_obj_id, $user_id)
    {
        $evaluation_access = ilObjSurveyAccess::_lookupEvaluationAccess($a_obj_id);
        switch ($evaluation_access) {
            case 0:
                // no evaluation access
                return false;
                break;
            case 1:
                // evaluation access for all registered users
                if (($user_id > 0) && ($user_id != ANONYMOUS_USER_ID)) {
                    return true;
                } else {
                    return false;
                }
                break;
            case 2:
                $svy_mode = self::_lookupMode($a_obj_id);
                switch ($svy_mode) {
                    case ilObjSurvey::MODE_360:
                        include_once "Modules/Survey/classes/class.ilObjSurvey.php";
                        $svy = new ilObjSurvey($a_obj_id, false);
                        $svy->read();
                        switch ($svy->get360Results()) {
                            case ilObjSurvey::RESULTS_360_NONE:
                                return false;

                            case ilObjSurvey::RESULTS_360_OWN:
                                return $svy->isAppraiseeClosed($user_id);

                            case ilObjSurvey::RESULTS_360_ALL:
                                return $svy->isAppraisee($user_id);
                        }
                        break;

                    case ilObjSurvey::MODE_SELF_EVAL:
                        include_once "Modules/Survey/classes/class.ilObjSurvey.php";
                        $svy = new ilObjSurvey($a_obj_id, false);
                        $svy->read();
                        switch ($svy->getSelfEvaluationResults()) {
                            case ilObjSurvey::RESULTS_SELF_EVAL_NONE:
                                return false;
                            default:
                                return true;
                        }
                        break;

                    default:
                        // evaluation access for participants
                        // check if the user with the given id is a survey participant

                        // show the evaluation button for anonymized surveys for all users
                        // access is only granted with the survey access code
                        if (ilObjSurveyAccess::_lookupAnonymize($a_obj_id) == 1) {
                            return true;
                        }

                        global $DIC;

                        $ilDB = $DIC->database();
                        $result = $ilDB->queryF(
                            "SELECT survey_id FROM svy_svy WHERE obj_fi = %s",
                            array('integer'),
                            array($a_obj_id)
                        );
                        if ($result->numRows() == 1) {
                            $row = $ilDB->fetchAssoc($result);

                            if (ilObjSurveyAccess::_isSurveyParticipant($user_id, $row["survey_id"])) {
                                return true;
                            }
                        }
                        return false;
                        break;
                }
                break;
        }
    }


    /**
    * get finished status
    *
    * @param	int		$a_obj_id		survey id
    */
    public static function _lookupFinished($a_obj_id, $a_user_id = "")
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();

        $finished = "";
        if (!strlen($a_user_id)) {
            $a_user_id = $ilUser->getId();
        }

        $result = $ilDB->queryF(
            "SELECT * FROM svy_svy WHERE obj_fi = %s",
            array('integer'),
            array($a_obj_id)
        );
        if ($result->numRows() == 1) {
            $row = $ilDB->fetchObject($result);
            if ($row->anonymize == 1) {
                $result = $ilDB->queryF(
                    "SELECT * FROM svy_finished, svy_anonymous WHERE svy_finished.survey_fi = %s " .
                    "AND svy_finished.survey_fi = svy_anonymous.survey_fi AND svy_anonymous.user_key = %s " .
                    "AND svy_anonymous.survey_key = svy_finished.anonymous_id",
                    array('integer','text'),
                    array($row->survey_id, md5($a_user_id))
                );
            } else {
                $result = $ilDB->queryF(
                    "SELECT * FROM svy_finished WHERE survey_fi = %s AND user_fi = %s",
                    array('integer','integer'),
                    array($row->survey_id, $a_user_id)
                );
            }
            if ($result->numRows() == 1) {
                $foundrow = $ilDB->fetchAssoc($result);
                $finished = (int) $foundrow["state"];
            }
        }

        return $finished;
    }

    /**
     * Get survey mode.
     * @param $a_obj_id
     * @return int
     */
    public static function _lookupMode($a_obj_id)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $result = $ilDB->queryF(
            "SELECT mode FROM svy_svy" .
            " WHERE obj_fi = %s",
            array('integer'),
            array($a_obj_id)
        );

        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
        }

        return $row["mode"];
    }
    
    public static function _lookup360Mode($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $result = $ilDB->queryF(
            "SELECT mode FROM svy_svy" .
            " WHERE obj_fi = %s AND mode = %s",
            array('integer','integer'),
            array($a_obj_id, ilObjSurvey::MODE_360)
        );
        return (bool) $ilDB->numRows($result);
    }
    
    /**
    * check whether goto script will succeed
    */
    public static function _checkGoto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        
        $t_arr = explode("_", $a_target);

        if ($t_arr[0] != "svy" || ((int) $t_arr[1]) <= 0) {
            return false;
        }
        
        // 360° external raters
        if ($_GET["accesscode"]) {
            include_once "Modules/Survey/classes/class.ilObjSurvey.php";
            if (ilObjSurvey::validateExternalRaterCode($t_arr[1], $_GET["accesscode"])) {
                return true;
            }
        }

        if ($ilAccess->checkAccess("visible", "", $t_arr[1]) ||
            $ilAccess->checkAccess("read", "", $t_arr[1])) {
            return true;
        }
        return false;
    }
}
