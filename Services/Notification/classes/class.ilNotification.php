<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilNotification
{
    public const THRESHOLD = 180; // time between mails in minutes
    public const TYPE_EXERCISE_SUBMISSION = 1;
    public const TYPE_WIKI = 2;
    public const TYPE_WIKI_PAGE = 3;
    public const TYPE_BLOG = 4;
    public const TYPE_DATA_COLLECTION = 5;
    public const TYPE_POLL = 6;
    public const TYPE_LM_BLOCKED_USERS = 7;
    public const TYPE_BOOK = 8;
    public const TYPE_LM = 9;
    public const TYPE_LM_PAGE = 10;

    /**
     * Check notification status for object and user
     */
    public static function hasNotification(
        int $type,
        int $user_id,
        int $id
    ): bool {
        global $DIC;

        $ilDB = $DIC->database();
        $tree = $DIC->repositoryTree();

        $notification = false;

        $setting = new ilObjNotificationSettings($id);
        if ($setting->getMode() !== ilObjNotificationSettings::MODE_DEF_OFF_USER_ACTIVATION) {
            // check membership, members should be notidifed...
            foreach (ilObject::_getAllReferences($id) as $ref_id) {
                $grp_ref_id = $tree->checkForParentType($ref_id, 'grp');
                if (($grp_ref_id > 0) && ilGroupParticipants::_isParticipant($grp_ref_id, $user_id)) {
                    $notification = true;
                }
                $crs_ref_id = $tree->checkForParentType($ref_id, 'crs');
                if (($crs_ref_id > 0) && ilCourseParticipants::_isParticipant($crs_ref_id, $user_id)) {
                    $notification = true;
                }
            }

            if ($notification && $setting->getMode() === ilObjNotificationSettings::MODE_DEF_ON_OPT_OUT) {
                $set = $ilDB->query("SELECT user_id FROM notification" .
                    " WHERE type = " . $ilDB->quote($type, "integer") .
                    " AND user_id = " . $ilDB->quote($user_id, "integer") .
                    " AND id = " . $ilDB->quote($id, "integer") .
                    " AND activated = " . $ilDB->quote(0, "integer"));
                $rec = $ilDB->fetchAssoc($set);
                // if there is no user record, take the default value
                if (!isset($rec["user_id"])) {
                    return $notification;
                }
                // ... except when the opted out
                return isset($rec["user_id"]) && ((int) $rec["user_id"] !== $user_id);
            }

            if ($notification && $setting->getMode() === ilObjNotificationSettings::MODE_DEF_ON_NO_OPT_OUT) {
                return true;
            }
        }


        $set = $ilDB->query("SELECT user_id FROM notification" .
            " WHERE type = " . $ilDB->quote($type, "integer") .
            " AND user_id = " . $ilDB->quote($user_id, "integer") .
            " AND id = " . $ilDB->quote($id, "integer") .
            " AND activated = " . $ilDB->quote(1, "integer"));

        return (bool) $ilDB->numRows($set);
    }

    /**
     * Is opt out (disable notification) allowed?
     */
    public static function hasOptOut(int $obj_id): bool
    {
        $setting = new ilObjNotificationSettings($obj_id);
        return $setting->getMode() !== ilObjNotificationSettings::MODE_DEF_ON_NO_OPT_OUT;
    }

    /**
     * Get all users/recipients for given object
     */
    public static function getNotificationsForObject(
        int $type,
        int $id,
        ?int $page_id = null,
        bool $ignore_threshold = false
    ): array {
        global $DIC;

        $ilDB = $DIC->database();
        $tree = $DIC->repositoryTree();

        // currently done for blog
        $recipients = array();
        $setting = new ilObjNotificationSettings($id);
        if ($setting->getMode() !== ilObjNotificationSettings::MODE_DEF_OFF_USER_ACTIVATION) {
            foreach (ilObject::_getAllReferences($id) as $ref_id) {
                $grp_ref_id = $tree->checkForParentType($ref_id, 'grp');
                if ($grp_ref_id > 0) {
                    $p = ilGroupParticipants::_getInstanceByObjId(ilObject::_lookupObjectId($grp_ref_id));
                    foreach ($p->getMembers() as $user_id) {
                        if (!in_array($user_id, $recipients)) {
                            $recipients[$user_id] = $user_id;
                        }
                    }
                }
                $crs_ref_id = $tree->checkForParentType($ref_id, 'crs');
                if ($crs_ref_id > 0) {
                    $p = ilCourseParticipants::_getInstanceByObjId(ilObject::_lookupObjectId($crs_ref_id));
                    foreach ($p->getMembers() as $user_id) {
                        if (!in_array($user_id, $recipients)) {
                            $recipients[$user_id] = $user_id;
                        }
                    }
                }
            }
        }

        // remove all users that deactivated the feature
        if ($setting->getMode() === ilObjNotificationSettings::MODE_DEF_ON_OPT_OUT) {
            $sql = "SELECT user_id FROM notification" .
                " WHERE type = " . $ilDB->quote($type, "integer") .
                " AND id = " . $ilDB->quote($id, "integer") .
                " AND activated = " . $ilDB->quote(0, "integer") .
                " AND " . $ilDB->in("user_id", $recipients, false, "integer");
            $set = $ilDB->query($sql);
            while ($rec = $ilDB->fetchAssoc($set)) {
                unset($recipients[$rec["user_id"]]);
            }
        }

        // remove all users that got a mail
        if ($setting->getMode() !== ilObjNotificationSettings::MODE_DEF_OFF_USER_ACTIVATION && !$ignore_threshold) {
            $sql = "SELECT user_id FROM notification" .
                " WHERE type = " . $ilDB->quote($type, "integer") .
                " AND id = " . $ilDB->quote($id, "integer") .
                " AND activated = " . $ilDB->quote(1, "integer") .
                " AND " . $ilDB->in("user_id", $recipients, false, "integer");
            $sql .= " AND (last_mail > " . $ilDB->quote(date(
                "Y-m-d H:i:s",
                strtotime("-" . self::THRESHOLD . "minutes")
            ), "timestamp");
            if ($page_id) {
                $sql .= " AND page_id = " . $ilDB->quote($page_id, "integer");
            }
            $sql .= ")";

            $set = $ilDB->query($sql);
            while ($rec = $ilDB->fetchAssoc($set)) {
                unset($recipients[$rec["user_id"]]);
            }
        }

        // get single subscriptions
        if ($setting->getMode() !== ilObjNotificationSettings::MODE_DEF_ON_NO_OPT_OUT) {
            $sql = "SELECT user_id FROM notification" .
                " WHERE type = " . $ilDB->quote($type, "integer") .
                " AND id = " . $ilDB->quote($id, "integer") .
                " AND activated = " . $ilDB->quote(1, "integer");
            if (!$ignore_threshold) {
                $sql .= " AND (last_mail < " . $ilDB->quote(date(
                    "Y-m-d H:i:s",
                    strtotime("-" . self::THRESHOLD . "minutes")
                ), "timestamp") .
                    " OR last_mail IS NULL";
                if ($page_id) {
                    $sql .= " OR page_id <> " . $ilDB->quote($page_id, "integer");
                }
                $sql .= ")";
            }
            $set = $ilDB->query($sql);
            while ($row = $ilDB->fetchAssoc($set)) {
                $recipients[$row["user_id"]] = $row["user_id"];
            }
        }

        return $recipients;
    }

    /**
     * Set notification status for object and user
     */
    public static function setNotification(
        int $type,
        int $user_id,
        int $id,
        bool $status = true
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $fields = array(
            "type" => array("integer", $type),
            "user_id" => array("integer", $user_id),
            "id" => array("integer", $id)
        );
        $ilDB->replace("notification", $fields, array("activated" => array("integer", (int) $status)));
    }

    /**
     * Update the last mail timestamp for given object and users
     */
    public static function updateNotificationTime(
        int $type,
        int $id,
        array $user_ids,
        ?int $page_id = null
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $sql = "UPDATE notification" .
                " SET last_mail = " . $ilDB->quote(date("Y-m-d H:i:s"), "timestamp");

        if ($page_id) {
            $sql .= ", page_id = " . $ilDB->quote($page_id, "integer");
        }

        $sql .= " WHERE type = " . $ilDB->quote($type, "integer") .
                " AND id = " . $ilDB->quote($id, "integer") .
                " AND " . $ilDB->in("user_id", $user_ids, false, "integer");

        $ilDB->query($sql);
    }

    /**
     * Remove all notifications for given object
     */
    public static function removeForObject(
        int $type,
        int $id
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->query("DELETE FROM notification" .
                " WHERE type = " . $ilDB->quote($type, "integer") .
                " AND id = " . $ilDB->quote($id, "integer"));
    }

    /**
     * Remove all notifications for given user
     */
    public static function removeForUser(
        int $user_id
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->query("DELETE FROM notification" .
                " WHERE user_id = " . $ilDB->quote($user_id, "integer"));
    }

    /**
     * Get activated notifications of give type for user
     * @return int[]
     */
    public static function getActivatedNotifications(
        int $type,
        int $user_id
    ): array {
        global $DIC;

        $db = $DIC->database();

        $set = $db->queryF(
            "SELECT id FROM notification " .
            " WHERE type = %s " .
            " AND user_id = %s " .
            " AND activated = %s ",
            array("integer", "integer", "integer"),
            array($type, $user_id, 1)
        );
        $ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $ids[] = $rec["id"];
        }

        return $ids;
    }
}
