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
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*/
class ilSessionStatistics
{
    private const SLOT_SIZE = 15;
    
    /**
     * Is session statistics active at all?
     */
    public static function isActive() : bool
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        return (bool) $ilSetting->get('session_statistics', "1");
    }
    
    /**
     * Create raw data entry
     */
    public static function createRawEntry(string $a_session_id, int $a_session_type, int $a_timestamp, int $a_user_id) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$a_user_id || !$a_session_id || !self::isActive()) {
            return;
        }
    
        // #9669: if a session was destroyed and somehow the session id is still
        // in use there will be a id-collision for the raw-entry
        
        $ilDB->replace(
            "usr_session_stats_raw",
            array(
                "session_id" => array("text", $a_session_id)
            ),
            array(
                "type" => array("integer", $a_session_type),
                "start_time" => array("integer", $a_timestamp),
                "user_id" => array("integer", $a_user_id)
            )
        );
    }
    
    /**
     * Close raw data entry
     *
     * @param int|array $a_session_id
     * @param int $a_context
     * @param int|bool $a_expired_at
     */
    public static function closeRawEntry($a_session_id, ?int $a_context = null, $a_expired_at = null) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!self::isActive()) {
            return;
        }
        
        // single entry
        if (!is_array($a_session_id)) {
            if ($a_expired_at) {
                $end_time = $a_expired_at;
            } else {
                $end_time = time();
            }
            $sql = "UPDATE usr_session_stats_raw" .
                " SET end_time = " . $ilDB->quote($end_time, "integer");
            if ($a_context) {
                $sql .= ",end_context = " . $ilDB->quote($a_context, "integer");
            }
            $sql .= " WHERE session_id = " . $ilDB->quote($a_session_id, "text") .
                " AND end_time IS NULL";
            $ilDB->manipulate($sql);
        }
        // batch closing
        elseif (!$a_expired_at) {
            $sql = "UPDATE usr_session_stats_raw" .
                " SET end_time = " . $ilDB->quote(time(), "integer");
            if ($a_context) {
                $sql .= ",end_context = " . $ilDB->quote($a_context, "integer");
            }
            $sql .= " WHERE " . $ilDB->in("session_id", $a_session_id, false, "text") .
                " AND end_time IS NULL";
            $ilDB->manipulate($sql);
        }
        // batch with individual timestamps
        else {
            foreach ($a_session_id as $id => $ts) {
                $sql = "UPDATE usr_session_stats_raw" .
                    " SET end_time = " . $ilDB->quote($ts, "integer");
                if ($a_context) {
                    $sql .= ",end_context = " . $ilDB->quote($a_context, "integer");
                }
                $sql .= " WHERE session_id = " . $ilDB->quote($id, "text") .
                    " AND end_time IS NULL";
                $ilDB->manipulate($sql);
            }
        }
    }
    
    /**
     * Get next slot to aggregate
     *
     * @return array begin, end
     */
    protected static function getCurrentSlot(int $a_now) : ?array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        // get latest slot in db
        $sql = "SELECT MAX(slot_end) previous_slot_end" .
            " FROM usr_session_stats";
        $res = $ilDB->query($sql);
        $row = $ilDB->fetchAssoc($res);
        $previous_slot_end = $row["previous_slot_end"];
        
        // no previous slot?  calculate last complete slot
        // should we use minimum session raw date instead? (problem: table lock)
        if (!$previous_slot_end) {
            $slot = (int) (floor(date("i") / self::SLOT_SIZE));
            // last slot of previous hour
            if (!$slot) {
                $current_slot_begin = mktime((int) date("H", $a_now) - 1, 60 - self::SLOT_SIZE, 0);
            }
            // "normalize" to slot
            else {
                $current_slot_begin = mktime((int) date("H", $a_now), ($slot - 1) * self::SLOT_SIZE, 0);
            }
        } else {
            $current_slot_begin = $previous_slot_end + 1;
        }
        
        $current_slot_end = $current_slot_begin + (60 * self::SLOT_SIZE) - 1;
        
        // no complete slot: nothing to do yet
        if ($current_slot_end < $a_now) {
            return array($current_slot_begin, $current_slot_end);
        }
        return null;
    }
    
    /**
     * Count number of active sessions at given time
     *
     * @param integer $a_time
     * @return integer
     */
    protected static function getNumberOfActiveRawSessions(int $a_time) : int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $sql = "SELECT COUNT(*) counter FROM usr_session_stats_raw" .
            " WHERE (end_time IS NULL OR end_time >= " . $ilDB->quote($a_time, "integer") . ")" .
            " AND start_time <= " . $ilDB->quote($a_time, "integer") .
            " AND " . $ilDB->in("type", ilSessionControl::$session_types_controlled, false, "integer");
        $res = $ilDB->query($sql);
        $row = $ilDB->fetchAssoc($res);
        return (int) $row["counter"];
    }
    
    /**
     * Read raw data for timespan
     */
    protected static function getRawData(int $a_begin, int $a_end) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $sql = "SELECT start_time,end_time,end_context FROM usr_session_stats_raw" .
            " WHERE start_time <= " . $ilDB->quote($a_end, "integer") .
            " AND (end_time IS NULL OR end_time >= " . $ilDB->quote($a_begin, "integer") . ")" .
            " AND " . $ilDB->in("type", ilSessionControl::$session_types_controlled, false, "integer") .
            " ORDER BY start_time";
        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $all[] = $row;
        }
        return $all;
    }
    
    /**
     * Create new slot (using table lock)
     *
     * @return array begin, end
     */
    protected static function createNewAggregationSlot(int $a_now) : ?array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilAtomQuery = $ilDB->buildAtomQuery();
        $ilAtomQuery->addTableLock("usr_session_stats");

        $ilAtomQuery->addQueryCallable(function (ilDBInterface $ilDB) use ($a_now, &$slot) {

            // if we had to wait for the lock, no current slot should be returned here
            $slot = self::getCurrentSlot($a_now);
            if (!is_array($slot)) {
                $slot = null;
                return;
            }

            // save slot to mark as taken
            $fields = array(
                "slot_begin" => array("integer", $slot[0]),
                "slot_end" => array("integer", $slot[1]),
            );
            $ilDB->insert("usr_session_stats", $fields);
        });

        $ilAtomQuery->run();
        
        return $slot;
    }
    
    /**
     * Aggregate raw session data (older than given time)
     *
     * @param integer $a_now
     */
    public static function aggretateRaw(int $a_now) : void
    {
        if (!self::isActive()) {
            return;
        }
        
        $slot = self::createNewAggregationSlot($a_now);
        while (is_array($slot)) {
            self::aggregateRawHelper($slot[0], $slot[1]);
            $slot = self::createNewAggregationSlot($a_now);
        }
        
        // #12728
        self::deleteAggregatedRaw($a_now);
    }
    
    /**
     * Aggregate statistics data for one slot
     *
     */
    public static function aggregateRawHelper(int $a_begin, int $a_end) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilSetting = $DIC['ilSetting'];
                
        // "relevant" closing types
        $separate_closed = array(ilSession::SESSION_CLOSE_USER,
            ilSession::SESSION_CLOSE_EXPIRE,
            ilSession::SESSION_CLOSE_IDLE,
            ilSession::SESSION_CLOSE_FIRST,
            ilSession::SESSION_CLOSE_LIMIT,
            ilSession::SESSION_CLOSE_LOGIN);
            
        // gather/process data (build event timeline)
        $closed_counter = $events = array();
        $opened_counter = 0;
        foreach (self::getRawData($a_begin, $a_end) as $item) {
            // open/close counters are _not_ time related
            
            // we could filter for undefined/invalid closing contexts
            // and ignore those items, but this would make any debugging
            // close to impossible
            // "closed_other" would have been a good idea...
            
            // session opened
            if ($item["start_time"] >= $a_begin) {
                $opened_counter++;
                $events[$item["start_time"]][] = 1;
            }
            // session closed
            if ($item["end_time"] && $item["end_time"] <= $a_end) {
                if (in_array($item["end_context"], $separate_closed, true)) {
                    $closed_counter[$item["end_context"]]++;
                } else {
                    $closed_counter[0] = ($closed_counter[0] ?? 0) + 1;
                }
                $events[$item["end_time"]][] = -1;
            }
        }
        
        // initialising active statistical values
        $active_begin = self::getNumberOfActiveRawSessions($a_begin - 1);
        $active_end = $active_min = $active_max = $active_avg = $active_begin;
        
        // parsing events / building avergages
        if (count($events)) {
            $last_update_avg = $a_begin - 1;
            $slot_seconds = self::SLOT_SIZE * 60;
            $active_avg = 0;
            
            // parse all open/closing events
            ksort($events);
            foreach ($events as $ts => $actions) {
                // actions which occur in the same second are "merged"
                foreach ($actions as $action) {
                    // max
                    if ($action > 0) {
                        $active_end++;
                    }
                    // min
                    else {
                        $active_end--;
                    }
                }
                
                // max
                if ($active_end > $active_max) {
                    $active_max = $active_end;
                }
                
                // min
                if ($active_end < $active_min) {
                    $active_min = $active_end;
                }
                
                // avg
                $diff = $ts - $last_update_avg;
                $active_avg += $diff / $slot_seconds * $active_end;
                $last_update_avg = $ts;
            }

            // add up to end of slot if needed
            if ($last_update_avg < $a_end) {
                $diff = $a_end - $last_update_avg;
                $active_avg += $diff / $slot_seconds * $active_end;
            }
            
            $active_avg = round($active_avg);
        }
        unset($events);
        
        
        // do we (really) need a log here?
        // $max_sessions = (int)$ilSetting->get("session_max_count", ilSessionControl::DEFAULT_MAX_COUNT);
        $max_sessions = self::getLimitForSlot($a_begin);
        
        // save aggregated data
        $fields = array(
            "active_min" => array("integer", $active_min),
            "active_max" => array("integer", $active_max),
            "active_avg" => array("integer", $active_avg),
            "active_end" => array("integer", $active_end),
            "opened" => array("integer", $opened_counter),
            "closed_manual" => array("integer", (int) ($closed_counter[ilSession::SESSION_CLOSE_USER] ?? 0)),
            "closed_expire" => array("integer", (int) ($closed_counter[ilSession::SESSION_CLOSE_EXPIRE] ?? 0)),
            "closed_idle" => array("integer", (int) ($closed_counter[ilSession::SESSION_CLOSE_IDLE] ?? 0)),
            "closed_idle_first" => array("integer", (int) ($closed_counter[ilSession::SESSION_CLOSE_FIRST] ?? 0)),
            "closed_limit" => array("integer", (int) ($closed_counter[ilSession::SESSION_CLOSE_LIMIT] ?? 0)),
            "closed_login" => array("integer", (int) ($closed_counter[ilSession::SESSION_CLOSE_LOGIN] ?? 0)),
            "closed_misc" => array("integer", (int) ($closed_counter[0] ?? 0)),
            "max_sessions" => array("integer", $max_sessions)
        );
        $ilDB->update(
            "usr_session_stats",
            $fields,
            array("slot_begin" => array("integer", $a_begin),
                "slot_end" => array("integer", $a_end))
        );
    }
    
    /**
     * Remove already aggregated raw data
     *
     * @param integer $a_now
     */
    protected static function deleteAggregatedRaw($a_now) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
    
        // we are rather defensive here - 7 days BEFORE current aggregation
        $cut = $a_now - (60 * 60 * 24 * 7);
        
        $ilDB->manipulate("DELETE FROM usr_session_stats_raw" .
            " WHERE start_time <= " . $ilDB->quote($cut, "integer"));
    }
    
    /**
     * Get latest slot during which sessions were maxed out
     *
     * @return ?int timestamp
     */
    public static function getLastMaxedOut() : ?int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $sql = "SELECT max(slot_end) latest FROM usr_session_stats" .
            " WHERE active_max >= max_sessions" .
            " AND max_sessions > " . $ilDB->quote(0, "integer");
        $res = $ilDB->query($sql);
        $row = $ilDB->fetchAssoc($res);
        if ($row["latest"]) {
            return $row["latest"];
        }
        //TODO check if return null as timestamp causes issues
        return null;
    }
    
    /**
     * Get maxed out duration in given timeframe
     *
     * @return ?int seconds
     */
    public static function getMaxedOutDuration(int $a_from, int $a_to) : ?int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $sql = "SELECT SUM(slot_end-slot_begin) dur FROM usr_session_stats" .
            " WHERE active_max >= max_sessions" .
            " AND max_sessions > " . $ilDB->quote(0, "integer") .
            " AND slot_end > " . $ilDB->quote($a_from, "integer") .
            " AND slot_begin < " . $ilDB->quote($a_to, "integer");
        $res = $ilDB->query($sql);
        $row = $ilDB->fetchAssoc($res);
        if ($row["dur"]) {
            return $row["dur"];
        }
        //TODO check if return null as timestamp causes issues
        return null;
    }
    
    /**
     * Get session counters by type (opened, closed)
     */
    public static function getNumberOfSessionsByType(int $a_from, int $a_to) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $sql = "SELECT SUM(opened) opened, SUM(closed_manual) closed_manual," .
            " SUM(closed_expire) closed_expire, SUM(closed_idle) closed_idle," .
            " SUM(closed_idle_first) closed_idle_first, SUM(closed_limit) closed_limit," .
            " SUM(closed_login) closed_login, SUM(closed_misc) closed_misc" .
            " FROM usr_session_stats" .
            " WHERE slot_end > " . $ilDB->quote($a_from, "integer") .
            " AND slot_begin < " . $ilDB->quote($a_to, "integer");
        $res = $ilDB->query($sql);
        return $ilDB->fetchAssoc($res);
    }
    
    /**
     * Get active sessions aggregated data
     */
    public static function getActiveSessions(int $a_from, int $a_to) : array
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC['ilDB'];
    
        $sql = "SELECT slot_begin, slot_end, active_min, active_max, active_avg," .
            " max_sessions" .
            " FROM usr_session_stats" .
            " WHERE slot_end > " . $ilDB->quote($a_from, "integer") .
            " AND slot_begin < " . $ilDB->quote($a_to, "integer") .
            " ORDER BY slot_begin";
        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $entry = [];
            foreach ($row as $key => $value) {
                $entry[$key] = (int) $value;
            }
            $all[] = $entry;
        }
        return $all;
    }
    
    /**
     * Get timestamp of last aggregation
     */
    public static function getLastAggregation() : ?int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $sql = "SELECT max(slot_end) latest FROM usr_session_stats";
        $res = $ilDB->query($sql);
        $row = $ilDB->fetchAssoc($res);
        if ($row["latest"]) {
            return (int) $row["latest"];
        }
        //TODO check if return null as timestamp causes issues
        return null;
    }
    
    /**
     * Get max session setting for given timestamp
     *
     */
    public static function getLimitForSlot(int $a_timestamp) : int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilSetting = $DIC['ilSetting'];
        
        $ilDB->setLimit(1);
        $sql = "SELECT maxval FROM usr_session_log" .
            " WHERE tstamp <= " . $ilDB->quote($a_timestamp, "integer") .
            " ORDER BY tstamp DESC";
        $res = $ilDB->query($sql);
        $val = $ilDB->fetchAssoc($res);
        if (isset($val["maxval"]) && $val["maxval"]) {
            return (int) $val["maxval"];
        }

        return (int) $ilSetting->get("session_max_count", (string) ilSessionControl::DEFAULT_MAX_COUNT);
    }
    
    /**
     * Log max session setting
     */
    public static function updateLimitLog(int $a_new_value) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilSetting = $DIC['ilSetting'];
        $ilUser = $DIC['ilUser'];
        
        $new_value = $a_new_value;
        $old_value = (int) $ilSetting->get("session_max_count", (string) ilSessionControl::DEFAULT_MAX_COUNT);
        
        if ($new_value !== $old_value) {
            $fields = array(
                "tstamp" => array("timestamp", time()),
                "maxval" => array("integer", $new_value),
                "user_id" => array("integer", $ilUser->getId())
            );
            $ilDB->insert("usr_session_log", $fields);
        }
    }
}
