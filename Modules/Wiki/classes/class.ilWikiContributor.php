<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilWikiContributor
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilWikiContributor
{
    const STATUS_NOT_GRADED = 0;
    const STATUS_PASSED = 1;
    const STATUS_FAILED = 2;
    
    /**
    * Lookup current success status (STATUS_NOT_GRADED|STATUS_PASSED|STATUS_FAILED)
    *
    * @param	int		$a_obj_id	exercise id
    * @param	int		$a_user_id	member id
    * @return	mixed	false (if user is no member) or notgraded|passed|failed
    */
    public static function _lookupStatus($a_obj_id, $a_user_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->queryF(
            "SELECT status FROM il_wiki_contributor " .
            "WHERE wiki_id = %s and user_id = %s",
            array("integer", "integer"),
            array($a_obj_id, $a_user_id)
        );
        if ($row = $ilDB->fetchAssoc($set)) {
            return $row["status"];
        }
        return false;
    }

    /**
    * Lookup last change in mark or success status
    *
    * @param	int		$a_obj_id	exercise id
    * @param	int		$a_user_id	member id
    * @return	mixed	false (if user is no member) or notgraded|passed|failed
    */
    public static function _lookupStatusTime($a_obj_id, $a_user_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->queryF(
            "SELECT status_time FROM il_wiki_contributor " .
            "WHERE wiki_id = %s and user_id = %s",
            array("integer", "integer"),
            array($a_obj_id, $a_user_id)
        );
        if ($row = $ilDB->fetchAssoc($set)) {
            return $row["status_time"];
        }
        return false;
    }

    /**
    * Write success status
    *
    * @param	int		$a_obj_id		exercise id
    * @param	int		$a_user_id		member id
    * @param	int		$status			status: STATUS_NOT_GRADED|STATUS_PASSED|STATUS_FAILED
    *
    * @return	int		number of affected rows
    */
    public static function _writeStatus($a_obj_id, $a_user_id, $a_status)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate("DELETE FROM il_wiki_contributor WHERE " .
            " wiki_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer"));

        $ilDB->manipulateF(
            "INSERT INTO il_wiki_contributor (status, wiki_id, user_id, status_time) " .
            "VALUES (%s,%s,%s,%s)",
            array("integer", "integer", "integer", "timestamp"),
            array($a_status, $a_obj_id, $a_user_id, ilUtil::now())
        );
    }
}
