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

declare(strict_types=1);

/**
 * @author Bjoern Heyser <bheyser@databay.de>
 */
class ilSessionControl
{
    /**
     * default value for settings that have not
     * been defined in setup or administration yet
     */
    public const DEFAULT_MIN_IDLE = 15;
    public const DEFAULT_ALLOW_CLIENT_MAINTENANCE = 1;

    /**
     * all fieldnames that are saved in settings table
     *
     * @var array $setting_fields
     */
    private static array $setting_fields = array(
        'session_allow_client_maintenance',
    );

    /**
     * session types from which one is
     * assigned to each session
     */
    private const SESSION_TYPE_UNKNOWN = 0;
    private const SESSION_TYPE_SYSTEM = 1;
    private const SESSION_TYPE_ADMIN = 2;
    private const SESSION_TYPE_USER = 3;
    private const SESSION_TYPE_ANONYM = 4;

    private const SESSION_TYPE_KEY = "SessionType";
    /**
     * all session types that will be involved when count of sessions
     * will be determined or when idleing sessions will be destroyed
     *
     * @var array $session_types_not_controlled
     */
    public static array $session_types_controlled = array(
        self::SESSION_TYPE_USER,
        self::SESSION_TYPE_ANONYM
    );

    /**
     * all session types that will be ignored when count of sessions
     * will be determined or when idleing sessions will be destroyed
     *
     * @var array $session_types_not_controlled
     */
    private static array $session_types_not_controlled = array(
        self::SESSION_TYPE_UNKNOWN,
        self::SESSION_TYPE_SYSTEM,
        self::SESSION_TYPE_ADMIN
    );

    /**
     * when current session is allowed to be created it marks it with
     * type regarding to the sessions user context.
     * when session is not allowed to be created it will be destroyed.
     */
    public static function handleLoginEvent(string $a_login, ilAuthSession $auth_session): bool
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $user_id = ilObjUser::_lookupId($a_login);

        // we need the session type for the session statistics
        // regardless of the current session handling type
        switch (true) {
            case isset($_ENV['SHELL']):
                $type = self::SESSION_TYPE_SYSTEM;
                break;

            case $user_id === ANONYMOUS_USER_ID:
                $type = self::SESSION_TYPE_ANONYM;
                break;

            case self::checkAdministrationPermission($user_id):
                $type = self::SESSION_TYPE_ADMIN;
                break;

            default:
                $type = self::SESSION_TYPE_USER;
                break;
        }

        ilSession::set(self::SESSION_TYPE_KEY, $type);
        self::debug(__METHOD__ . " --> update sessions type to (" . $type . ")");

        return true;
    }

    /**
     * reset sessions type to unknown
     */
    public static function handleLogoutEvent(): void
    {
    }

    /**
     * returns number of valid sessions relating to given session types
     */
    public static function getExistingSessionCount(array $a_types): int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ts = time();

        $query = "SELECT count(session_id) AS num_sessions FROM usr_session " .
                    "WHERE expires > %s " .
                    "AND " . $ilDB->in('type', $a_types, false, 'integer');

        $res = $ilDB->queryF($query, array('integer'), array($ts));
        return (int) $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)->num_sessions;
    }

    /**
     * checks if session exists for given id
     * and if it is still valid
     *
     * @return	boolean		session_valid
     */
    private static function isValidSession(string $a_sid): bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT session_id, expires FROM usr_session " .
                    "WHERE session_id = %s";

        $res = $ilDB->queryF($query, array('text'), array($a_sid));

        $ts = time();

        $sessions = array();

        while ($row = $ilDB->fetchAssoc($res)) {
            if ($row['expires'] > $ts) {
                self::debug(__METHOD__ . ' --> Found a valid session with id (' . $a_sid . ')');
                $sessions[] = $row;
            } else {
                self::debug(__METHOD__ . ' --> Found an expired session with id (' . $a_sid . ')');
            }
        }

        if (count($sessions) === 1) {
            self::debug(__METHOD__ . ' --> Exact one valid session found for session id (' . $a_sid . ')');

            return true;
        }

        if (count($sessions) > 1) {
            self::debug(__METHOD__ . ' --> Strange!!! More than one sessions found for given session id! (' . $a_sid . ')');
        } else {
            self::debug(__METHOD__ . ' --> No valid session found for session id (' . $a_sid . ')');
        }

        return false;
    }

    /**
     * removes a session cookie, so it is not sent by browser anymore
     */
    private static function removeSessionCookie(): void
    {
        ilUtil::setCookie(session_name(), 'deleted', true, true);
        self::debug('Session cookie has been removed');
    }

    /**
     * checks wether a given user login relates to an user
     * with administrative permissions
     *
     * @global ilRbacSystem $rbacsystem
     * @return boolean access
     */
    private static function checkAdministrationPermission(int $a_user_id): bool
    {
        if (!$a_user_id) {
            return false;
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        $access = $rbacsystem->checkAccessOfUser(
            $a_user_id,
            'read,visible',
            SYSTEM_FOLDER_ID
        );

        return $access;
    }

    /**
     * logs the given debug message in \ilLogger
     *
     * @param	string	$a_debug_log_message
     */
    private static function debug(string $a_debug_log_message): void
    {
        global $DIC;

        $logger = $DIC->logger()->auth();

        $logger->debug($a_debug_log_message);
    }

    /**
     * returns the array of setting fields
     *
     * @return array setting_fields
     */
    public static function getSettingFields(): array
    {
        return self::$setting_fields;
    }
}
