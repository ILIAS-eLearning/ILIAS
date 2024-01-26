<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilNotification
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjExerciseGUI.php 24003 2010-05-26 14:35:42Z akill $
*
* @ingroup ServicesNotification
*/
class ilNotification
{
    const TYPE_EXERCISE_SUBMISSION = 1;
    const TYPE_WIKI = 2;
    const TYPE_WIKI_PAGE = 3;
    const TYPE_BLOG = 4;
    const TYPE_DATA_COLLECTION = 5;
    const TYPE_POLL = 6;
    const TYPE_LM_BLOCKED_USERS = 7;
    const TYPE_BOOK = 8;
    const TYPE_LM = 9;
    const TYPE_LM_PAGE = 10;

    const THRESHOLD = 180; // time between mails in minutes

    /**
     * Check notification status for object and user
     *
     * @param	int		$type
     * @param	int		$user_id
     * @param	int		$id
     * @return	bool
     */
    public static function hasNotification($type, $user_id, $id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $tree = $DIC->repositoryTree();

        $notification = false;

        include_once("./Services/Notification/classes/class.ilObjNotificationSettings.php");
        $setting = new ilObjNotificationSettings($id);
        if ($setting->getMode() != ilObjNotificationSettings::MODE_DEF_OFF_USER_ACTIVATION) {
            // check membership, members should be notidifed...
            foreach (ilObject::_getAllReferences($id) as $ref_id) {
                $grp_ref_id = $tree->checkForParentType($ref_id, 'grp');
                if ($grp_ref_id > 0) {
                    include_once("./Modules/Group/classes/class.ilGroupParticipants.php");
                    if (ilGroupParticipants::_isParticipant($grp_ref_id, $user_id)) {
                        $notification = true;
                    }
                }
                $crs_ref_id = $tree->checkForParentType($ref_id, 'crs');
                if ($crs_ref_id > 0) {
                    include_once("./Modules/Course/classes/class.ilCourseParticipants.php");
                    if (ilCourseParticipants::_isParticipant($crs_ref_id, $user_id)) {
                        $notification = true;
                    }
                }
            }

            if ($notification && $setting->getMode() == ilObjNotificationSettings::MODE_DEF_ON_OPT_OUT) {
                $set = $ilDB->query("SELECT user_id FROM notification" .
                    " WHERE type = " . $ilDB->quote($type, "integer") .
                    " AND user_id = " . $ilDB->quote($user_id, "integer") .
                    " AND id = " . $ilDB->quote($id, "integer") .
                    " AND activated = " . $ilDB->quote(0, "integer"));
                $rec = $ilDB->fetchAssoc($set);
                // ... except when the opted out
                if ($rec["user_id"] == $user_id) {
                    return false;
                }
                return true;
            }

            if ($notification && $setting->getMode() == ilObjNotificationSettings::MODE_DEF_ON_NO_OPT_OUT) {
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
     *
     * @param	int		$obj_id
     * @return	bool
     */
    public static function hasOptOut($obj_id)
    {
        include_once("./Services/Notification/classes/class.ilObjNotificationSettings.php");
        $setting = new ilObjNotificationSettings($obj_id);
        if ($setting->getMode() == ilObjNotificationSettings::MODE_DEF_ON_NO_OPT_OUT) {
            return false;
        }
        return true;
    }

    /**
     * Get all users for given object
     *
     * @param	int		$type
     * @param	int		$id
     * @param	int		$page_id
     * @param	bool	$ignore_threshold
     * @return	array
     */
    public static function getNotificationsForObject($type, $id, $page_id = null, $ignore_threshold = false)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $tree = $DIC->repositoryTree();

        include_once("./Services/Notification/classes/class.ilObjNotificationSettings.php");

        $log = ilLoggerFactory::getLogger('noti');
        $log->debug("Get notifications for type " . $type . ", id " . $id . ", page_id " . $page_id .
            ", ignore threshold " . $ignore_threshold);

        // currently done for blog
        $recipients = array();
        $setting = new ilObjNotificationSettings($id);
        if ($setting->getMode() != ilObjNotificationSettings::MODE_DEF_OFF_USER_ACTIVATION) {
            foreach (ilObject::_getAllReferences($id) as $ref_id) {
                $grp_ref_id = $tree->checkForParentType($ref_id, 'grp');
                if ($grp_ref_id > 0) {
                    include_once("./Modules/Group/classes/class.ilGroupParticipants.php");
                    $p = ilGroupParticipants::_getInstanceByObjId(ilObject::_lookupObjectId($grp_ref_id));
                    foreach ($p->getMembers() as $user_id) {
                        if (!in_array($user_id, $recipients)) {
                            $recipients[$user_id] = $user_id;
                        }
                    }
                }
                $crs_ref_id = $tree->checkForParentType($ref_id, 'crs');
                if ($crs_ref_id > 0) {
                    include_once("./Modules/Course/classes/class.ilCourseParticipants.php");
                    $p = ilCourseParticipants::_getInstanceByObjId(ilObject::_lookupObjectId($crs_ref_id));
                    foreach ($p->getMembers() as $user_id) {
                        if (!in_array($user_id, $recipients)) {
                            $recipients[$user_id] = $user_id;
                        }
                    }
                }
            }
        }

        $log->debug("Step 1 recipients: " . print_r($recipients, true));


        // remove all users that deactivated the feature
        if ($setting->getMode() == ilObjNotificationSettings::MODE_DEF_ON_OPT_OUT) {
            $sql = "SELECT user_id FROM notification" .
                " WHERE type = " . $ilDB->quote($type, "integer") .
                " AND id = " . $ilDB->quote($id, "integer") .
                " AND activated = " . $ilDB->quote(0, "integer") .
                " AND " . $ilDB->in("user_id", $recipients, false, "integer");
            $set = $ilDB->query($sql);
            while ($rec = $ilDB->fetchAssoc($set)) {
                unset($recipients[$rec["user_id"]]);
                $log->debug("Remove due to deactivation: " . $rec["user_id"]);
            }
        }

        // remove all users that got a mail
        // see #22773
        //if ($setting->getMode() != ilObjNotificationSettings::MODE_DEF_OFF_USER_ACTIVATION && !$ignore_threshold) {
        if (!$ignore_threshold) {
            $sql = "SELECT user_id FROM notification" .
                " WHERE type = " . $ilDB->quote($type, "integer") .
                " AND id = " . $ilDB->quote($id, "integer") .
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
                $log->debug("Remove due to got mail: " . $rec["user_id"]);
            }
        }

        // get single subscriptions
        if ($setting->getMode() != ilObjNotificationSettings::MODE_DEF_ON_NO_OPT_OUT) {
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
                $log->debug("Adding single subscription: " . $row["user_id"]);
            }
        }

        return $recipients;
    }

    /**
     * Set notification status for object and user
     *
     * @param	int		$type
     * @param	int		$user_id
     * @param	int		$id
     * @param	bool	$status
     */
    public static function setNotification($type, $user_id, $id, $status = true)
    {
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
     *
     * @param	int		$type
     * @param	int		$id
     * @param	array	$user_ids
     * @param	int		$page_id
     */
    public static function updateNotificationTime($type, $id, array $user_ids, $page_id = false)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        // create initial entries, if not existing
        // see #22773, currently only done for wiki, might be feasible for other variants
        if (in_array($type, [self::TYPE_WIKI, self::TYPE_BLOG])) {
            $set = $ilDB->queryF(
                "SELECT user_id FROM notification " .
                " WHERE type = %s AND id = %s AND " .
                $ilDB->in("user_id", $user_ids, false, "integer"),
                ["integer", "integer"],
                [$type, $id]
            );
            $noti_users = [];
            while ($rec = $ilDB->fetchAssoc($set)) {
                $noti_users[] = $rec["user_id"];
            }
            foreach ($user_ids as $user_id) {
                if (!in_array($user_id, $noti_users)) {
                    $ilDB->insert("notification", [
                        "type" => ["integer", $type],
                        "id" => ["integer", $id],
                        "user_id" => ["integer", $user_id],
                        "page_id" => ["integer", (int) $page_id],
                        "activated" => ["integer", 1]
                    ]);
                }
            }
        }

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
     *
     * @param	int		$type
     * @param	int		$id
     */
    public static function removeForObject($type, $id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->query("DELETE FROM notification" .
                " WHERE type = " . $ilDB->quote($type, "integer") .
                " AND id = " . $ilDB->quote($id, "integer"));
    }

    /**
     * Remove all notifications for given user
     *
     * @param	int		$user_id
     */
    public static function removeForUser($user_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->query("DELETE FROM notification" .
                " WHERE user_id = " . $ilDB->quote($user_id, "integer"));
    }

    /**
     * Get activated notifications of give type for user
     *
     * @param int $type
     * @param int $user_id
     * @return int[]
     */
    public static function getActivatedNotifications(int $type, int $user_id) : array
    {
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
