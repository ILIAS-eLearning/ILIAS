<?php

declare(strict_types=1);

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
 * Class ilObjSCORMTracking
 * @author  Alex Killing <alex.killing@gmx.de>
 * @ingroup ModulesScormAicc
 */
class ilObjSCORMTracking
{
    public static function storeJsApi(): void
    {
        global $DIC;
        $obj_id = $DIC->http()->wrapper()->query()->retrieve('package_id', $DIC->refinery()->kindlyTo()->int());
        $refId = $DIC->http()->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int());
        $in = file_get_contents("php://input");
        $data = json_decode($in);
        $user_id = (int) $data->p;

        header('Content-Type: text/plain; charset=UTF-8');

        $rval = self::storeJsApiCmi($user_id, $obj_id, $data);
        if ($rval != true) {
            print("storeJsApiCmi failed");
        } else {
            $rval = self::syncGlobalStatus($user_id, $obj_id, $refId, $data, $data->now_global_status);
            if ($rval != true) {
                print("syncGlobalStatus failed");
            }
        }
        if ($rval == true) {
            print("ok");
        }
    }

    public static function storeJsApiCmi(int $user_id, int $obj_id, object $data): bool
    {
        global $DIC;
        $ilLog = ilLoggerFactory::getLogger('sahs');
        $ilDB = $DIC->database();

        $b_updateStatus = false;
        $i_score_max = 0;
        $i_score_raw = 0;

        $aa_data = array();
        // if (is_array($_POST["S"])) {
        // foreach($_POST["S"] as $key => $value) {
        // $aa_data[] = array("sco_id" => $value, "left" => $_POST["L"][$key], "right" => $_POST["R"][$key]);
        // }
        // }
        foreach ($data->cmi as $value) {
            $aa_data[] = array("sco_id" => (int) $value[0],
                               "left" => $value[1],
                               "right" => $value[2]
            );
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
                    array('integer', 'integer', 'text', 'integer'),
                    array($user_id, $a_data["sco_id"], $a_data["left"], $obj_id)
                );
                if ($rec = $ilDB->fetchAssoc($set)) {
                    if ($a_data["left"] === 'cmi.core.lesson_status' && $a_data["right"] != $rec["rvalue"]) {
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
                    $ilLog->debug("ScormAicc: storeJsApi Updated - L:" . $a_data["left"] . ",R:" .
                        $a_data["right"] . " for obj_id:" . $obj_id . ",sco_id:" . $a_data["sco_id"] . ",user_id:" . $user_id);
                } else {
                    if ($a_data["left"] === 'cmi.core.lesson_status') {
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
                    $ilLog->debug("ScormAicc: storeJsApi Inserted - L:" . $a_data["left"] . ",R:" .
                        $a_data["right"] . " for obj_id:" . $obj_id . ",sco_id:" . $a_data["sco_id"] . ",user_id:" . $user_id);
                }
                if ($a_data["left"] === 'cmi.core.score.max') {
                    $i_score_max = $a_data["right"];
                }
                if ($a_data["left"] === 'cmi.core.score.raw') {
                    $i_score_raw = $a_data["right"];
                }
            }
            // mantis #30293
            if ($i_score_max > 0 && $i_score_raw > 0) {
                if (count(ilSCORMObject::_lookupPresentableItems($obj_id)) == 1) {
                    ilLTIAppEventListener::handleOutcomeWithoutLP(
                        $obj_id,
                        $user_id,
                        ($i_score_raw / $i_score_max) * 100
                    );
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

    public static function syncGlobalStatus(int $userId, int $packageId, int $refId, object $data, ?int $new_global_status): bool
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilLog = ilLoggerFactory::getLogger('sahs');
        $saved_global_status = $data->saved_global_status;
        $ilLog->write("saved_global_status=" . $saved_global_status);

        // get attempts
        if (!$data->packageAttempts) {
            $val_set = $ilDB->queryF(
                'SELECT package_attempts FROM sahs_user WHERE obj_id = %s AND user_id = %s',
                array('integer', 'integer'),
                array($packageId, $userId)
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
            'UPDATE sahs_user SET last_visited=%s, last_access = %s, sco_total_time_sec=%s, status=%s, percentage_completed=%s, package_attempts=%s WHERE obj_id = %s AND user_id = %s',
            array('text', 'timestamp', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer'),
            array($data->last_visited,
                  date('Y-m-d H:i:s'),
                  $totalTime,
                  $new_global_status,
                  $data->percentageCompleted,
                  $attempts,
                  $packageId,
                  $userId
            )
        );

        //		self::ensureObjectDataCacheExistence();
        global $DIC;
        $ilObjDataCache = $DIC['ilObjDataCache'];
        ilChangeEvent::_recordReadEvent(
            "sahs",
            $refId,
            $packageId,
            $userId,
            false,
            $attempts,
            $totalTime
        );

        //end sync access number and time in read event table

        // update learning progress
        if ($new_global_status != null) {
            ilLPStatus::writeStatus($packageId, $userId, $new_global_status, $data->percentageCompleted);

            //			here put code for soap to MaxCMS e.g. when if($saved_global_status != $new_global_status)
        }
        return true;
    }

    public static function _insertTrackData(int $a_sahs_id, string $a_lval, string $a_rval, int $a_obj_id): void
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilUser = $DIC->user();

        $ilDB->insert('scorm_tracking', array(
            'obj_id' => array('integer', $a_obj_id),
            'user_id' => array('integer', $ilUser->getId()),
            'sco_id' => array('integer', $a_sahs_id),
            'lvalue' => array('text', $a_lval),
            'rvalue' => array('clob', $a_rval),
            'c_timestamp' => array('timestamp', ilUtil::now())
        ));

        if ($a_lval === "cmi.core.lesson_status") {
            ilLPStatusWrapper::_updateStatus($a_obj_id, $ilUser->getId());
        }
    }

    //erase later see ilSCORM2004StoreData
    /**
     * like necessary because of Oracle
     * @return mixed[]
     */
    public static function _getCompleted(object $scorm_item_id, int $a_obj_id): array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $user_ids = [];

        if (is_array($scorm_item_id)) {
            $in = $ilDB->in('sco_id', $scorm_item_id, false, 'integer');

            $res = $ilDB->queryF(
                'SELECT DISTINCT(user_id) FROM scorm_tracking 
			WHERE ' . $in . '
			AND obj_id = %s
			AND lvalue = %s 
			AND (' . $ilDB->like('rvalue', 'clob', 'completed') . ' OR ' . $ilDB->like(
                    'rvalue',
                    'clob',
                    'passed'
                ) . ')',
                array('integer', 'text'),
                array($a_obj_id, 'cmi.core.lesson_status')
            );
        } else {
            $res = $ilDB->queryF(
                'SELECT DISTINCT(user_id) FROM scorm_tracking 
			WHERE sco_id = %s
			AND obj_id = %s
			AND lvalue = %s 
			AND (' . $ilDB->like('rvalue', 'clob', 'completed') . ' OR ' . $ilDB->like(
                    'rvalue',
                    'clob',
                    'passed'
                ) . ')',
                array('integer', 'integer', 'text'),
                array($scorm_item_id, $a_obj_id, 'cmi.core.lesson_status')
            );
        }

        while ($row = $ilDB->fetchObject($res)) {
            $user_ids[] = $row->user_id;
        }
        return $user_ids;
    }

    public static function _getCollectionStatus(?array $a_scos, int $a_obj_id, int $a_user_id): string
    {
        global $DIC;
        $ilDB = $DIC->database();

        $status = "not_attempted";

        if (is_array($a_scos)) {
            $in = $ilDB->in('sco_id', $a_scos, false, 'integer');

            $res = $ilDB->queryF(
                'SELECT sco_id, rvalue FROM scorm_tracking 
			WHERE ' . $in . '
			AND obj_id = %s
			AND lvalue = %s
			AND user_id = %s',
                array('integer', 'text', 'integer'),
                array($a_obj_id, 'cmi.core.lesson_status', $a_user_id)
            );

            $cnt = 0;
            $completed = true;
            $failed = false;
            while ($rec = $ilDB->fetchAssoc($res)) {
                if ($rec["rvalue"] === "failed") {
                    $failed = true;
                }
                if ($rec["rvalue"] !== "completed" && $rec["rvalue"] !== "passed") {
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

    public static function _countCompleted(?array $a_scos, int $a_obj_id, int $a_user_id): int
    {
        global $DIC;
        $ilDB = $DIC->database();
        $cnt = 0;

        if (is_array($a_scos)) {
            $in = $ilDB->in('sco_id', $a_scos, false, 'integer');

            $res = $ilDB->queryF(
                'SELECT sco_id, rvalue FROM scorm_tracking 
			WHERE ' . $in . '
			AND obj_id = %s
			AND lvalue = %s
			AND user_id = %s',
                array('integer', 'text', 'integer'),
                array($a_obj_id, 'cmi.core.lesson_status', $a_user_id)
            );

            while ($rec = $ilDB->fetchAssoc($res)) {
                if ($rec["rvalue"] === "completed" || $rec["rvalue"] === "passed") {
                    $cnt++;
                }
            }
        }
        return $cnt;
    }

    /**
     * Lookup last acccess time for all users of a scorm module
     * @return array<int|string, mixed>
     */
    public static function lookupLastAccessTimes(int $a_obj_id): array
    {
        global $DIC;
        $ilDB = $DIC->database();
        $users = array();

        $query = 'SELECT user_id, MAX(c_timestamp) tst ' .
            'FROM scorm_tracking ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer') . ' ' .
            'GROUP BY user_id';
        $res = $ilDB->query($query);

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $users[$row->user_id] = $row->tst;
        }
        return $users;
    }

    /**
     * Get all tracked users
     * @return mixed[]
     */
    public static function _getTrackedUsers(int $a_obj_id): array
    {
        global $DIC;
        $ilDB = $DIC->database();
//        $ilLog = ilLoggerFactory::getLogger('sahs');

        $res = $ilDB->queryF(
            'SELECT DISTINCT user_id FROM scorm_tracking 
			WHERE obj_id = %s
			AND lvalue = %s',
            array('integer', 'text'),
            array($a_obj_id, 'cmi.core.lesson_status')
        );

        $users = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $users[] = $row["user_id"];
        }
        return $users;
    }

    /**
     * like necessary because of Oracle
     * @return mixed[]
     */
    public static function _getFailed(object $scorm_item_id, int $a_obj_id): array
    {
        global $DIC;
        $ilDB = $DIC->database();
        $user_ids = [];

        if (is_array($scorm_item_id)) {
            $in = $ilDB->in('sco_id', $scorm_item_id, false, 'integer');

            $res = $ilDB->queryF(
                '
				SELECT DISTINCT(user_id) FROM scorm_tracking 
				WHERE ' . $in . '
				AND obj_id = %s
				AND lvalue =  %s
				AND ' . $ilDB->like('rvalue', 'clob', 'failed') . ' ',
                array('integer', 'text'),
                array($a_obj_id, 'cmi.core.lesson_status')
            );
        } else {
            $res = $ilDB->queryF(
                '
				SELECT DISTINCT(user_id) FROM scorm_tracking 
				WHERE sco_id = %s
				AND obj_id = %s
				AND lvalue =  %s
				AND ' . $ilDB->like('rvalue', 'clob', 'failed') . ' ',
                array('integer', 'integer', 'text'),
                array($scorm_item_id, $a_obj_id, 'cmi.core.lesson_status')
            );
        }

        while ($row = $ilDB->fetchObject($res)) {
            $user_ids[] = $row->user_id;
        }
        return $user_ids;
    }

    /**
     * Get users who have status completed or passed.
     * @return array<int|string, mixed>
     */
    public static function _getCountCompletedPerUser(array $a_scorm_item_ids, int $a_obj_id): array
    {
        global $DIC;
        $ilDB = $DIC->database();
        $users = [];

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
        return $users;
    }
    //not correct because of assets!
    /**
     * Get info about
     * @return array<string, mixed[]>
     */
    public static function _getProgressInfo(array $sco_item_ids, int $a_obj_id): array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $in = $ilDB->in('sco_id', $sco_item_ids, false, 'integer');

        $res = $ilDB->queryF(
            '
		SELECT * FROM scorm_tracking 
		WHERE ' . $in . '
		AND obj_id = %s 
		AND lvalue = %s ',
            array('integer', 'text'),
            array($a_obj_id, 'cmi.core.lesson_status')
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

    /**
     * @param array|int  $scorm_item_id
     * @return array<int|string, mixed[]>
     */
    public static function _getInProgress($scorm_item_id, int $a_obj_id, ?array $a_blocked_user_ids = null): array
    {
        global $DIC;
        $ilDB = $DIC->database();

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
                array('integer', 'integer'),
                array($scorm_item_id, $a_obj_id)
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

    public static function scorm12PlayerUnload(): void
    {
        global $DIC;
        $ilDB = $DIC->database();
        $user_id = $DIC->http()->wrapper()->query()->retrieve('p', $DIC->refinery()->kindlyTo()->int());
        $ref_id = $DIC->http()->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int());
        $obj_id = $DIC->http()->wrapper()->query()->retrieve('package_id', $DIC->refinery()->kindlyTo()->int());
        if ($obj_id <= 1) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ' no valid obj_id');
        } else {
            $last_visited = "";
            if ($DIC->http()->wrapper()->query()->has('last_visited')) {
                $last_visited = $DIC->http()->wrapper()->query()->retrieve('last_visited', $DIC->refinery()->kindlyTo()->string());
            }

            $endDate = date(
                'Y-m-d H:i:s',
                mktime((int) date('H'), (int) date('i') + 5, (int) date('s'), (int) date('m'), (int) date('d'), (int) date('Y'))
            );
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

    public static function checkIfAllowed(int $packageId, int $userId, int $hash): void
    {
        global $DIC;
        $ilDB = $DIC->database();
        $res = $ilDB->queryF(
            'select hash from sahs_user where obj_id=%s AND user_id=%s AND hash_end>%s',
            array('integer', 'integer', 'timestamp'),
            array($packageId, $userId, date('Y-m-d H:i:s'))
        );
        $rowtmp = $ilDB->fetchAssoc($res);
        if ($rowtmp['hash'] == $hash) {
            //ok - do nothing
//            die("allowed");
        } else {
            //output used by api
            die("not allowed");
        }
    }

    public static function _syncReadEvent(int $a_obj_id, int $a_user_id, string $a_type, int $a_ref_id): void
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilLog = ilLoggerFactory::getLogger('sahs');
        $val_set = $ilDB->queryF(
            'SELECT package_attempts, total_time_sec, sco_total_time_sec, time_from_lms FROM sahs_user, sahs_lm '
            . 'WHERE sahs_user.obj_id = %s AND sahs_user.user_id = %s AND sahs_user.obj_id = sahs_lm.id',
            array('integer', 'integer'),
            array($a_obj_id, $a_user_id)
        );

        $val_rec = $ilDB->fetchAssoc($val_set);

        if ($val_rec["package_attempts"] == null) {
            $val_rec["package_attempts"] = "";
        }
        $attempts = $val_rec["package_attempts"];

        $time = (int) $val_rec["sco_total_time_sec"];

        // get learning time for old ILIAS-Versions
        if ($time == 0) {
            $sco_set = $ilDB->queryF(
                '
			SELECT sco_id, rvalue FROM scorm_tracking 
			WHERE obj_id = %s
			AND user_id = %s
			AND lvalue = %s
			AND sco_id <> %s',
                array('integer', 'integer', 'text', 'integer'),
                array($a_obj_id, $a_user_id, 'cmi.core.total_time', 0)
            );

            while ($sco_rec = $ilDB->fetchAssoc($sco_set)) {
                $tarr = explode(":", $sco_rec["rvalue"]);
                $sec = (int) $tarr[2] + (int) $tarr[1] * 60 +
                    (int) substr($tarr[0], strlen($tarr[0]) - 3) * 60 * 60;
                $time += $sec;
            }
        }
        ilChangeEvent::_recordReadEvent($a_type, $a_ref_id, $a_obj_id, $a_user_id, false, $attempts, $time);
    }
} // END class.ilObjSCORMTracking
