<?php declare(strict_types=1);
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilSCORMTrackingItems
 * @author  Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @ingroup ModulesScormAicc
 */
class ilSCORMTrackingItems
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function exportSelectedRawColumns() : array
    {
        global $DIC;
        $lng = $DIC->language();
        $lng->loadLanguageModule("scormtrac");
        // default fields
        $cols = array();
        $udh = self::userDataHeaderForExport();
        $a_cols = explode(
            ',',
            'lm_id,lm_title,identifierref,sco_id,sco_marked_for_learning_progress,sco_title,' . $udh["cols"]
            . ',c_timestamp,lvalue,rvalue'
        );
        $a_true = explode(',', $udh["default"] . ",identifierref,c_timestamp,lvalue,rvalue");
        for ($i = 0, $iMax = count($a_cols); $i < $iMax; $i++) {
            $cols[$a_cols[$i]] = array("txt" => $lng->txt($a_cols[$i]), "default" => false);
        }
        for ($i = 0, $iMax = count($a_true); $i < $iMax; $i++) {
            $cols[$a_true[$i]]["default"] = true;
        }
        return $cols;
    }

    /**
     * @return array<string, string>
     */
    public static function userDataHeaderForExport() : array
    {
        $privacy = ilPrivacySettings::getInstance();
        $allowExportPrivacy = $privacy->enabledExportSCORM();
        $returnData = array();
        if ($allowExportPrivacy == true) {
            $returnData["cols"] = 'login,user,email,department';
        } else {
            $returnData["cols"] = 'user';
        }
        $returnData["default"] = 'user';
        return $returnData;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function exportSelectedCoreColumns(bool $b_orderBySCO, bool $b_allowExportPrivacy) : array
    {
        global $DIC;
        $lng = $DIC->language();
        $lng->loadLanguageModule("scormtrac");
        // default fields
        $cols = array();
        $udh = self::userDataHeaderForExport();
        $a_cols = explode(
            ',',
            'lm_id,lm_title,sco_id,sco_marked_for_learning_progress,sco_title,' . $udh["cols"]
            . ',lesson_status,credit,c_entry,c_exit,c_max,c_min,c_raw,session_time,total_time,c_timestamp,suspend_data,launch_data'
        );
        $a_true = explode(',', $udh["default"] . ",sco_title,lesson_status");
        for ($i = 0, $iMax = count($a_cols); $i < $iMax; $i++) {
            $cols[$a_cols[$i]] = array("txt" => $lng->txt($a_cols[$i]), "default" => false);
        }
        for ($i = 0, $iMax = count($a_true); $i < $iMax; $i++) {
            $cols[$a_true[$i]]["default"] = true;
        }
        return $cols;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function exportSelectedInteractionsColumns() : array
    {
        global $DIC;
        $lng = $DIC->language();
        $lng->loadLanguageModule("scormtrac");
        $cols = array();
        $udh = self::userDataHeaderForExport();
        $a_cols = explode(
            ',',
            'lm_id,lm_title,sco_id,sco_marked_for_learning_progress,sco_title,' . $udh["cols"]
            . ',counter,id,weighting,type,result,student_response,latency,time,c_timestamp'
        );//,latency_seconds
        $a_true = explode(',', $udh["default"] . ",sco_title,id,result,student_response");
        for ($i = 0, $iMax = count($a_cols); $i < $iMax; $i++) {
            $cols[$a_cols[$i]] = array("txt" => $lng->txt($a_cols[$i]), "default" => false);
        }
        for ($i = 0, $iMax = count($a_true); $i < $iMax; $i++) {
            $cols[$a_true[$i]]["default"] = true;
        }
        return $cols;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function exportSelectedObjectivesColumns() : array
    {
        global $DIC;
        $lng = $DIC->language();
        $lng->loadLanguageModule("scormtrac");
        $cols = array();
        $udh = self::userDataHeaderForExport();
        $a_cols = explode(
            ',',
            'lm_id,lm_title,sco_id,sco_marked_for_learning_progress,sco_title,' . $udh["cols"]
            . ',counter,id,c_max,c_min,c_raw,ostatus,c_timestamp'
        );
        $a_true = explode(',', $udh["default"] . ",sco_title,id,c_raw,ostatus");
        for ($i = 0, $iMax = count($a_cols); $i < $iMax; $i++) {
            $cols[$a_cols[$i]] = array("txt" => $lng->txt($a_cols[$i]), "default" => false);
        }
        for ($i = 0, $iMax = count($a_true); $i < $iMax; $i++) {
            $cols[$a_true[$i]]["default"] = true;
        }
        return $cols;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function exportSelectedSuccessColumns() : array
    {
        global $DIC;
        $lng = $DIC->language();
        $lng->loadLanguageModule("scormtrac");
        // default fields
        $cols = array();

        $udh = self::userDataHeaderForExport();
        $a_cols = explode(',', 'LearningModuleId,LearningModuleTitle,LearningModuleVersion,' . $udh["cols"]
            . ',status,Percentage,Attempts,existingSCOs,startedSCOs,completedSCOs,passedSCOs,roundedTotal_timeSeconds,offline_mode,last_access');
        $a_true = explode(',', $udh["default"] . ",LearningModuleTitle,status,Percentage,Attempts");

        for ($i = 0, $iMax = count($a_cols); $i < $iMax; $i++) {
            $cols[$a_cols[$i]] = array("txt" => $lng->txt($a_cols[$i]), "default" => false);
        }
        for ($i = 0, $iMax = count($a_true); $i < $iMax; $i++) {
            $cols[$a_true[$i]]["default"] = true;
        }
        return $cols;
    }

    /**
     * @return array<int, array>
     */
    public function exportSelectedRaw(
        array $a_user,
        array $a_sco,
        bool $b_orderBySCO,
        bool $allowExportPrivacy,
        int $obj_id,
        string $lmTitle
    ) : array {
        global $DIC;
        $ilDB = $DIC->database();
        $lng = $DIC->language();
        $lng->loadLanguageModule("scormtrac");

        $returnData = array();

        $scoTitles = $this->scoTitlesForExportSelected($obj_id);

        $scoProgress = $this->markedLearningStatusForExportSelected($scoTitles, $obj_id);

        $query = 'SELECT user_id, st.obj_id, sco_id, identifierref, c_timestamp, lvalue, rvalue '
            . 'FROM scorm_tracking st '
            . 'JOIN sc_item si ON st.sco_id = si.obj_id '
            . 'WHERE ' . $ilDB->in('sco_id', $a_sco, false, 'integer') . ' '
            . 'AND ' . $ilDB->in('user_id', $a_user, false, 'integer') . ' '
//			. 'AND st.obj_id = '.$ilDB->quote($this->getId(),'integer') .' '
            . 'ORDER BY ';
        if ($b_orderBySCO) {
            $query .= 'sco_id, user_id';
        } else {
            $query .= 'user_id, sco_id';
        }
        $res = $ilDB->query($query);
        while ($data = $ilDB->fetchAssoc($res)) {
            $data["lm_id"] = $obj_id;
            $data["lm_title"] = $lmTitle;
            $data = array_merge($data, self::userDataArrayForExport((int) $data["user_id"], $allowExportPrivacy));//PHP8Review: Just a notice that this may cause huge perfomance issues. But im not sure hiw this is refactorable.
            $data["sco_marked_for_learning_progress"] = $scoProgress[$data["sco_id"]];
            $data["sco_title"] = $scoTitles[$data["sco_id"]];
            $data["rvalue"] = "" . $data["rvalue"];
            // $data["c_timestamp"] = $data["c_timestamp"];//ilDatePresentation::formatDate(new ilDateTime($data["c_timestamp"],IL_CAL_UNIX));
            $returnData[] = $data;
        }

        return $returnData;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function scoTitlesForExportSelected(int $obj_id) : array
    {
        global $DIC;
        $ilDB = $DIC->database();
        $scoTitles = array();

        $query = 'SELECT obj_id, title 
				FROM scorm_object
				WHERE slm_id = %s AND c_type = %s';
        $res = $ilDB->queryF(
            $query,
            array('integer', 'text'),
            array($obj_id, 'sit')
        );
        while ($row = $ilDB->fetchAssoc($res)) {
            $scoTitles[$row['obj_id']] = $row['title'];
        }
        return $scoTitles;
    }

    public function markedLearningStatusForExportSelected(array $a_scos, int $obj_id) : array
    {
        global $DIC;
        $lng = $DIC->language();
        $olp = ilObjectLP::getInstance($obj_id);
        $collection = $olp->getCollectionInstance();

        foreach ($a_scos as $sco_id => $value) {
            if ($collection && $collection->isAssignedEntry($sco_id)) {
                $a_scos[$sco_id] = $lng->txt('yes');
            } else {
                $a_scos[$sco_id] = $lng->txt('no');
            }
        }
        return $a_scos;
    }

    public static function userDataArrayForExport(int $user, bool $b_allowExportPrivacy = false) : array
    {
        $userArray = array();
        if ($b_allowExportPrivacy == false) {
            $userArray["user"] = $user;
        } else {
            global $DIC;
            $ilUser = $DIC->user();
            $userArray["login"] = "";
            $userArray["user"] = "";
            $userArray["email"] = "";
            $userArray["department"] = "";
            if (ilObject::_exists($user) && ilObject::_lookUpType($user) === 'usr') {
                $e_user = new ilObjUser($user);
                $userArray["login"] = $e_user->getLogin();
                $userArray["user"] = $e_user->getLastname() . ', ' . $e_user->getFirstname();
                $userArray["email"] = "" . $e_user->getEmail();
                $userArray["department"] = "" . $e_user->getDepartment();
            }
        }
        return $userArray;
    }

    /**
     * @return array<int, array>
     */
    public function exportSelectedCore(
        array $a_user,
        array $a_sco,
        bool $b_orderBySCO,
        bool $allowExportPrivacy,
        int $obj_id,
        string $lmTitle
    ) : array {
        global $DIC;
        $ilDB = $DIC->database();
        $lng = $DIC->language();
        $lng->loadLanguageModule("scormtrac");

        $returnData = array();

        $scoTitles = $this->scoTitlesForExportSelected($obj_id);

        $scoProgress = $this->markedLearningStatusForExportSelected($scoTitles, $obj_id);

        //data-arrays to fill for all users
        $a_empty = array();
        foreach ($a_user as $value) {
            $a_empty[$value] = array();
        }

        $dbdata = array();
        $query = 'SELECT user_id, sco_id, max(c_timestamp) as c_timestamp '
            . 'FROM scorm_tracking '
            . 'WHERE ' . $ilDB->in('sco_id', $a_sco, false, 'integer') . ' '
            . 'AND ' . $ilDB->in('user_id', $a_user, false, 'integer') . ' '
            . 'GROUP BY user_id, sco_id '
            . 'ORDER BY ';
        if ($b_orderBySCO) {
            $query .= 'sco_id, user_id';
        } else {
            $query .= 'user_id, sco_id';
        }
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            $dbdata[] = $row;
            $a_empty[$row["user_id"]][$row["sco_id"]] = "";
        }

        $a_lesson_status = $this->getScormTrackingValue($obj_id, $a_user, $a_sco, $a_empty, 'cmi.core.lesson_status');
        $a_credit = $this->getScormTrackingValue($obj_id, $a_user, $a_sco, $a_empty, 'cmi.core.credit');
        $a_c_entry = $this->getScormTrackingValue($obj_id, $a_user, $a_sco, $a_empty, 'cmi.core.entry');
        $a_c_exit = $this->getScormTrackingValue($obj_id, $a_user, $a_sco, $a_empty, 'cmi.core.exit');
        $a_c_max = $this->getScormTrackingValue($obj_id, $a_user, $a_sco, $a_empty, 'cmi.core.score.max');
        $a_c_min = $this->getScormTrackingValue($obj_id, $a_user, $a_sco, $a_empty, 'cmi.core.score.min');
        $a_c_raw = $this->getScormTrackingValue($obj_id, $a_user, $a_sco, $a_empty, 'cmi.core.score.raw');
        $a_session_time = $this->getScormTrackingValue($obj_id, $a_user, $a_sco, $a_empty, 'cmi.core.session_time');
        $a_total_time = $this->getScormTrackingValue($obj_id, $a_user, $a_sco, $a_empty, 'cmi.core.total_time');
        $a_suspend_data = $this->getScormTrackingValue($obj_id, $a_user, $a_sco, $a_empty, 'cmi.suspend_data');
        $a_launch_data = $this->getScormTrackingValue($obj_id, $a_user, $a_sco, $a_empty, 'cmi.launch_data');

        foreach ($dbdata as $data) {
            $data["lm_id"] = $obj_id;
            $data["lm_title"] = $lmTitle;

            $data = array_merge($data, self::userDataArrayForExport((int) $data["user_id"], $allowExportPrivacy));//PHP8Review: Just a notice that this may cause huge perfomance issues. But im not sure hiw this is refactorable.

            $data["sco_marked_for_learning_progress"] = $scoProgress[$data["sco_id"]];
            $data["sco_title"] = $scoTitles[$data["sco_id"]];

            // $data["audio_captioning"] = "".$data["audio_captioning"];
            // $data["audio_level"] = "".$data["audio_level"];
            $data["lesson_status"] = $a_lesson_status[$data['user_id']][$data['sco_id']];
            $data["credit"] = $a_credit[$data['user_id']][$data['sco_id']];
            // $data["delivery_speed"] = "".$data["delivery_speed"];
            $data["c_entry"] = $a_c_entry[$data['user_id']][$data['sco_id']];
            $data["c_exit"] = $a_c_exit[$data['user_id']][$data['sco_id']];
            // $data["c_language"] = "".$data["c_language"];
            // $data["c_location"] = "".str_replace('"','',$data["c_location"]);
            // $data["c_mode"] = "".$data["c_mode"];
            $data["c_max"] = $a_c_max[$data['user_id']][$data['sco_id']];
            $data["c_min"] = $a_c_min[$data['user_id']][$data['sco_id']];
            $data["c_raw"] = $a_c_raw[$data['user_id']][$data['sco_id']];
            $data["session_time"] = $a_session_time[$data['user_id']][$data['sco_id']];
            // $data["session_time_seconds"] = "";
            // if ($data["session_time"] != "") $data["session_time_seconds"] = round(ilObjSCORM2004LearningModule::_ISODurationToCentisec($data["session_time"])/100);
            $data["total_time"] = $a_total_time[$data['user_id']][$data['sco_id']];
            // $data["total_time_seconds"] = "";
            // if ($data["total_time"] != "") $data["total_time_seconds"] = round(ilObjSCORM2004LearningModule::_ISODurationToCentisec($data["total_time"])/100);
            $data["c_timestamp"] = $data["c_timestamp"];//ilDatePresentation::formatDate(new ilDateTime($data["c_timestamp"],IL_CAL_UNIX));
            $data["suspend_data"] = $a_suspend_data[$data['user_id']][$data['sco_id']];
            $data["launch_data"] = $a_launch_data[$data['user_id']][$data['sco_id']];
            $returnData[] = $data;
        }

        return $returnData;
    }

    public function getScormTrackingValue(int $obj_id, array $a_user, array $a_sco, array $a_empty, string $lvalue) : array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = 'SELECT user_id, sco_id, rvalue '
            . 'FROM scorm_tracking '
            . 'WHERE obj_id = %s '
            . 'AND ' . $ilDB->in('user_id', $a_user, false, 'integer') . ' '
            . 'AND ' . $ilDB->in('sco_id', $a_sco, false, 'integer') . ' '
            . 'AND lvalue=%s';
        $res = $ilDB->queryF(
            $query,
            array('integer', 'text'),
            array($obj_id, $lvalue)
        );
        while ($data = $ilDB->fetchAssoc($res)) {
            if (!is_null($data['rvalue'])) {
                $a_empty[$data['user_id']][$data['sco_id']] = $data['rvalue'];
            }
        }
        return $a_empty;
    }

    /**
     * @return array<int, array>
     */
    public function exportSelectedInteractions(
        array $a_user,
        array $a_sco,
        bool $b_orderBySCO,
        bool $allowExportPrivacy,
        int $obj_id,
        string $lmTitle
    ) : array {
        global $DIC;
        $ilDB = $DIC->database();

        $returnData = array();

        $scoTitles = $this->scoTitlesForExportSelected($obj_id);

        $scoProgress = $this->markedLearningStatusForExportSelected($scoTitles, $obj_id);

        $dbdata = array();

        $interactionsCounter = array();
        $prevcounter = -1;

        $query = 'SELECT user_id, sco_id, lvalue, c_timestamp '
            . 'FROM scorm_tracking '
            . 'WHERE obj_id = %s AND ' . $ilDB->in('sco_id', $a_sco, false, 'integer') . ' '
            . 'AND ' . $ilDB->in('user_id', $a_user, false, 'integer') . ' '
            . 'AND left(lvalue,17) = %s '
            . 'ORDER BY ';
        if ($b_orderBySCO) {
            $query .= 'sco_id, user_id, lvalue';
        } else {
            $query .= 'user_id, sco_id, lvalue';
        }
        $res = $ilDB->queryF(
            $query,
            array('integer', 'text'),
            array($obj_id, 'cmi.interactions.')
        );

        while ($row = $ilDB->fetchAssoc($res)) {
            $tmpar = explode('.', $row["lvalue"]);
            $tmpcounter = $tmpar[2];
            if (in_array($tmpcounter, $interactionsCounter) == false) {
                $interactionsCounter[] = $tmpcounter;
            }
            if ($tmpcounter != $prevcounter) {
                $tmpar = array();
                $tmpar["user_id"] = $row["user_id"];
                $tmpar["sco_id"] = $row["sco_id"];
                $tmpar["counter"] = $tmpcounter;
                $tmpar["id"] = "";
                $tmpar["weighting"] = "";
                $tmpar["type"] = "";
                $tmpar["result"] = "";
                $tmpar["student_response"] = "";
                $tmpar["latency"] = "";
                $tmpar["time"] = "";
                $tmpar["c_timestamp"] = $row["c_timestamp"];
                $dbdata[] = $tmpar;
                $prevcounter = $tmpcounter;
            }
        }
        //		id,weighting,type,result,student_response,latency,time

        $a_id = array();
        $a_weighting = array();
        $a_type = array();
        $a_result = array();
        $a_student_response = array();
        $a_latency = array();
        $a_time = array();
        foreach ($interactionsCounter as $value) {
            $a_id = array_merge(
                $a_id,
                $this->getScormTrackingValueForInteractionsOrObjectives(
                    $obj_id,
                    $a_user,
                    $a_sco,
                    'id',
                    (int) $value,
                    'interactions'
                )
            );//PHP8Review: Just a notice that this may cause huge perfomance issues. But im not sure hiw this is refactorable.
            $a_weighting = array_merge(
                $a_weighting,
                $this->getScormTrackingValueForInteractionsOrObjectives(
                    $obj_id,
                    $a_user,
                    $a_sco,
                    'weighting',
                    (int) $value,
                    'interactions'
                )
            );//PHP8Review: Just a notice that this may cause huge perfomance issues. But im not sure hiw this is refactorable.
            $a_type = array_merge(
                $a_type,
                $this->getScormTrackingValueForInteractionsOrObjectives(
                    $obj_id,
                    $a_user,
                    $a_sco,
                    'type',
                    (int) $value,
                    'interactions'
                )
            );//PHP8Review: Just a notice that this may cause huge perfomance issues. But im not sure hiw this is refactorable.
            $a_result = array_merge(
                $a_result,
                $this->getScormTrackingValueForInteractionsOrObjectives(
                    $obj_id,
                    $a_user,
                    $a_sco,
                    'result',
                    (int) $value,
                    'interactions'
                )
            );//PHP8Review: Just a notice that this may cause huge perfomance issues. But im not sure hiw this is refactorable.
            $a_student_response = array_merge(
                $a_student_response,
                $this->getScormTrackingValueForInteractionsOrObjectives(
                    $obj_id,
                    $a_user,
                    $a_sco,
                    'student_response',
                    (int) $value,
                    'interactions'
                )
            );//PHP8Review: Just a notice that this may cause huge perfomance issues. But im not sure hiw this is refactorable.
            $a_latency = array_merge(
                $a_latency,
                $this->getScormTrackingValueForInteractionsOrObjectives(
                    $obj_id,
                    $a_user,
                    $a_sco,
                    'latency',
                    (int) $value,
                    'interactions'
                )
            );//PHP8Review: Just a notice that this may cause huge perfomance issues. But im not sure hiw this is refactorable.
            $a_time = array_merge(
                $a_time,
                $this->getScormTrackingValueForInteractionsOrObjectives(
                    $obj_id,
                    $a_user,
                    $a_sco,
                    'time',
                    (int) $value,
                    'interactions'
                )
            );//PHP8Review: Just a notice that this may cause huge perfomance issues. But im not sure hiw this is refactorable.
        }
        foreach ($dbdata as $data) {
            $data["lm_id"] = $obj_id;
            $data["lm_title"] = $lmTitle;

            $data = array_merge($data, self::userDataArrayForExport((int) $data["user_id"], $allowExportPrivacy));//PHP8Review: Just a notice that this may cause huge perfomance issues. But im not sure hiw this is refactorable.

            $data["sco_marked_for_learning_progress"] = $scoProgress[$data["sco_id"]];
            $data["sco_title"] = $scoTitles[$data["sco_id"]];

            $combinedId = '' . $data["user_id"] . '-' . $data["sco_id"] . '-' . $data["counter"];
            if (array_key_exists($combinedId, $a_id)) {
                $data["id"] = $a_id[$combinedId];
            }
            if (array_key_exists($combinedId, $a_weighting)) {
                $data["weighting"] = $a_weighting[$combinedId];
            }
            if (array_key_exists($combinedId, $a_type)) {
                $data["type"] = $a_type[$combinedId];
            }
            if (array_key_exists($combinedId, $a_result)) {
                $data["result"] = $a_result[$combinedId];
            }
            if (array_key_exists($combinedId, $a_student_response)) {
                $data["student_response"] = $a_student_response[$combinedId];
            }
            if (array_key_exists($combinedId, $a_latency)) {
                $data["latency"] = $a_latency[$combinedId];
            }
            if (array_key_exists($combinedId, $a_time)) {
                $data["time"] = $a_time[$combinedId];
            }

            //$data["c_timestamp"] = $data["c_timestamp"];//ilDatePresentation::formatDate(new ilDateTime($data["c_timestamp"],IL_CAL_UNIX));
            $returnData[] = $data;
        }

        //		var_dump($returnData);
        return $returnData;
    }

    /**
     * @return array<string, mixed>
     */
    public function getScormTrackingValueForInteractionsOrObjectives(
        int $obj_id,
        array $a_user,
        array $a_sco,
        string $lvalue,
        int $counter,
        string $topic
    ) : array {
        global $DIC;
        $ilDB = $DIC->database();
        $a_return = array();
        $query = 'SELECT user_id, sco_id, rvalue '
            . 'FROM scorm_tracking '
            . 'WHERE obj_id = %s '
            . 'AND ' . $ilDB->in('user_id', $a_user, false, 'integer') . ' '
            . 'AND ' . $ilDB->in('sco_id', $a_sco, false, 'integer') . ' '
            . 'AND lvalue = %s';
        $res = $ilDB->queryF(
            $query,
            array('integer', 'text'),
            array($obj_id, 'cmi.' . $topic . '.' . $counter . '.' . $lvalue)
        );
        while ($data = $ilDB->fetchAssoc($res)) {
            if (!is_null($data['rvalue'])) {
                $a_return['' . $data['user_id'] . '-' . $data['sco_id'] . '-' . $counter] = $data['rvalue'];
            }
        }
        return $a_return;
    }

    /**
     * @return array<int, array>
     */
    public function exportSelectedObjectives(
        array $a_user,
        array $a_sco,
        bool $b_orderBySCO,
        bool $allowExportPrivacy,
        int $obj_id,
        string $lmTitle
    ) : array {
        global $DIC;
        $ilDB = $DIC->database();

        $returnData = array();

        $scoTitles = $this->scoTitlesForExportSelected($obj_id);

        $scoProgress = $this->markedLearningStatusForExportSelected($scoTitles, $obj_id);

        $dbdata = array();

        $objectivesCounter = array();
        $prevcounter = -1;

        $query = 'SELECT user_id, sco_id, lvalue, c_timestamp '
            . 'FROM scorm_tracking '
            . 'WHERE obj_id = %s AND ' . $ilDB->in('sco_id', $a_sco, false, 'integer') . ' '
            . 'AND ' . $ilDB->in('user_id', $a_user, false, 'integer') . ' '
            . 'AND left(lvalue,15) = %s '
            . 'ORDER BY ';
        if ($b_orderBySCO) {
            $query .= 'sco_id, user_id, lvalue';
        } else {
            $query .= 'user_id, sco_id, lvalue';
        }
        $res = $ilDB->queryF(
            $query,
            array('integer', 'text'),
            array($obj_id, 'cmi.objectives.')
        );

        while ($row = $ilDB->fetchAssoc($res)) {
            $tmpar = explode('.', $row["lvalue"]);
            $tmpcounter = $tmpar[2];
            if (in_array($tmpcounter, $objectivesCounter) == false) {
                $objectivesCounter[] = $tmpcounter;
            }
            if ($tmpcounter != $prevcounter) {
                $tmpar = array();
                $tmpar["user_id"] = $row["user_id"];
                $tmpar["sco_id"] = $row["sco_id"];
                $tmpar["counter"] = $tmpcounter;
                $tmpar["id"] = "";
                $tmpar["c_max"] = "";
                $tmpar["c_min"] = "";
                $tmpar["c_raw"] = "";
                $tmpar["ostatus"] = "";
                $tmpar["c_timestamp"] = $row["c_timestamp"];
                $dbdata[] = $tmpar;
                $prevcounter = $tmpcounter;
            }
        }
        $a_id = array();
        $a_c_max = array();
        $a_c_min = array();
        $a_c_raw = array();
        $a_status = array();
        foreach ($objectivesCounter as $value) {
            $a_id = array_merge(
                $a_id,
                $this->getScormTrackingValueForInteractionsOrObjectives(
                    $obj_id,
                    $a_user,
                    $a_sco,
                    'id',
                    (int) $value,
                    'objectives'
                )
            );//PHP8Review: Just a notice that this may cause huge perfomance issues. But im not sure hiw this is refactorable.
            $a_c_max = array_merge(
                $a_c_max,
                $this->getScormTrackingValueForInteractionsOrObjectives(
                    $obj_id,
                    $a_user,
                    $a_sco,
                    'score.max',
                    (int) $value,
                    'objectives'
                )
            );//PHP8Review: Just a notice that this may cause huge perfomance issues. But im not sure hiw this is refactorable.
            $a_c_min = array_merge(
                $a_c_min,
                $this->getScormTrackingValueForInteractionsOrObjectives(
                    $obj_id,
                    $a_user,
                    $a_sco,
                    'score.min',
                    (int) $value,
                    'objectives'
                )
            );//PHP8Review: Just a notice that this may cause huge perfomance issues. But im not sure hiw this is refactorable.
            $a_c_raw = array_merge(
                $a_c_raw,
                $this->getScormTrackingValueForInteractionsOrObjectives(
                    $obj_id,
                    $a_user,
                    $a_sco,
                    'score.raw',
                    (int) $value,
                    'objectives'
                )
            );//PHP8Review: Just a notice that this may cause huge perfomance issues. But im not sure hiw this is refactorable.
            $a_status = array_merge(
                $a_status,
                $this->getScormTrackingValueForInteractionsOrObjectives(
                    $obj_id,
                    $a_user,
                    $a_sco,
                    'status',
                    (int) $value,
                    'objectives'
                )
            );//PHP8Review: Just a notice that this may cause huge perfomance issues. But im not sure hiw this is refactorable.
        }
        foreach ($dbdata as $data) {
            $data["lm_id"] = $obj_id;
            $data["lm_title"] = $lmTitle;

            $data = array_merge($data, self::userDataArrayForExport((int) $data["user_id"], $allowExportPrivacy));//PHP8Review: Just a notice that this may cause huge perfomance issues. But im not sure hiw this is refactorable.

            $data["sco_marked_for_learning_progress"] = $scoProgress[$data["sco_id"]];
            $data["sco_title"] = $scoTitles[$data["sco_id"]];

            $combinedId = '' . $data["user_id"] . '-' . $data["sco_id"] . '-' . $data["counter"];
            if (array_key_exists($combinedId, $a_id)) {
                $data["id"] = $a_id[$combinedId];
            }
            if (array_key_exists($combinedId, $a_c_max)) {
                $data["c_max"] = $a_c_max[$combinedId];
            }
            if (array_key_exists($combinedId, $a_c_min)) {
                $data["c_min"] = $a_c_min[$combinedId];
            }
            if (array_key_exists($combinedId, $a_c_raw)) {
                $data["c_raw"] = $a_c_raw[$combinedId];
            }
            if (array_key_exists($combinedId, $a_status)) {
                $data["ostatus"] = $a_status[$combinedId];
            }

            //$data["c_timestamp"] = $data["c_timestamp"];//ilDatePresentation::formatDate(new ilDateTime($data["c_timestamp"],IL_CAL_UNIX));
            $returnData[] = $data;
        }

        //		var_dump($returnData);
        return $returnData;
    }

    /**
     * @return array<int, array>
     */
    public function exportSelectedSuccess(array $a_user, bool $allowExportPrivacy, int $obj_id, string $lmTitle) : array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $scoCounter = 0;
        $query = 'SELECT count(distinct(scorm_object.obj_id)) counter '
            . 'FROM scorm_object, sc_item, sc_resource '
            . 'WHERE scorm_object.slm_id = %s '
            . 'AND scorm_object.obj_id = sc_item.obj_id '
            . 'AND sc_item.identifierref = sc_resource.import_id '
            . 'AND (sc_resource.scormtype = %s OR sc_resource.scormtype is null)';
        $res = $ilDB->queryF(
            $query,
            array('integer', 'text'),
            array($obj_id, 'sco')
        );
        while ($row = $ilDB->fetchAssoc($res)) {
            $scoCounter = (int) $row['counter'];
        }

        //data-arrays for all users
        $u_startedSCO = array();
        $u_completedSCO = array();
        $u_passedSCO = array();
        foreach ($a_user as $value) {
            $u_startedSCO[$value] = 0;
            $u_completedSCO[$value] = 0;
            $u_passedSCO[$value] = 0;
        }

        $query = 'SELECT user_id, count(distinct(SCO_ID)) counter '
            . 'FROM scorm_tracking '
            . 'WHERE obj_id = %s '
            . 'AND SCO_ID > 0 '
            . 'AND ' . $ilDB->in('user_id', $a_user, false, 'integer') . ' '
            . 'GROUP BY user_id';
        $res = $ilDB->queryF(
            $query,
            array('integer'),
            array($obj_id)
        );
        while ($data = $ilDB->fetchAssoc($res)) {
            $u_startedSCO[$data['user_id']] = $data['counter'];
        }

        $query = 'SELECT user_id, count(*) counter '
            . 'FROM scorm_tracking '
            . 'WHERE obj_id = %s AND lvalue = %s AND rvalue like %s '
            . 'AND ' . $ilDB->in('user_id', $a_user, false, 'integer') . ' '
            . 'GROUP BY user_id';
        $res = $ilDB->queryF(
            $query,
            array('integer', 'text', 'text'),
            array($obj_id, 'cmi.core.lesson_status', 'completed')
        );
        while ($data = $ilDB->fetchAssoc($res)) {
            $u_completedSCO[$data['user_id']] = $data['counter'];
        }

        $res = $ilDB->queryF(
            $query,
            array('integer', 'text', 'text'),
            array($obj_id, 'cmi.core.lesson_status', 'passed')
        );
        while ($data = $ilDB->fetchAssoc($res)) {
            $u_passedSCO[$data['user_id']] = $data['counter'];
        }

        $dbdata = array();

        $query = 'SELECT * FROM sahs_user WHERE obj_id = ' . $ilDB->quote($obj_id, 'integer')
            . ' AND ' . $ilDB->in('user_id', $a_user, false, 'integer')
            . ' ORDER BY user_id';
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            $dbdata[] = $row;
        }
        return $this->exportSelectedSuccessRows(
            $a_user,
            $allowExportPrivacy,
            $dbdata,
            $scoCounter,
            $u_startedSCO,
            $u_completedSCO,
            $u_passedSCO,
            $obj_id,
            $lmTitle
        );
    }

    /**
     * @return array<int, array>
     */
    public function exportSelectedSuccessRows(
        array $a_user,
        bool $allowExportPrivacy,
        array $dbdata,
        int $scoCounter,
        array $u_startedSCO,
        array $u_completedSCO,
        array $u_passedSCO,
        int $obj_id,
        string $lmTitle
    ) : array {
        $returnData = array();
        foreach ($dbdata as $data) {
            $dat = array();
            $dat["LearningModuleId"] = $obj_id;
            $dat["LearningModuleTitle"] = "" . $lmTitle;
            $dat["LearningModuleVersion"] = "" . $data["module_version"];

            $dat = array_merge($dat, self::userDataArrayForExport((int) $data["user_id"], $allowExportPrivacy));//PHP8Review: Just a notice that this may cause huge perfomance issues. But im not sure hiw this is refactorable.

            $dat["status"] = "" . $data["status"];
            $dat["Percentage"] = "" . $data["percentage_completed"];
            $dat["Attempts"] = "" . $data["package_attempts"];
            $dat["existingSCOs"] = "" . $scoCounter;
            $dat["startedSCOs"] = "" . $u_startedSCO[$data["user_id"]];
            $dat["completedSCOs"] = "" . $u_completedSCO[$data["user_id"]];
            $dat["passedSCOs"] = "" . $u_passedSCO[$data["user_id"]];
            $dat["roundedTotal_timeSeconds"] = "" . $data["sco_total_time_sec"];
            if (is_null($data["offline_mode"])) {
                $dat["offline_mode"] = "";
            } else {
                $dat["offline_mode"] = $data["offline_mode"];
            }
            $dat["last_access"] = "" . $data["last_access"];
            $returnData[] = $dat;
        }
        return $returnData;
    }

    /**
     * @return float|string
     */
    public function SCORMTimeToSeconds(string $a_time)
    {
        if ($a_time == "") {
            return "";
        }
        $tarr = explode(":", $a_time);
        //		$sec = (int) $tarr[2] + (int) $tarr[1] * 60 + (int) substr($tarr[0], strlen($tarr[0]) - 3) * 3600;
        if (count($tarr) != 3 || is_nan((float) $tarr[0]) || is_nan((float) $tarr[1]) || is_nan((float) $tarr[2])) {
            return "";
        }
        $csec = (int) $tarr[0] * 360000 + (int) $tarr[1] * 6000 + $tarr[2] * 100;
        return round($csec / 100);
    }
}
