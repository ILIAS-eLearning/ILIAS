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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiContributor
{
    public const STATUS_NOT_GRADED = 0;
    public const STATUS_PASSED = 1;
    public const STATUS_FAILED = 2;
    
    /**
     * Lookup current success status (STATUS_NOT_GRADED|STATUS_PASSED|STATUS_FAILED)
     * @return ?int (if user is no member) or notgraded|passed|failed
     */
    public static function _lookupStatus(
        int $a_obj_id,
        int $a_user_id
    ) : ?int {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->queryF(
            "SELECT status FROM il_wiki_contributor " .
            "WHERE wiki_id = %s and user_id = %s",
            array("integer", "integer"),
            array($a_obj_id, $a_user_id)
        );
        if ($row = $ilDB->fetchAssoc($set)) {
            return (int) $row["status"];
        }
        return null;
    }

    public static function _lookupStatusTime(
        int $a_obj_id,
        int $a_user_id
    ) : ?string {
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
        return null;
    }

    /**
     * @param int $status status: STATUS_NOT_GRADED|STATUS_PASSED|STATUS_FAILED
     */
    public static function _writeStatus(
        int $a_obj_id,
        int $a_user_id,
        int $a_status
    ) : void {
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
