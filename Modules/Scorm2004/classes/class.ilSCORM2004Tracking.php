<?php declare(strict_types=1);

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
 * Class ilSCORM2004Tracking
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSCORM2004Tracking
{
    /**
     * @throws Exception
     */
    public static function _getInProgress(int $scorm_item_id, int $a_obj_id) : void
    {
        throw new Exception("Not Implemented: ilSCORM2004Tracking_getInProgress");
    }

    /**
     * @throws Exception
     */
    public static function _getCompleted(int $scorm_item_id, int $a_obj_id) : void
    {
        throw new Exception("Not Implemented: ilSCORM2004Tracking_getCompleted");
    }

    /**
     * @throws Exception
     */
    public static function _getFailed(int $scorm_item_id, int $a_obj_id) : void
    {
        throw new Exception("Not Implemented: ilSCORM2004Tracking_getFailed");
    }

    /**
     * Get progress of selected scos
     * @param bool  $a_omit_failed do not include success==failed
     * @return array<int|string, mixed>
     */
    public static function _getCountCompletedPerUser(array $a_scorm_item_ids, int $a_obj_id, bool $a_omit_failed = false) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $users = [];

        $in = $ilDB->in('cp_node.cp_node_id', $a_scorm_item_ids, false, 'integer');

        // #8171: success_status vs. completion status
        $omit_failed = '';
        if ($a_omit_failed) {
            $omit_failed = ' AND success_status <> ' . $ilDB->quote('failed', 'text');
        }

        $res = $ilDB->queryF(
            '
			SELECT cmi_node.user_id user_id, COUNT(user_id) completed FROM cp_node, cmi_node 
			WHERE ' . $in . $omit_failed . '
			AND cp_node.cp_node_id = cmi_node.cp_node_id
			AND cp_node.slm_id = %s
			AND completion_status = %s 
		 	GROUP BY cmi_node.user_id',
            array('integer', 'text'),
            array($a_obj_id, 'completed')
        );
        while ($row = $ilDB->fetchObject($res)) {
            $users[$row->user_id] = $row->completed;
        }

        return $users;
    }

    /**
     * Get overall scorm status
     * @return array<string, int[]>
     */
    public static function _getProgressInfo(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            '
			SELECT user_id, status, satisfied FROM cmi_gobjective
			WHERE objective_id = %s
			AND scope_id = %s',
            array('text', 'integer'),
            array('-course_overall_status-', $a_obj_id)
        );

        $info['completed'] = array();
        $info['failed'] = array();
        $info['in_progress'] = array();

        while ($row = $ilDB->fetchAssoc($res)) {
            if (self::_isCompleted($row["status"], $row["satisfied"])) {
                $info['completed'][] = (int) $row["user_id"];
            }
            if (self::_isInProgress($row["status"], $row["satisfied"])) {
                $info['in_progress'][] = (int) $row["user_id"];
            }
            if (self::_isFailed($row["status"], $row["satisfied"])) {
                $info['failed'][] = (int) $row["user_id"];
            }
        }

        return $info;
    }

    /**
     * Get overall scorm status
     */
    public static function _getProgressInfoOfUser(int $a_obj_id, int $a_user_id) : string
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilLog = $DIC["ilLog"];

        $res = $ilDB->queryF(
            '
			SELECT status, satisfied FROM cmi_gobjective
			WHERE objective_id = %s
			AND scope_id = %s AND user_id = %s',
            array('text', 'integer', 'integer'),
            array('-course_overall_status-', $a_obj_id, $a_user_id)
        );

        $status = "not_attempted";
        if ($row = $ilDB->fetchAssoc($res)) {
            if (self::_isInProgress($row["status"], $row["satisfied"])) {
                $status = "in_progress";
            }
            if (self::_isCompleted($row["status"], $row["satisfied"])) {
                $status = "completed";
            }
            if (self::_isFailed($row["status"], $row["satisfied"])) {
                $status = "failed";
            }
        }
        return $status;
    }

    /**
     * Get all tracked users
     * @return int[]
     */
    public static function _getTrackedUsers(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilLog = $DIC["ilLog"];

        $res = $ilDB->queryF(
            '
			SELECT DISTINCT user_id FROM cmi_gobjective
			WHERE objective_id = %s
			AND scope_id = %s',
            array('text', 'integer'),
            array('-course_overall_status-', $a_obj_id)
        );

        $users = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $users[] = (int) $row["user_id"];
        }
        return $users;
    }

    /**
     * @return array<string, array<int|string, mixed[]>>
     */
    public static function _getItemProgressInfo(array $a_scorm_item_ids, int $a_obj_id, bool $a_omit_failed) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $in = $ilDB->in('cp_node.cp_node_id', $a_scorm_item_ids, false, 'integer');

        $res = $ilDB->queryF(
            'SELECT cp_node.cp_node_id id, 
					cmi_node.user_id user_id,
					cmi_node.completion_status completion, 
					cmi_node.success_status success
			 FROM cp_node, cmi_node 
			 WHERE ' . $in . '
			 AND cp_node.cp_node_id = cmi_node.cp_node_id
			 AND cp_node.slm_id = %s',
            array('integer'),
            array($a_obj_id)
        );

        $info['completed'] = array();
        $info['failed'] = array();
        $info['in_progress'] = array();

        while ($row = $ilDB->fetchAssoc($res)) {
            // if any data available, set in progress.
            $info['in_progress'][$row["id"]][] = $row["user_id"];
            if ($row["completion"] === "completed" || $row["success"] === "passed") {
                // #8171: success_status vs. completion status
                if (!$a_omit_failed || $row["success"] !== "failed") {
                    $info['completed'][$row["id"]][] = $row["user_id"];
                }
            }
            if ($row["success"] === "failed") {
                $info['failed'][$row["id"]][] = $row["user_id"];
            }
        }
        return $info;
    }

    public static function _getCollectionStatus(array $a_scos, int $a_obj_id, int $a_user_id) : string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $status = "not_attempted";

        if (is_array($a_scos)) {
            $in = $ilDB->in('cp_node.cp_node_id', $a_scos, false, 'integer');

            $res = $ilDB->queryF(
                'SELECT cp_node.cp_node_id id,
						cmi_node.completion_status completion, 
						cmi_node.success_status success
				 FROM cp_node, cmi_node 
				 WHERE ' . $in . '
				 AND cp_node.cp_node_id = cmi_node.cp_node_id
				 AND cp_node.slm_id = %s
				AND cmi_node.user_id = %s',
                array('integer', 'integer'),
                array($a_obj_id, $a_user_id)
            );

            $started = false;
            $cntcompleted = 0;
            $failed = false;
            while ($rec = $ilDB->fetchAssoc($res)) {
                if ($rec["completion"] === "completed" || $rec["success"] === "passed") {
                    $cntcompleted++;
                }
                if ($rec["success"] === "failed") {
                    $failed = true;
                }
                $started = true;
            }
            if ($started == true) {
                $status = "in_progress";
            }
            if ($failed == true) {
                $status = "failed";
            } elseif ($cntcompleted == count($a_scos)) {
                $status = "completed";
            }
        }
        return $status;
    }

    public static function _countCompleted(
        array $a_scos,
        int $a_obj_id,
        int $a_user_id,
        bool $a_omit_failed
    ) : int {
        global $DIC;

        $ilDB = $DIC->database();

        if (is_array($a_scos)) {
            $in = $ilDB->in('cp_node.cp_node_id', $a_scos, false, 'integer');

            $res = $ilDB->queryF(
                'SELECT cp_node.cp_node_id id,
						cmi_node.completion_status completion, 
						cmi_node.success_status success
				 FROM cp_node, cmi_node 
				 WHERE ' . $in . '
				 AND cp_node.cp_node_id = cmi_node.cp_node_id
				 AND cp_node.slm_id = %s
				AND cmi_node.user_id = %s',
                array('integer', 'integer'),
                array($a_obj_id, $a_user_id)
            );


            $cnt = 0;
            while ($rec = $ilDB->fetchAssoc($res)) {
                // #8171: alex, added (!$a_omit_failed || $rec["success"] != "failed")
                // since completed/failed combination should not be included in
                // percentage calculation at ilLPStatusSCOM::determinePercentage
                if (($rec["completion"] === "completed" || $rec["success"] === "passed")
                    && (!$a_omit_failed || $rec["success"] !== "failed")) {
                    $cnt++;
                }
            }
        }
        return $cnt;
    }

    /**
     * Synch read event table
     */
    public static function _syncReadEvent(int $a_obj_id, int $a_user_id, string $a_type, int $a_ref_id, ?bool $time_from_lms = null) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        //get condition to select time
        $val_set = $ilDB->queryF(
            'SELECT time_from_lms FROM sahs_lm WHERE id = %s',
            array('integer'),
            array($a_obj_id)
        );
        $val_rec = $ilDB->fetchAssoc($val_set);
        $time_from_lms = (ilUtil::yn2tf($val_rec["time_from_lms"]));

        // get attempts and time
        $val_set = $ilDB->queryF(
            '
			SELECT package_attempts, sco_total_time_sec, total_time_sec 
			FROM sahs_user WHERE obj_id = %s AND user_id = %s',
            array('integer','integer'),
            array($a_obj_id,$a_user_id)
        );
        $val_rec = $ilDB->fetchAssoc($val_set);
        if ($time_from_lms == false) {
            $time = $val_rec["sco_total_time_sec"];
        } else {
            $time = $val_rec["total_time_sec"];
        }
        $attempts = $val_rec["package_attempts"];
        if ($attempts == null) {
            $attempts = "";
        } //??

        if ($attempts != "" && $time == null) { //use old way
            $time = self::getSumTotalTimeSecondsFromScos($a_obj_id, $a_user_id, true);
        }

        ilChangeEvent::_recordReadEvent(
            $a_type,
            $a_ref_id,
            $a_obj_id,
            $a_user_id,
            false,
            $attempts,
            $time
        );
    }

    public static function _isCompleted(string $a_status, string $a_satisfied) : bool
    {
        return $a_status === "completed" || $a_satisfied === "satisfied";
    }

    public static function _isInProgress(string $a_status, string $a_satisfied) : bool
    {
        return $a_status !== "completed";
    }

    public static function _isFailed(string $a_status, string $a_satisfied) : bool
    {
        return $a_status === "completed" && $a_satisfied === "notSatisfied";
    }

    /**
     * should be avoided; store value to increase performance for further requests
     */
    public static function getSumTotalTimeSecondsFromScos(int $a_obj_id, int $a_user_id, bool $a_write = false) : int
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilLog = $DIC["ilLog"];
        $scos = array();
        $val_set = $ilDB->queryF(
            'SELECT cp_node_id FROM cp_node 
			WHERE nodename = %s
			AND cp_node.slm_id = %s',
            array('text', 'integer'),
            array('item', $a_obj_id)
        );
        while ($val_rec = $ilDB->fetchAssoc($val_set)) {
            $scos[] = $val_rec['cp_node_id'];
        }
        $time = 0;
        foreach ($scos as $sco) {
            $sec = 0;
            $data_set = $ilDB->queryF(
                '
				SELECT total_time
				FROM cmi_node 
				WHERE cp_node_id = %s
				AND user_id = %s',
                array('integer','integer'),
                array($sco, $a_user_id)
            );

            while ($data_rec = $ilDB->fetchAssoc($data_set)) {
                $sec = ilObjSCORM2004LearningModule::_ISODurationToCentisec($data_rec["total_time"]) / 100;
            }
            $time += (int) $sec;
            $sec = 0;
            //$ilLog->write("++".$time);
        }
        if ($a_write && $time > 0) {
            $ilDB->queryF(
                'UPDATE sahs_user SET sco_total_time_sec=%s WHERE obj_id = %s AND user_id = %s',
                array('integer', 'integer', 'integer'),
                array($time, $a_obj_id, $a_user_id)
            );
        }
        return $time;
    }
}
// END class.ilSCORM2004Tracking
