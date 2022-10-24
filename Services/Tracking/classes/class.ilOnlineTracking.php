<?php

declare(strict_types=0);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilOnlineTracking
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @package ilias-core
 *          Stores total online time of users
 */
class ilOnlineTracking
{
    /**
     * This static variable is used to prevent two database request (addUser and updateAccess) on login
     */
    protected static ?int $last_access_time = null;

    public static function getOnlineTime(int $a_user_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();
        $res = $ilDB->query(
            'SELECT online_time FROM ut_online WHERE usr_id = ' . $ilDB->quote(
                $a_user_id,
                'integer'
            )
        );
        while ($row = $ilDB->fetchAssoc($res)) {
            return (int) $row['online_time'];
        }
        return 0;
    }

    public static function addUser(int $a_user_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $res = $ilDB->query(
            'SELECT access_time FROM ut_online WHERE usr_id = ' . $ilDB->quote(
                $a_user_id,
                'integer'
            )
        );
        if ($ilDB->numRows($res)) {
            $row = $ilDB->fetchAssoc($res);
            self::$last_access_time = (int) $row['access_time'];
            return false;
        }

        $ilDB->manipulateF(
            'INSERT INTO ut_online (usr_id, access_time) VALUES (%s, %s)',
            array('integer', 'integer'),
            array($a_user_id, time())
        );
        self::$last_access_time = time();
        return true;
    }

    public static function updateAccess(ilObjUser $user): bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilSetting = $DIC->settings();

        if (null === self::$last_access_time) {
            $query = 'SELECT access_time FROM ut_online WHERE usr_id = ' . $ilDB->quote(
                $user->getId(),
                'integer'
            );
            $res = $ilDB->query($query);
            if (!$ilDB->numRows($res)) {
                return false;
            }
            $row = $ilDB->fetchAssoc($res);
            self::$last_access_time = (int) $row['access_time'];
        }

        $time_span = (int) $ilSetting->get('tracking_time_span', '300');
        if (($diff = time() - self::$last_access_time) <= $time_span) {
            $ilDB->manipulateF(
                'UPDATE ut_online SET online_time = online_time + %s, access_time = %s WHERE usr_id = %s',
                array('integer', 'integer', 'integer'),
                array($diff, time(), $user->getId())
            );
        } else {
            $ilDB->manipulateF(
                'UPDATE ut_online SET access_time = %s WHERE usr_id = %s',
                array('integer', 'integer'),
                array(time(), $user->getId())
            );
        }
        return true;
    }
}
