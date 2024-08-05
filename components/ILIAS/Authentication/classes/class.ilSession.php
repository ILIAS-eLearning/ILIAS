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
* @author Alex Killing <alex.killing@gmx.de>
*
* @externalTableAccess ilObjUser on usr_session
*/
class ilSession
{
    /**
     * Constant for reason of session destroy
     *
     * @var integer
     */
    public const SESSION_CLOSE_USER = 1;  // manual logout
    public const SESSION_CLOSE_EXPIRE = 2;  // has expired
    public const SESSION_CLOSE_LOGIN = 6;  // anonymous => login
    public const SESSION_CLOSE_PUBLIC = 7;  // => anonymous
    public const SESSION_CLOSE_TIME = 8;  // account time limit reached
    public const SESSION_CLOSE_IP = 9;  // wrong ip
    public const SESSION_CLOSE_SIMUL = 10; // simultaneous login
    public const SESSION_CLOSE_INACTIVE = 11; // inactive account

    private static ?int $closing_context = null;

    protected static bool $enable_web_access_without_session = false;

    /**
     * Get session data from table
     *
     * According to https://bugs.php.net/bug.php?id=70520 read data must return a string.
     * Otherwise session_regenerate_id might fail with php 7.
     *
     * @param	string		session id
     * @return	string		session data
     */
    public static function _getData(string $a_session_id): string
    {
        if (!$a_session_id) {
            // fix for php #70520
            return '';
        }
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $q = "SELECT data FROM usr_session WHERE session_id = " .
            $ilDB->quote($a_session_id, "text");
        $set = $ilDB->query($q);
        $rec = $ilDB->fetchAssoc($set);
        if (!is_array($rec)) {
            return '';
        }

        // fix for php #70520
        return (string) $rec["data"];
    }

    /**
     * Lookup expire time for a specific session
     * @param string $a_session_id
     * @return int expired unix timestamp
     */
    public static function lookupExpireTime(string $a_session_id): int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT expires FROM usr_session WHERE session_id = ' .
            $ilDB->quote($a_session_id, 'text');
        $res = $ilDB->query($query);
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->expires;
        }
        return 0;
    }

    public static function _writeData(string $a_session_id, string $a_data): bool
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC['ilDB'];
        /** @var ilIniFile $ilClientIniFile */
        $ilClientIniFile = $DIC['ilClientIniFile'];

        if (self::isWebAccessWithoutSessionEnabled()) {
            // Prevent session data written for web access checker
            // when no cookie was sent (e.g. for pdf files linking others).
            // This would result in new session records for each request.
            return true;
        }

        if (!$a_session_id) {
            return true;
        }

        $now = time();

        // prepare session data
        $fields = [
            'user_id' => [ilDBConstants::T_INTEGER, (int) (self::get('_authsession_user_id') ?? 0)],
            'expires' => [ilDBConstants::T_INTEGER, self::getExpireValue()],
            'data' => [ilDBConstants::T_CLOB, $a_data],
            'ctime' => [ilDBConstants::T_INTEGER, $now],
            'type' => [ilDBConstants::T_INTEGER, (int) (self::get('SessionType') ?? 0)]
        ];
        if ($ilClientIniFile->readVariable('session', 'save_ip')) {
            $fields['remote_addr'] = [ilDBConstants::T_TEXT, $_SERVER['REMOTE_ADDR'] ?? ''];
        }

        if (self::_exists($a_session_id)) {
            // note that we do this only when inserting the new record
            // updating may get us other contexts for the same session, especially ilContextWAC, which we do not want
            if (class_exists('ilContext') && ilContext::isSessionMainContext()) {
                $fields['context'] = [ilDBConstants::T_TEXT, ilContext::getType()];
            }
            $ilDB->update(
                'usr_session',
                $fields,
                ['session_id' => [ilDBConstants::T_TEXT, $a_session_id]]
            );
        } else {
            $fields['session_id'] = [ilDBConstants::T_TEXT, $a_session_id];
            $fields['createtime'] = [ilDBConstants::T_INTEGER, $now];

            // note that we do this only when inserting the new record
            // updating may get us other contexts for the same session, especially ilContextWAC, which we do not want
            if (class_exists('ilContext')) {
                $fields['context'] = [ilDBConstants::T_TEXT, ilContext::getType()];
            }

            $insert_fields = implode(', ', array_keys($fields));
            $insert_values = implode(
                ', ',
                array_map(
                    static fn(string $type, $value): string => $ilDB->quote($value, $type),
                    array_column($fields, 0),
                    array_column($fields, 1)
                )
            );

            $update_fields = array_filter(
                $fields,
                static fn(string $field): bool => !in_array($field, ['session_id', 'user_id', 'createtime'], true),
                ARRAY_FILTER_USE_KEY
            );
            $update_values = implode(
                ', ',
                array_map(
                    static fn(string $field, string $type, $value): string => $field . ' = ' . $ilDB->quote(
                        $value,
                        $type
                    ),
                    array_keys($update_fields),
                    array_column($update_fields, 0),
                    array_column($update_fields, 1)
                )
            );

            $ilDB->manipulate(
                'INSERT INTO usr_session (' . $insert_fields . ') '
                . 'VALUES (' . $insert_values . ') '
                . 'ON DUPLICATE KEY UPDATE ' . $update_values
            );

            // check type against session control
            $type = (int) $fields['type'][1];
            if (in_array($type, ilSessionControl::$session_types_controlled, true)) {
                ilSessionStatistics::createRawEntry(
                    $fields['session_id'][1],
                    $type,
                    $fields['createtime'][1],
                    $fields['user_id'][1]
                );
            }
        }

        if (!$DIC->cron()->manager()->isJobActive('auth_destroy_expired_sessions')) {
            $r = new \Random\Randomizer();
            if ($r->getInt(0, 50) === 2) {
                // get time _before_ destroying expired sessions
                self::_destroyExpiredSessions();
                ilSessionStatistics::aggretateRaw($now);
            }
        }

        return true;
    }



    /**
    * Check whether session exists
    *
    * @param	string		session id
    * @return	boolean		true, if session id exists
    */
    public static function _exists(string $a_session_id): bool
    {
        if (!$a_session_id) {
            return false;
        }
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $q = "SELECT 1 FROM usr_session WHERE session_id = " . $ilDB->quote($a_session_id, "text");
        $set = $ilDB->query($q);

        return $ilDB->numRows($set) > 0;
    }

    /**
    * Destroy session
    *
    * @param	string|array $a_session_id      session id|s
    * @param	int|null     $a_closing_context closing context
    * @param	int|bool     $a_expired_at      expired at timestamp
    */
    public static function _destroy($a_session_id, ?int $a_closing_context = null, $a_expired_at = null): bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!$a_closing_context) {
            $a_closing_context = self::$closing_context;
        }

        ilSessionStatistics::closeRawEntry($a_session_id, $a_closing_context, $a_expired_at);

        if (!is_array($a_session_id)) {
            $q = "DELETE FROM usr_session WHERE session_id = " .
                $ilDB->quote($a_session_id, "text");
        } else {
            // array: id => timestamp - so we get rid of timestamps
            if ($a_expired_at) {
                $a_session_id = array_keys($a_session_id);
            }
            $q = "DELETE FROM usr_session WHERE " .
                $ilDB->in("session_id", $a_session_id, false, "text");
        }

        ilSessionIStorage::destroySession($a_session_id);

        $ilDB->manipulate($q);

        try {
            // only delete session cookie if it is set in the current request
            if ($DIC->http()->wrapper()->cookie()->has(session_name()) &&
                $DIC->http()->wrapper()->cookie()->retrieve(session_name(), $DIC->refinery()->kindlyTo()->string()) === $a_session_id) {
                $cookieJar = $DIC->http()->cookieJar()->without(session_name());
                $cookieJar->renderIntoResponseHeader($DIC->http()->response());
            }
        } catch (\Throwable $e) {
            // ignore
            // this is needed for "header already"  sent errors when the random cleanup of expired sessions is triggered
        }

        return true;
    }

    /**
    * Destroy session
    *
    * @param	int 		user id
    */
    public static function _destroyByUserId(int $a_user_id): bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $q = "DELETE FROM usr_session WHERE user_id = " .
            $ilDB->quote($a_user_id, "integer");
        $ilDB->manipulate($q);

        return true;
    }

    /**
     * Destroy expired sessions
     * @return int The number of deleted sessions on success
     */
    public static function _destroyExpiredSessions(): int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $q = 'SELECT session_id, expires FROM usr_session WHERE expires < ' . $ilDB->quote(time(), ilDBConstants::T_INTEGER);
        $res = $ilDB->query($q);
        $ids = [];
        while ($row = $ilDB->fetchAssoc($res)) {
            $ids[$row['session_id']] = (int) $row['expires'];
        }
        if ($ids !== []) {
            self::_destroy($ids, self::SESSION_CLOSE_EXPIRE, true);
        }

        return count($ids);
    }

    /**
    * Duplicate session
    *
    * @param	string		session id
    * @return	string		new session id
    */
    public static function _duplicate(string $a_session_id): string
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // Create new session id
        $new_session = $a_session_id;
        do {
            $new_session = md5($new_session);
            $q = "SELECT * FROM usr_session WHERE " .
                "session_id = " . $ilDB->quote($new_session, "text");
            $res = $ilDB->query($q);
        } while ($ilDB->fetchAssoc($res));

        $query = "SELECT * FROM usr_session " .
            "WHERE session_id = " . $ilDB->quote($a_session_id, "text");
        $res = $ilDB->query($query);

        if ($row = $ilDB->fetchObject($res)) {
            self::_writeData($new_session, $row->data);
            return $new_session;
        }
        //TODO check if throwing an excpetion might be a better choice
        return "";
    }

    /**
     * Returns the expiration timestamp in seconds
     */
    public static function getExpireValue(): int
    {
            return time() + self::getIdleValue();
    }

    /**
     * Returns the idle time in seconds
     */
    public static function getIdleValue(): int
    {
        global $DIC;

        $ilClientIniFile = $DIC['ilClientIniFile'];

        return (int) $ilClientIniFile->readVariable('session', 'expire');
    }

    /**
     * Returns the session expiration value
     */
    public static function getSessionExpireValue(): int
    {
        return self::getIdleValue();
    }

    /**
     * Set a value
     */
    public static function set(string $a_var, $a_val): void
    {
        $_SESSION[$a_var] = $a_val;
    }

    /**
     * @return mixed|null
     */
    public static function get(string $a_var)
    {
        return $_SESSION[$a_var] ?? null;
    }

    public static function has($a_var): bool
    {
        return isset($_SESSION[$a_var]);
    }

    /**
     * @param string $a_var
     */
    public static function clear(string $a_var): void
    {
        if (isset($_SESSION[$a_var])) {
            unset($_SESSION[$a_var]);
        }
    }

    public static function dumpToString(): string
    {
        return print_r($_SESSION, true);
    }

    /**
     * set closing context (for statistics)
     */
    public static function setClosingContext(int $a_context): void
    {
        self::$closing_context = $a_context;
    }

    /**
     * get closing context (for statistics)
     */
    public static function getClosingContext(): int
    {
        return self::$closing_context;
    }



    /**
     * @return boolean
     */
    public static function isWebAccessWithoutSessionEnabled(): bool
    {
        return self::$enable_web_access_without_session;
    }

    /**
     * @param boolean $enable_web_access_without_session
     */
    public static function enableWebAccessWithoutSession(bool $enable_web_access_without_session): void
    {
        self::$enable_web_access_without_session = $enable_web_access_without_session;
    }
}
