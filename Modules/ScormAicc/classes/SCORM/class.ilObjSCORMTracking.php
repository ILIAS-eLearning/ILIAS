<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjSCORMTracking
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORMTracking
{
    public function extractData()
    {
        $this->insert = array();
        if (is_array($_GET["iL"])) {
            foreach ($_GET["iL"] as $key => $value) {
                $this->insert[] = array("left" => $value, "right" => $_GET["iR"][$key]);
            }
        }
        if (is_array($_POST["iL"])) {
            foreach ($_POST["iL"] as $key => $value) {
                $this->insert[] = array("left" => $value, "right" => $_POST["iR"][$key]);
            }
        }

        $this->update = array();
        if (is_array($_GET["uL"])) {
            foreach ($_GET["uL"] as $key => $value) {
                $this->update[] = array("left" => $value, "right" => $_GET["uR"][$key]);
            }
        }
        if (is_array($_POST["uL"])) {
            foreach ($_POST["uL"] as $key => $value) {
                $this->update[] = array("left" => $value, "right" => $_POST["uR"][$key]);
            }
        }
    }

    public function store($obj_id = 0, $sahs_id = 0, $extractData = 1)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        $ref_id = $_GET["ref_id"];
        if (empty($obj_id)) {
            $obj_id = ilObject::_lookupObjId($_GET["ref_id"]);
        }
        
        // writing to scorm test log
        $f = fopen("./Modules/ScormAicc/log/scorm.log", "a");
        fwrite($f, "\nCALLING SCORM store()\n");
        fwrite($f, 'POST: ' . print_r($_POST, true));
        
        
        if (empty($sahs_id)) {
            $sahs_id = ($_GET["sahs_id"] != "")	? $_GET["sahs_id"] : $_POST["sahs_id"];
        }
            
        if ($extractData == 1) {
            $this->extractData();
        }

        if (is_object($ilUser)) {
            $user_id = $ilUser->getId();
        }

        

        if ($obj_id <= 1) {
            fwrite($f, "Error: No obj_id given.\n");
        } else {
            foreach ($this->insert as $insert) {
                $set = $ilDB->queryF(
                    '
				SELECT * FROM scorm_tracking 
				WHERE user_id = %s
				AND sco_id =  %s
				AND lvalue =  %s
				AND obj_id = %s',
                    array('integer','integer','text','integer'),
                    array($user_id,$sahs_id,$insert["left"],$obj_id)
                );
                if ($rec = $ilDB->fetchAssoc($set)) {
                    fwrite($f, "Error Insert, left value already exists. L:" . $insert["left"] . ",R:" .
                        $insert["right"] . ",sahs_id:" . $sahs_id . ",user_id:" . $user_id . "\n");
                } else {
                    $ilDB->insert('scorm_tracking', array(
                        'obj_id' => array('integer', $obj_id),
                        'user_id' => array('integer', $user_id),
                        'sco_id' => array('integer', $sahs_id),
                        'lvalue' => array('text', $insert["left"]),
                        'rvalue' => array('clob', $insert["right"]),
                        'c_timestamp' => array('timestamp', ilUtil::now())
                    ));
                                        
                    fwrite($f, "Insert - L:" . $insert["left"] . ",R:" .
                        $insert["right"] . ",sahs_id:" . $sahs_id . ",user_id:" . $user_id . "\n");
                }
            }
            foreach ($this->update as $update) {
                $set = $ilDB->queryF(
                    '
				SELECT * FROM scorm_tracking 
				WHERE user_id = %s
				AND sco_id =  %s
				AND lvalue =  %s
				AND obj_id = %s',
                    array('integer','integer','text','integer'),
                    array($user_id,$sahs_id,$update["left"],$obj_id)
                );
                
                if ($rec = $ilDB->fetchAssoc($set)) {
                    $ilDB->update(
                        'scorm_tracking',
                        array(
                            'rvalue' => array('clob', $update["right"]),
                            'c_timestamp' => array('timestamp', ilUtil::now())
                        ),
                        array(
                            'user_id' => array('integer', $user_id),
                            'sco_id' => array('integer', $sahs_id),
                            'lvalue' => array('text', $update["left"]),
                            'obj_id' => array('integer', $obj_id)
                        )
                    );
                } else {
                    fwrite($f, "ERROR Update, left value does not exist. L:" . $update["left"] . ",R:" .
                        $update["right"] . ",sahs_id:" . $sahs_id . ",user_id:" . $user_id . "\n");
                }
            }
        }
        fclose($f);
        
        // update status
        include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
        ilLPStatusWrapper::_updateStatus($obj_id, $user_id);
        
        // update time and numbers of attempts in change event
        //NOTE: is possibly not correct (it is count of commit with changed values); be careful to performance issues
        ilObjSCORMTracking::_syncReadEvent($obj_id, $user_id, "sahs", $ref_id);
    }
    
    public static function storeJsApi($obj_id = 0)
    {
        // global $DIC;
        // $ilLog = $DIC['ilLog'];
        // $ilUser = $DIC['ilUser'];

        // if (is_object($ilUser)) {
        // $user_id = $ilUser->getId();
        // }
        // if (empty($obj_id)) $obj_id = ilObject::_lookupObjId($_GET["ref_id"]);
        $obj_id = (int) $_GET["package_id"];
        $in = file_get_contents("php://input");
        // $ilLog->write($in);
        $data = json_decode($in);
        $user_id = (int) $data->p;

        header('Content-Type: text/plain; charset=UTF-8');

        $rval = self::storeJsApiCmi($user_id, $obj_id, $data);
        if ($rval != true) {
            print("storeJsApiCmi failed");
        } else {
            $rval = self::syncGlobalStatus($user_id, $obj_id, $data, $data->now_global_status);
            if ($rval != true) {
                print("syncGlobalStatus failed");
            }
        }
        if ($rval == true) {
            print("ok");
        }
    }

    public static function storeJsApiCmi($user_id, $obj_id, $data)
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];
        $ilDB = $DIC['ilDB'];
        
        $b_updateStatus = false;
        
        $b_messageLog = false;
        if ($ilLog->current_log_level == 30) {
            $b_messageLog = true;
        }

        if ($b_messageLog) {
            $ilLog->write("ScormAicc: CALLING SCORM storeJsApi() " . $_POST);
        }


        $aa_data = array();
        // if (is_array($_POST["S"])) {
        // foreach($_POST["S"] as $key => $value) {
        // $aa_data[] = array("sco_id" => $value, "left" => $_POST["L"][$key], "right" => $_POST["R"][$key]);
        // }
        // }
        for ($i = 0;$i < count($data->cmi);$i++) {
            $aa_data[] = array("sco_id" => (int) $data->cmi[$i][0], "left" => $data->cmi[$i][1], "right" => $data->cmi[$i][2]);
            //			$aa_data[] = array("sco_id" => (int) $data->cmi[$i][0], "left" => $data->cmi[$i][1], "right" => rawurldecode($data->cmi[$i][2]));
        }

        if ($obj_id <= 1) {
            $ilLog->write("ScormAicc: storeJsApi: Error: No valid obj_id given.");
        } else {
            foreach ($aa_data as $a_data) {
                $set = $ilDB->queryF(
                    '
				SELECT rvalue FROM scorm_tracking 
				WHERE user_id = %s
				AND sco_id =  %s
				AND lvalue =  %s
				AND obj_id = %s',
                    array('integer','integer','text','integer'),
                    array($user_id,$a_data["sco_id"],$a_data["left"],$obj_id)
                );
                if ($rec = $ilDB->fetchAssoc($set)) {
                    if ($a_data["left"] == 'cmi.core.lesson_status' && $a_data["right"] != $rec["rvalue"]) {
                        $b_updateStatus = true;
                    }
                    $ilDB->update(
                        'scorm_tracking',
                        array(
                            'rvalue' => array('clob', $a_data["right"]),
                            'c_timestamp' => array('timestamp', ilUtil::now())
                        ),
                        array(
                            'user_id' => array('integer', $user_id),
                            'sco_id' => array('integer', $a_data["sco_id"]),
                            'lvalue' => array('text', $a_data["left"]),
                            'obj_id' => array('integer', $obj_id)
                        )
                    );
                    if ($b_messageLog) {
                        $ilLog->write("ScormAicc: storeJsApi Updated - L:" . $a_data["left"] . ",R:" .
                        $a_data["right"] . " for obj_id:" . $obj_id . ",sco_id:" . $a_data["sco_id"] . ",user_id:" . $user_id);
                    }
                } else {
                    if ($a_data["left"] == 'cmi.core.lesson_status') {
                        $b_updateStatus = true;
                    }
                    $ilDB->insert('scorm_tracking', array(
                        'obj_id' => array('integer', $obj_id),
                        'user_id' => array('integer', $user_id),
                        'sco_id' => array('integer', $a_data["sco_id"]),
                        'lvalue' => array('text', $a_data["left"]),
                        'rvalue' => array('clob', $a_data["right"]),
                        'c_timestamp' => array('timestamp', ilUtil::now())
                    ));
                    if ($b_messageLog) {
                        $ilLog->write("ScormAicc: storeJsApi Inserted - L:" . $a_data["left"] . ",R:" .
                        $a_data["right"] . " for obj_id:" . $obj_id . ",sco_id:" . $a_data["sco_id"] . ",user_id:" . $user_id);
                    }
                }
            }
        }
        
        // update status
        // if ($b_updateStatus == true) {
        // include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
        // ilLPStatusWrapper::_updateStatus($obj_id, $user_id);
        // }

        return true;
    }

    //erase later see ilSCORM2004StoreData
    public static function syncGlobalStatus($userId, $packageId, $data, $new_global_status)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];
        $saved_global_status = $data->saved_global_status;
        $ilLog->write("saved_global_status=" . $saved_global_status);

        //last_visited!
        
        // get attempts
        if (!$data->packageAttempts) {
            $val_set = $ilDB->queryF(
                'SELECT package_attempts FROM sahs_user WHERE obj_id = %s AND user_id = %s',
                array('integer','integer'),
                array($packageId,$userId)
            );
            $val_rec = $ilDB->fetchAssoc($val_set);
            $attempts = $val_rec["package_attempts"];
        } else {
            $attempts = $data->packageAttempts;
        }
        if ($attempts == null) {
            $attempts = 1;
        }

        //update percentage_completed, sco_total_time_sec,status in sahs_user
        $totalTime = (int) $data->totalTimeCentisec;
        $totalTime = round($totalTime / 100);
        $ilDB->queryF(
            'UPDATE sahs_user SET sco_total_time_sec=%s, status=%s, percentage_completed=%s, package_attempts=%s WHERE obj_id = %s AND user_id = %s',
            array('integer', 'integer', 'integer', 'integer', 'integer', 'integer'),
            array($totalTime, $new_global_status, $data->percentageCompleted, $attempts, $packageId, $userId)
        );
        
        //		self::ensureObjectDataCacheExistence();
        global $DIC;
        $ilObjDataCache = $DIC['ilObjDataCache'];
        include_once("./Services/Tracking/classes/class.ilChangeEvent.php");
        ilChangeEvent::_recordReadEvent("sahs", (int) $_GET['ref_id'], $packageId, $userId, false, $attempts, $totalTime);

        //end sync access number and time in read event table

        // update learning progress
        if ($new_global_status != null) {//could only happen when synchronising from SCORM Offline Player
            include_once("./Services/Tracking/classes/class.ilObjUserTracking.php");
            include_once("./Services/Tracking/classes/class.ilLPStatus.php");
            ilLPStatus::writeStatus($packageId, $userId, $new_global_status, $data->percentageCompleted);

            //			here put code for soap to MaxCMS e.g. when if($saved_global_status != $new_global_status)
        }
        return true;
    }


    /**
     * Synch read event table
     *
     * @param
     * @return
     */
    public static function _syncReadEvent($a_obj_id, $a_user_id, $a_type, $a_ref_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];
        $val_set = $ilDB->queryF(
            'SELECT package_attempts, total_time_sec, sco_total_time_sec, time_from_lms FROM sahs_user, sahs_lm '
                                . 'WHERE sahs_user.obj_id = %s AND sahs_user.user_id = %s AND sahs_user.obj_id = sahs_lm.id',
            array('integer','integer'),
            array($a_obj_id,$a_user_id)
        );
        
        $val_rec = $ilDB->fetchAssoc($val_set);
        
        if ($val_rec["package_attempts"] == null) {
            $val_rec["package_attempts"] = "";
        }
        $attempts = $val_rec["package_attempts"];
        
        $time = 0;
        // if ($val_rec["time_from_lms"] == "y") {
        // $time = (int)$val_rec["total_time_sec"];
        // } else {
        $time = (int) $val_rec["sco_total_time_sec"];
        // }

        // get learning time for old ILIAS-Versions
        if ($time == 0) {
            $sco_set = $ilDB->queryF(
                '
			SELECT sco_id, rvalue FROM scorm_tracking 
			WHERE obj_id = %s
			AND user_id = %s
			AND lvalue = %s
			AND sco_id <> %s',
                array('integer','integer','text','integer'),
                array($a_obj_id,$a_user_id, 'cmi.core.total_time',0)
            );

            while ($sco_rec = $ilDB->fetchAssoc($sco_set)) {
                $tarr = explode(":", $sco_rec["rvalue"]);
                $sec = (int) $tarr[2] + (int) $tarr[1] * 60 +
                    (int) substr($tarr[0], strlen($tarr[0]) - 3) * 60 * 60;
                $time += $sec;
            }
        }
        
        include_once("./Services/Tracking/classes/class.ilChangeEvent.php");
        ilChangeEvent::_recordReadEvent($a_type, $a_ref_id, $a_obj_id, $a_user_id, false, $attempts, $time);
    }

    public static function _insertTrackData($a_sahs_id, $a_lval, $a_rval, $a_obj_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        $ilDB->insert('scorm_tracking', array(
            'obj_id' => array('integer', $a_obj_id),
            'user_id' => array('integer', $ilUser->getId()),
            'sco_id' => array('integer', $a_sahs_id),
            'lvalue' => array('text', $a_lval),
            'rvalue' => array('clob', $a_rval),
            'c_timestamp' => array('timestamp', ilUtil::now())
        ));
        
        if ($a_lval == "cmi.core.lesson_status") {
            include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
            ilLPStatusWrapper::_updateStatus($a_obj_id, $ilUser->getId());
        }
    }


    /**
     * @param object $scorm_item_id
     * @param object $a_obj_id
     * @param array $a_blocked_user_ids
     * @return
     */
    public static function _getInProgress($scorm_item_id, $a_obj_id, $a_blocked_user_ids = null)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        if (is_array($scorm_item_id)) {
            $in = $ilDB->in('sco_id', $scorm_item_id, false, 'integer');
            
            $res = $ilDB->queryF(
                'SELECT user_id,sco_id FROM scorm_tracking
			WHERE ' . $in . '
			AND obj_id = %s 
			GROUP BY user_id, sco_id',
                array('integer'),
                array($a_obj_id)
            );
        } else {
            $res = $ilDB->queryF(
                'SELECT user_id,sco_id FROM scorm_tracking			
			WHERE sco_id = %s 
			AND obj_id = %s',
                array('integer','integer'),
                array($scorm_item_id,$a_obj_id)
            );
        }
        
        $in_progress = array();
        
        while ($row = $ilDB->fetchObject($res)) {
            // #15061 - see _getProgressInfo()
            if (!($a_blocked_user_ids &&
                is_array($a_blocked_user_ids[$row->sco_id]) &&
                in_array($row->user_id, $a_blocked_user_ids[$row->sco_id]))) {
                $in_progress[$row->sco_id][] = $row->user_id;
            }
        }
        return $in_progress;
    }

    /**
     * like necessary because of Oracle
     * @param object $scorm_item_id
     * @param object $a_obj_id
     * @return
     */
    public static function _getCompleted($scorm_item_id, $a_obj_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (is_array($scorm_item_id)) {
            $in = $ilDB->in('sco_id', $scorm_item_id, false, 'integer');
            
            $res = $ilDB->queryF(
                'SELECT DISTINCT(user_id) FROM scorm_tracking 
			WHERE ' . $in . '
			AND obj_id = %s
			AND lvalue = %s 
			AND (' . $ilDB->like('rvalue', 'clob', 'completed') . ' OR ' . $ilDB->like('rvalue', 'clob', 'passed') . ')',
                array('integer','text'),
                array($a_obj_id,'cmi.core.lesson_status')
            );
        } else {
            $res = $ilDB->queryF(
                'SELECT DISTINCT(user_id) FROM scorm_tracking 
			WHERE sco_id = %s
			AND obj_id = %s
			AND lvalue = %s 
			AND (' . $ilDB->like('rvalue', 'clob', 'completed') . ' OR ' . $ilDB->like('rvalue', 'clob', 'passed') . ')',
                array('integer','integer','text'),
                array($scorm_item_id,$a_obj_id,'cmi.core.lesson_status')
            );
        }
        
        while ($row = $ilDB->fetchObject($res)) {
            $user_ids[] = $row->user_id;
        }
        return $user_ids ? $user_ids : array();
    }

    public static function _getCollectionStatus($a_scos, $a_obj_id, $a_user_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];


        $status = "not_attempted";
        
        if (is_array($a_scos)) {
            $in = $ilDB->in('sco_id', $a_scos, false, 'integer');
            
            $res = $ilDB->queryF(
                'SELECT sco_id, rvalue FROM scorm_tracking 
			WHERE ' . $in . '
			AND obj_id = %s
			AND lvalue = %s
			AND user_id = %s',
                array('integer','text', 'integer'),
                array($a_obj_id,'cmi.core.lesson_status', $a_user_id)
            );
            
            $cnt = 0;
            $completed = true;
            $failed = false;
            while ($rec = $ilDB->fetchAssoc($res)) {
                if ($rec["rvalue"] == "failed") {
                    $failed = true;
                }
                if ($rec["rvalue"] != "completed" && $rec["rvalue"] != "passed") {
                    $completed = false;
                }
                $cnt++;
            }
            if ($cnt > 0) {
                $status = "in_progress";
            }
            if ($completed && $cnt == count($a_scos)) {
                $status = "completed";
            }
            if ($failed) {
                $status = "failed";
            }
        }
        return $status;
    }
    

    public static function _countCompleted($a_scos, $a_obj_id, $a_user_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (is_array($a_scos)) {
            $in = $ilDB->in('sco_id', $a_scos, false, 'integer');
            
            $res = $ilDB->queryF(
                'SELECT sco_id, rvalue FROM scorm_tracking 
			WHERE ' . $in . '
			AND obj_id = %s
			AND lvalue = %s
			AND user_id = %s',
                array('integer','text', 'integer'),
                array($a_obj_id,'cmi.core.lesson_status', $a_user_id)
            );
            
            $cnt = 0;
            while ($rec = $ilDB->fetchAssoc($res)) {
                if ($rec["rvalue"] == "completed" || $rec["rvalue"] == "passed") {
                    $cnt++;
                }
            }
        }
        return $cnt;
    }
    //not correct because of assets!
    /**
     * Lookup last acccess time for all users of a scorm module
     * @global ilDB $ilDB
     * @param int $a_obj_id
     * @return array
     */
    public static function lookupLastAccessTimes($a_obj_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'SELECT user_id, MAX(c_timestamp) tst ' .
            'FROM scorm_tracking ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer') . ' ' .
            'GROUP BY user_id';
        $res = $ilDB->query($query);

        $users = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $users[$row->user_id] = $row->tst;
        }
        return $users;
    }


    /**
     * Get all tracked users
     * @param object $a_obj_id
     * @return
     */
    public static function _getTrackedUsers($a_obj_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];

        $res = $ilDB->queryF(
            'SELECT DISTINCT user_id FROM scorm_tracking 
			WHERE obj_id = %s
			AND lvalue = %s',
            array('integer','text'),
            array($a_obj_id,'cmi.core.lesson_status')
        );
        
        $users = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $users[] = $row["user_id"];
        }
        return $users;
    }

    /**
     * like necessary because of Oracle
     * @param object $scorm_item_id
     * @param object $a_obj_id
     * @return
     */
    public static function _getFailed($scorm_item_id, $a_obj_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (is_array($scorm_item_id)) {
            $in = $ilDB->in('sco_id', $scorm_item_id, false, 'integer');
            
            $res = $ilDB->queryF(
                '
				SELECT DISTINCT(user_id) FROM scorm_tracking 
				WHERE ' . $in . '
				AND obj_id = %s
				AND lvalue =  %s
				AND ' . $ilDB->like('rvalue', 'clob', 'failed') . ' ',
                array('integer','text'),
                array($a_obj_id,'cmi.core.lesson_status')
            );
        } else {
            $res = $ilDB->queryF(
                '
				SELECT DISTINCT(user_id) FROM scorm_tracking 
				WHERE sco_id = %s
				AND obj_id = %s
				AND lvalue =  %s
				AND ' . $ilDB->like('rvalue', 'clob', 'failed') . ' ',
                array('integer','integer','text'),
                array($scorm_item_id,$a_obj_id,'cmi.core.lesson_status')
            );
        }

        while ($row = $ilDB->fetchObject($res)) {
            $user_ids[] = $row->user_id;
        }
        return $user_ids ? $user_ids : array();
    }

    /**
     * Get users who have status completed or passed.
     * @param object $a_scorm_item_ids
     * @param object $a_obj_id
     * @return
     */
    public static function _getCountCompletedPerUser($a_scorm_item_ids, $a_obj_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $in = $ilDB->in('sco_id', $a_scorm_item_ids, false, 'integer');

        // Why does this query use a like search against "passed" and "failed"
        //because it's clob and we support Oracle
        $res = $ilDB->queryF(
            '
			SELECT user_id, COUNT(user_id) completed FROM scorm_tracking
			WHERE ' . $in . '
			AND obj_id = %s
			AND lvalue = %s 
			AND (' . $ilDB->like('rvalue', 'clob', 'completed') . ' OR ' . $ilDB->like('rvalue', 'clob', 'passed') . ')
			GROUP BY user_id',
            array('integer', 'text'),
            array($a_obj_id, 'cmi.core.lesson_status')
        );
        while ($row = $ilDB->fetchObject($res)) {
            $users[$row->user_id] = $row->completed;
        }
        return $users ? $users : array();
    }

    /**
     * Get info about
     * @param object $sco_item_ids
     * @param object $a_obj_id
     * @return
     */
    public static function _getProgressInfo($sco_item_ids, $a_obj_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $in = $ilDB->in('sco_id', $sco_item_ids, false, 'integer');

        $res = $ilDB->queryF(
            '
		SELECT * FROM scorm_tracking 
		WHERE ' . $in . '
		AND obj_id = %s 
		AND lvalue = %s ',
            array('integer','text'),
            array($a_obj_id,'cmi.core.lesson_status')
        );
        
        $info['completed'] = array();
        $info['failed'] = array();
        
        $user_ids = array();
        while ($row = $ilDB->fetchObject($res)) {
            switch ($row->rvalue) {
                case 'completed':
                case 'passed':
                    $info['completed'][$row->sco_id][] = $row->user_id;
                    $user_ids[$row->sco_id][] = $row->user_id;
                    break;

                case 'failed':
                    $info['failed'][$row->sco_id][] = $row->user_id;
                    $user_ids[$row->sco_id][] = $row->user_id;
                    break;
            }
        }
        $info['in_progress'] = ilObjSCORMTracking::_getInProgress($sco_item_ids, $a_obj_id, $user_ids);

        return $info;
    }

    public static function scorm12PlayerUnload()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $ilDB = $DIC['ilDB'];

        //$user_id = $ilUser->getID();
        $user_id = (int) $_GET["p"];
        $ref_id = (int) $_GET["ref_id"];
        // $obj_id = ilObject::_lookupObjId($ref_id);
        $obj_id = (int) $_GET["package_id"];
        if ($obj_id <= 1) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ' no valid obj_id');
        } else {
            $last_visited = $_POST['last_visited'];
            $endDate = date('Y-m-d H:i:s', mktime(date('H'), date('i') + 5, date('s'), date('m'), date('d'), date('Y')));
            $ilDB->manipulateF(
                'UPDATE sahs_user 
				SET last_visited = %s, hash_end =%s, last_access = %s
				WHERE obj_id = %s AND user_id = %s',
                array('text', 'timestamp', 'timestamp', 'integer', 'integer'),
                array($last_visited, $endDate, date('Y-m-d H:i:s'), $obj_id, $user_id)
            );
            // update time and numbers of attempts in change event
            //NOTE: here it is correct (not count of commit with changed values); be careful to performance issues
            ilObjSCORMTracking::_syncReadEvent($obj_id, $user_id, "sahs", $ref_id);
        }
        header('Content-Type: text/plain; charset=UTF-8');
        print("");
    }
    
    public static function checkIfAllowed($packageId, $userId, $hash)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $res = $ilDB->queryF(
            'select hash from sahs_user where obj_id=%s AND user_id=%s AND hash_end>%s',
            array('integer','integer','timestamp'),
            array($packageId,$userId,date('Y-m-d H:i:s'))
        );
        $rowtmp = $ilDB->fetchAssoc($res);
        if ($rowtmp['hash'] == $hash) {
            return;
        } else {
            die("not allowed");
        }
    }
} // END class.ilObjSCORMTracking
