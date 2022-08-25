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
 * Class ilObjSurveyAccess
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class ilObjSurveyAccess extends ilObjectAccess implements ilConditionHandling
{
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected ilRbacSystem $rbacsystem;
    protected ilAccessHandler $access;

    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->access = $DIC->access();
    }


    public static function getConditionOperators(): array
    {
        return array(
            ilConditionHandler::OPERATOR_FINISHED
        );
    }

    public static function checkCondition(int $a_trigger_obj_id, string $a_operator, string $a_value, int $a_usr_id): bool
    {
        switch ($a_operator) {
            case ilConditionHandler::OPERATOR_FINISHED:
                if (self::_lookupFinished($a_trigger_obj_id, $a_usr_id)) {
                    return true;
                } else {
                    return false;
                }

                // no break
            default:
                return true;
        }
    }

    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null): bool
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $rbacsystem = $this->rbacsystem;
        $ilAccess = $this->access;

        if (is_null($user_id)) {
            $user_id = $ilUser->getId();
        }

        $is_admin = $rbacsystem->checkAccessOfUser($user_id, 'write', $ref_id);

        switch ($permission) {
            case "visible":
            case "read":
                if (!self::_lookupCreationComplete($obj_id) &&
                    !$is_admin) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("warning_survey_not_complete"));
                    return false;
                }
                break;
        }

        switch ($cmd) {
            case "run":
                if (!self::_lookupCreationComplete($obj_id)) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("warning_survey_not_complete"));
                    return false;
                }
                break;

            case "evaluation":
                if (!self::_lookupCreationComplete($obj_id)) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("warning_survey_not_complete"));
                    return false;
                }
                if ($rbacsystem->checkAccess("write", $ref_id) || self::_hasEvaluationAccess($obj_id, $user_id)) {
                    return true;
                } else {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("status_no_permission"));
                    return false;
                }
        }

        return true;
    }


    public static function _getCommands(): array
    {
        $commands = array(
            array("permission" => "read", "cmd" => "infoScreen", "lang_var" => "svy_run", "default" => true),
            array("permission" => "write", "cmd" => "questions", "lang_var" => "edit_questions"),
            array("permission" => "write", "cmd" => "properties", "lang_var" => "settings"),
            array("permission" => "read", "cmd" => "evaluation", "lang_var" => "svy_results")
        );

        return $commands;
    }

    //
    // object specific access related methods
    //

    /**
     * checks whether all necessary parts of the survey are given
     */
    public static function _lookupCreationComplete(int $a_obj_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $result = $ilDB->queryF(
            "SELECT * FROM svy_svy WHERE obj_fi=%s",
            array('integer'),
            array($a_obj_id)
        );

        $row = null;
        if ($result->numRows() === 1) {
            $row = $ilDB->fetchAssoc($result);
        }
        if (is_null($row) || !$row["complete"]) {
            return false;
        }
        return true;
    }

    /**
     * get evaluation access
     */
    public static function _lookupEvaluationAccess(int $a_obj_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $result = $ilDB->queryF(
            "SELECT * FROM svy_svy WHERE obj_fi=%s",
            array('integer'),
            array($a_obj_id)
        );
        if ($result->numRows() === 1) {
            $row = $ilDB->fetchAssoc($result);
            return (int) $row["evaluation_access"];
        }
        return 0;
    }

    public static function _isSurveyParticipant(
        int $user_id,
        int $survey_id
    ): bool {
        global $DIC;

        $ilDB = $DIC->database();

        $result = $ilDB->queryF(
            "SELECT finished_id FROM svy_finished WHERE user_fi = %s AND survey_fi = %s",
            array('integer','integer'),
            array($user_id, $survey_id)
        );
        return $result->numRows() === 1;
    }

    public static function _lookupAnonymize(
        int $a_obj_id
    ): bool {
        global $DIC;

        $ilDB = $DIC->database();

        $result = $ilDB->queryF(
            "SELECT anonymize FROM svy_svy WHERE obj_fi = %s",
            array('integer'),
            array($a_obj_id)
        );
        if ($result->numRows() === 1) {
            $row = $ilDB->fetchAssoc($result);
            return (bool) $row["anonymize"];
        } else {
            return false;
        }
    }

    public static function _hasEvaluationAccess(
        int $a_obj_id,
        int $user_id
    ): bool {
        $evaluation_access = self::_lookupEvaluationAccess($a_obj_id);
        $svy_mode = self::_lookupMode($a_obj_id);

        if ($svy_mode === ilObjSurvey::MODE_IND_FEEDB) {
            $svy = new ilObjSurvey($a_obj_id, false);
            $svy->read();
            switch ($svy->get360Results()) {
                case ilObjSurvey::RESULTS_360_ALL:
                case ilObjSurvey::RESULTS_360_NONE:
                    return false;

                case ilObjSurvey::RESULTS_360_OWN:
                    return true;

                    // not applicable
            }
        }

        switch ($evaluation_access) {
            case 0:
                // no evaluation access
                return false;
            case 1:
                // evaluation access for all registered users
                return ($user_id > 0) && ($user_id !== ANONYMOUS_USER_ID);
            case 2:
                switch ($svy_mode) {
                    case ilObjSurvey::MODE_360:
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

                    case ilObjSurvey::MODE_IND_FEEDB:
                        $svy = new ilObjSurvey($a_obj_id, false);
                        $svy->read();
                        switch ($svy->get360Results()) {
                            case ilObjSurvey::RESULTS_360_NONE:
                                return false;

                            case ilObjSurvey::RESULTS_360_OWN:
                                return true;

                            case ilObjSurvey::RESULTS_360_ALL:
                                return $svy->isAppraisee($user_id);
                        }
                        break;

                    case ilObjSurvey::MODE_SELF_EVAL:
                        $svy = new ilObjSurvey($a_obj_id, false);
                        $svy->read();
                        switch ($svy->getSelfEvaluationResults()) {
                            case ilObjSurvey::RESULTS_SELF_EVAL_NONE:
                                return false;
                            default:
                                return true;
                        }

                        // no break
                    default:
                        // evaluation access for participants
                        // check if the user with the given id is a survey participant

                        // show the evaluation button for anonymized surveys for all users
                        // access is only granted with the survey access code
                        if (self::_lookupAnonymize($a_obj_id)) {
                            return true;
                        }

                        global $DIC;

                        $ilDB = $DIC->database();
                        $result = $ilDB->queryF(
                            "SELECT survey_id FROM svy_svy WHERE obj_fi = %s",
                            array('integer'),
                            array($a_obj_id)
                        );
                        if ($result->numRows() === 1) {
                            $row = $ilDB->fetchAssoc($result);

                            if (self::_isSurveyParticipant($user_id, $row["survey_id"])) {
                                return true;
                            }
                        }
                        return false;
                }
        }
        return false;
    }


    /**
     * get finished status
     *
     * @param	int		$a_obj_id		survey id
     */
    public static function _lookupFinished(
        int $a_obj_id,
        int $a_user_id = 0
    ): int {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();

        $finished = 0;
        if ($a_user_id === 0) {
            $a_user_id = $ilUser->getId();
        }

        $result = $ilDB->queryF(
            "SELECT * FROM svy_svy WHERE obj_fi = %s",
            array('integer'),
            array($a_obj_id)
        );
        if ($result->numRows() === 1) {
            $row = $ilDB->fetchObject($result);
            if ((int) $row->anonymize === 1) {
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
            if ($result->numRows() === 1) {
                $foundrow = $ilDB->fetchAssoc($result);
                $finished = (int) $foundrow["state"];
            }
        }

        return $finished;
    }

    /**
     * Get survey mode (see ilObjSurvey::MODE_... constants)
     */
    public static function _lookupMode(
        int $a_obj_id
    ): int {
        global $DIC;
        $ilDB = $DIC->database();

        $result = $ilDB->queryF(
            "SELECT mode FROM svy_svy" .
            " WHERE obj_fi = %s",
            array('integer'),
            array($a_obj_id)
        );

        if ($result->numRows() === 1) {
            $row = $ilDB->fetchAssoc($result);
            return (int) $row["mode"];
        }

        return 0;
    }

    public static function _lookup360Mode(
        int $a_obj_id
    ): bool {
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
    public static function _checkGoto(string $target): bool
    {
        global $DIC;

        $request = $DIC->survey()
            ->internal()
            ->gui()
            ->execution()
            ->request();

        $ilAccess = $DIC->access();

        $t_arr = explode("_", $target);

        if ($t_arr[0] !== "svy" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        // 360° external raters
        if ($request->getAccessCode()) {
            if (ilObjSurvey::validateExternalRaterCode($t_arr[1], $request->getAccessCode())) {
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
