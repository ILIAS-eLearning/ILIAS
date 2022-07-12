<?php declare(strict_types=0);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author     Stefan Meyer <meyer@leifos.com>
 * @ingroup    ServicesTracking
 */
class ilLPStatusTestFinished extends ilLPStatus
{
    public static function _getInProgress(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "
			SELECT active_id, user_fi, COUNT(tst_sequence.active_fi) sequences
			FROM tst_active
			LEFT JOIN tst_sequence
			ON tst_sequence.active_fi = tst_active.active_id
			WHERE tries = {$ilDB->quote(0, "integer")}
			AND test_fi = {$ilDB->quote(ilObjTestAccess::_getTestIDFromObjectID($a_obj_id), "integer")}
			GROUP BY active_id, user_fi
			HAVING COUNT(tst_sequence.active_fi) > {$ilDB->quote(0, "integer")}
		";

        $res = $ilDB->query($query);

        $user_ids = array();

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $user_ids[$row->user_fi] = (int) $row->user_fi;
        }
        return array_values($user_ids);
    }

    public static function _getCompleted(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "
			SELECT active_id, user_fi, COUNT(tst_sequence.active_fi) sequences
			FROM tst_active
			LEFT JOIN tst_sequence
			ON tst_sequence.active_fi = tst_active.active_id
			WHERE tries > {$ilDB->quote(0, "integer")}
			AND test_fi = {$ilDB->quote(ilObjTestAccess::_getTestIDFromObjectID($a_obj_id))}
			GROUP BY active_id, user_fi
			HAVING COUNT(tst_sequence.active_fi) > {$ilDB->quote(0, "integer")}
		";

        $res = $ilDB->query($query);

        $user_ids = array();

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $user_ids[$row->user_fi] = (int) $row->user_fi;
        }
        return array_values($user_ids);
    }

    public static function _getNotAttempted(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "
			SELECT active_id, user_fi, COUNT(tst_sequence.active_fi) sequences
			FROM tst_active
			LEFT JOIN tst_sequence
			ON tst_sequence.active_fi = tst_active.active_id
			WHERE test_fi = {$ilDB->quote(ilObjTestAccess::_getTestIDFromObjectID($a_obj_id))}
			GROUP BY active_id, user_fi
			HAVING COUNT(tst_sequence.active_fi) = {$ilDB->quote(0, "integer")}
		";

        $res = $ilDB->query($query);

        $user_ids = array();

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $user_ids[$row->user_fi] = (int) $row->user_fi;
        }

        return array_values($user_ids);
    }

    public static function getParticipants($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $res = $ilDB->query(
            "SELECT DISTINCT user_fi FROM tst_active" .
            " WHERE test_fi = " . $ilDB->quote(
                ilObjTestAccess::_getTestIDFromObjectID($a_obj_id)
            )
        );
        $user_ids = array();

        while ($rec = $ilDB->fetchAssoc($res)) {
            $user_ids[] = (int) $rec["user_fi"];
        }
        return $user_ids;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        object $a_obj = null
    ) : int {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $res = $this->db->query(
            "
			SELECT active_id, user_fi, tries, COUNT(tst_sequence.active_fi) sequences
			FROM tst_active
			LEFT JOIN tst_sequence
			ON tst_sequence.active_fi = tst_active.active_id
			WHERE user_fi = {$this->db->quote($a_usr_id, "integer")}
			AND test_fi = {$this->db->quote(ilObjTestAccess::_getTestIDFromObjectID($a_obj_id), ilDBConstants::T_INTEGER)}
			GROUP BY active_id, user_fi, tries
		"
        );

        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;

        if ($rec = $this->db->fetchAssoc($res)) {
            if ($rec['sequences'] > 0) {
                $status = self::LP_STATUS_IN_PROGRESS_NUM;

                if ($rec['tries'] > 0) {
                    $status = self::LP_STATUS_COMPLETED_NUM;
                }
            }
        }
        return $status;
    }
}
