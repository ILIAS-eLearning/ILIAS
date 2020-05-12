<?php
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
     * @var int
     * @static
     */
    protected static $last_access_time = null;

    /**
     * @static
     * @param int $a_user_id
     * @return int
     */
    public static function getOnlineTime($a_user_id)
    {
        /**
         * @var $ilDB ilDB
         */
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $res = $ilDB->query('SELECT online_time FROM ut_online WHERE usr_id = ' . $ilDB->quote($a_user_id, 'integer'));
        while ($row = $ilDB->fetchAssoc($res)) {
            return (int) $row['online_time'];
        }

        return 0;
    }

    /**
     * Add access time
     * @param int $a_user_id
     * @return bool
     * @static
     */
    public static function addUser($a_user_id)
    {
        /**
         * @var $ilDB ilDB
         */
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $res = $ilDB->query('SELECT access_time FROM ut_online WHERE usr_id = ' . $ilDB->quote($a_user_id, 'integer'));
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

    /**
     * Update access time
     * @param ilObjUser $user
     * @return bool
     * @static
     */
    public static function updateAccess(ilObjUser $user)
    {
        /**
         * @var $ilDB      ilDB
         * @var $ilSetting ilSetting
         */
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilSetting = $DIC['ilSetting'];

        if (null === self::$last_access_time) {
            $query = 'SELECT access_time FROM ut_online WHERE usr_id = ' . $ilDB->quote($user->getId(), 'integer');
            $res = $ilDB->query($query);
            if (!$ilDB->numRows($res)) {
                return false;
            }
            $row = $ilDB->fetchAssoc($res);
            self::$last_access_time = $row['access_time'];
        }

        $time_span = (int) $ilSetting->get('tracking_time_span', 300);
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
