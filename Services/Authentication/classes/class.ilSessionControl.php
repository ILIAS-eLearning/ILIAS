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
 * @author Bjoern Heyser <bheyser@databay.de>
 */
class ilSessionControl
{
    /**
     * default value for settings that have not
     * been defined in setup or administration yet
     */
    public const DEFAULT_MAX_COUNT = 0;
    public const DEFAULT_MIN_IDLE = 15;
    public const DEFAULT_MAX_IDLE = 30;
    public const DEFAULT_MAX_IDLE_AFTER_FIRST_REQUEST = 1;
    public const DEFAULT_ALLOW_CLIENT_MAINTENANCE = 1;

    /**
     * all fieldnames that are saved in settings table
     *
     * @var array $setting_fields
     */
    private static array $setting_fields = array(
        'session_max_count',
        'session_min_idle',
        'session_max_idle',
        'session_max_idle_after_first_request',
        'session_allow_client_maintenance',
        'session_handling_type'
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
    public static function handleLoginEvent(string $a_login, ilAuthSession $auth_session) : bool
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
                
        // do not handle login event in fixed duration mode
        if ((int) $ilSetting->get('session_handling_type', (string) ilSession::SESSION_HANDLING_FIXED) !== ilSession::SESSION_HANDLING_LOAD_DEPENDENT) {
            return true;
        }
                
        if (in_array($type, self::$session_types_controlled, true)) {
            //TODO rework this, as it did return value of a void method call
            self::checkCurrentSessionIsAllowed($auth_session, $user_id);
            return true;
        }
        return false;
    }

    /**
     * reset sessions type to unknown
     */
    public static function handleLogoutEvent() : void
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        // do not handle logout event in fixed duration mode
        if ((int) $ilSetting->get('session_handling_type', '0') !== 1) {
            return;
        }

        ilSession::set('SessionType', self::SESSION_TYPE_UNKNOWN);
        self::debug(__METHOD__ . " --> reset sessions type to (" . ilSession::get('SessionType') . ")");
        
        // session_destroy() is called in auth, so raw data will be updated

        self::removeSessionCookie();
    }

    /**
     * checks wether the current session exhaust the limit of sessions
     * when limit is reached it deletes "firstRequestAbidencer" and checks again
     * when limit is still reached it deletes "oneMinIdleSession" and checks again
     * when limit is still reached the current session will be logged out
     */
    private static function checkCurrentSessionIsAllowed(ilAuthSession $auth, int $a_user_id) : void
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        $max_sessions = (int) $ilSetting->get('session_max_count', (string) self::DEFAULT_MAX_COUNT);

        if ($max_sessions > 0) {
            // get total number of sessions
            $num_sessions = self::getExistingSessionCount(self::$session_types_controlled);

            self::debug(__METHOD__ . "--> total existing sessions (" . $num_sessions . ")");

            if (($num_sessions + 1) > $max_sessions) {
                self::debug(__METHOD__ . ' --> limit for session pool reached, but try kicking some first request abidencer');

                self::kickFirstRequestAbidencer(self::$session_types_controlled);

                // get total number of sessions again
                $num_sessions = self::getExistingSessionCount(self::$session_types_controlled);

                if (($num_sessions + 1) > $max_sessions) {
                    self::debug(__METHOD__ . ' --> limit for session pool still reached so try kick one min idle session');

                    self::kickOneMinIdleSession(self::$session_types_controlled);

                    // get total number of sessions again
                    $num_sessions = self::getExistingSessionCount(self::$session_types_controlled);

                    if (($num_sessions + 1) > $max_sessions) {
                        self::debug(__METHOD__ . ' --> limit for session pool still reached so logout session (' . session_id() . ') and trigger event');

                        ilSession::setClosingContext(ilSession::SESSION_CLOSE_LIMIT);
                        
                        // as the session is opened and closed in one request, there
                        // is no proper session yet and we have to do this ourselves
                        ilSessionStatistics::createRawEntry(
                            session_id(),
                            ilSession::get(self::SESSION_TYPE_KEY),
                            time(),
                            $a_user_id
                        );

                        $auth->logout();

                        // Trigger reachedSessionPoolLimit Event
                        $ilAppEventHandler = $DIC['ilAppEventHandler'];
                        $ilAppEventHandler->raise(
                            'Services/Authentication',
                            'reachedSessionPoolLimit',
                            array()
                        );

                        // auth won't do this, we need to close session properly
                        // already done in new implementation
                        // session_destroy();

                        ilUtil::redirect('login.php?reached_session_limit=true');
                    } else {
                        self::debug(__METHOD__ . ' --> limit of session pool not reached anymore after kicking one min idle session');
                    }
                } else {
                    self::debug(__METHOD__ . ' --> limit of session pool not reached anymore after kicking some first request abidencer');
                }
            } else {
                self::debug(__METHOD__ . ' --> limit for session pool not reached yet');
            }
        } else {
            self::debug(__METHOD__ . ' --> limit for session pool not set so check is bypassed');
        }
    }

    /**
     * returns number of valid sessions relating to given session types
     */
    public static function getExistingSessionCount(array $a_types) : int
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
     * if sessions exist that relates to given session types
     * and idled longer than min idle parameter, this method
     * deletes one of these sessions
     */
    private static function kickOneMinIdleSession(array $a_types) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilSetting = $DIC['ilSetting'];

        $ts = time();
        $min_idle = (int) $ilSetting->get('session_min_idle', self::DEFAULT_MIN_IDLE) * 60;
        $max_idle = (int) $ilSetting->get('session_max_idle', self::DEFAULT_MAX_IDLE) * 60;

        $query = "SELECT session_id,expires FROM usr_session WHERE expires >= %s " .
                "AND (expires - %s) < (%s - %s) " .
                "AND " . $ilDB->in('type', $a_types, false, 'integer') . " ORDER BY expires";

        $res = $ilDB->queryF(
            $query,
            array('integer', 'integer', 'integer', 'integer'),
            array($ts, $ts, $max_idle, $min_idle)
        );
        
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            ilSession::_destroy($row->session_id, ilSession::SESSION_CLOSE_IDLE, $row->expires);

            self::debug(__METHOD__ . ' --> successfully deleted one min idle session');

            return;
        }
        self::debug(__METHOD__ . ' --> no min idle session available for deletion');
    }

    /**
     * kicks sessions of users that abidence after login
     * so people could not login and go for coffe break ;-)
     */
    private static function kickFirstRequestAbidencer(array $a_types) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilSetting = $DIC['ilSetting'];

        $max_idle_after_first_request = (int) $ilSetting->get('session_max_idle_after_first_request') * 60;

        if ((int) $max_idle_after_first_request === 0) {
            return;
        }

        $query = "SELECT session_id,expires FROM usr_session WHERE " .
                "(ctime - createtime) < %s " .
                "AND (%s - createtime) > %s " .
                "AND " . $ilDB->in('type', $a_types, false, 'integer');

        $res = $ilDB->queryF(
            $query,
            array('integer', 'integer', 'integer'),
            array($max_idle_after_first_request, time(), $max_idle_after_first_request)
        );
        
        $session_ids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $session_ids[$row->session_id] = $row->expires;
        }
        ilSession::_destroy($session_ids, ilSession::SESSION_CLOSE_FIRST, true);

        self::debug(__METHOD__ . ' --> Finished kicking first request abidencer');
    }

    /**
     * checks if session exists for given id
     * and if it is still valid
     *
     * @return	boolean		session_valid
     */
    private static function isValidSession(string $a_sid) : bool
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
    private static function removeSessionCookie() : void
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
    private static function checkAdministrationPermission(int $a_user_id) : bool
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
    private static function debug(string $a_debug_log_message) : void
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
    public static function getSettingFields() : array
    {
        return self::$setting_fields;
    }
}
