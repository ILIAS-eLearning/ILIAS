<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Tracking/classes/class.ilLPStatus.php';

/**
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup	ServicesTracking
*
*/
class ilLPStatusTestFinished extends ilLPStatus
{
    public function __construct($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        parent::__construct($a_obj_id);
        $this->db = $ilDB;
    }

    public static function _getInProgress($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        include_once './Modules/Test/classes/class.ilObjTestAccess.php';

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
            $user_ids[$row->user_fi] = $row->user_fi;
        }

        return array_values($user_ids);
    }


    public static function _getCompleted($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        include_once './Modules/Test/classes/class.ilObjTestAccess.php';

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
            $user_ids[$row->user_fi] = $row->user_fi;
        }

        return array_values($user_ids);
    }

    public static function _getNotAttempted($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        include_once './Modules/Test/classes/class.ilObjTestAccess.php';

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
            $user_ids[$row->user_fi] = $row->user_fi;
        }

        return array_values($user_ids);
    }

    /**
     * Get participants
     *
     * @param
     * @return
     */
    public static function getParticipants($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        include_once './Modules/Test/classes/class.ilObjTestAccess.php';

        $res = $ilDB->query("SELECT DISTINCT user_fi FROM tst_active" .
            " WHERE test_fi = " . $ilDB->quote(ilObjTestAccess::_getTestIDFromObjectID($a_obj_id)));
        $user_ids = array();

        while ($rec = $ilDB->fetchAssoc($res)) {
            $user_ids[] = $rec["user_fi"];
        }
        return $user_ids;
    }

    
    /**
     * Determine status
     *
     * @param	integer		object id
     * @param	integer		user id
     * @param	object		object (optional depends on object type)
     * @return	integer		status
     */
    public function determineStatus($a_obj_id, $a_user_id, $a_obj = null)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        include_once './Modules/Test/classes/class.ilObjTestAccess.php';

        $res = $ilDB->query("
			SELECT active_id, user_fi, tries, COUNT(tst_sequence.active_fi) sequences
			FROM tst_active
			LEFT JOIN tst_sequence
			ON tst_sequence.active_fi = tst_active.active_id
			WHERE user_fi = {$ilDB->quote($a_user_id, "integer")}
			AND test_fi = {$ilDB->quote(ilObjTestAccess::_getTestIDFromObjectID($a_obj_id))}
			GROUP BY active_id, user_fi, tries
		");

        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;

        if ($rec = $ilDB->fetchAssoc($res)) {
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
