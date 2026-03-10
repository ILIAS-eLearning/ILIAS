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

class ilSessionStatistics
{
    private const int SLOT_SIZE = 15;

    private static ?ilDBStatement $number_of_active_raw_sessions_statement = null;
    private static ?ilDBStatement $aggregated_raw_data_statement = null;
    private static ?ilDBStatement $raw_data_statement = null;

    public static function isActive(): bool
    {
        global $DIC;

        /** @var ilSetting $ilSetting */
        $ilSetting = $DIC['ilSetting'];

        return (bool) $ilSetting->get('session_statistics', '1');
    }

    public static function createRawEntry(string $a_session_id, int $a_session_type, int $a_timestamp, int $a_user_id): void
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC['ilDB'];

        if (!$a_user_id || !$a_session_id || !self::isActive()) {
            return;
        }

        // #9669: if a session was destroyed and somehow the session id is still
        // in use there will be a id-collision for the raw-entry
        $ilDB->replace(
            'usr_session_stats_raw',
            [
                'session_id' => [ilDBConstants::T_TEXT, $a_session_id]
            ],
            [
                'type' => [ilDBConstants::T_INTEGER, $a_session_type],
                'start_time' => [ilDBConstants::T_INTEGER, $a_timestamp],
                'user_id' => [ilDBConstants::T_INTEGER, $a_user_id]
            ]
        );
    }

    /**
     * @param string|list<string> $a_session_id
     * @param int|bool $a_expired_at
     */
    public static function closeRawEntry($a_session_id, ?int $a_context = null, $a_expired_at = null): void
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
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
            $sql = 'UPDATE usr_session_stats_raw' .
                ' SET end_time = ' . $ilDB->quote($end_time, ilDBConstants::T_INTEGER);
            if ($a_context) {
                $sql .= ', end_context = ' . $ilDB->quote($a_context, ilDBConstants::T_INTEGER);
            }
            $sql .= ' WHERE session_id = ' . $ilDB->quote($a_session_id, ilDBConstants::T_TEXT) .
                ' AND end_time IS NULL';
            $ilDB->manipulate($sql);
        }
        // batch closing
        elseif (!$a_expired_at) {
            $sql = 'UPDATE usr_session_stats_raw' .
                ' SET end_time = ' . $ilDB->quote(time(), ilDBConstants::T_INTEGER);
            if ($a_context) {
                $sql .= ', end_context = ' . $ilDB->quote($a_context, ilDBConstants::T_INTEGER);
            }
            $sql .= ' WHERE ' . $ilDB->in('session_id', $a_session_id, false, ilDBConstants::T_TEXT) .
                ' AND end_time IS NULL';
            $ilDB->manipulate($sql);
        }
        // batch with individual timestamps
        else {
            foreach ($a_session_id as $id => $ts) {
                $sql = 'UPDATE usr_session_stats_raw' .
                    ' SET end_time = ' . $ilDB->quote($ts, ilDBConstants::T_INTEGER);
                if ($a_context) {
                    $sql .= ', end_context = ' . $ilDB->quote($a_context, ilDBConstants::T_INTEGER);
                }
                $sql .= ' WHERE session_id = ' . $ilDB->quote($id, ilDBConstants::T_TEXT) .
                    ' AND end_time IS NULL';
                $ilDB->manipulate($sql);
            }
        }
    }

    /**
     * Get next slot to aggregate
     * @return array{0: int, 1: int}|null
     */
    private static function getCurrentSlot(int $a_now): ?array
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC['ilDB'];

        // get latest slot in db
        $sql = 'SELECT MAX(slot_end) previous_slot_end FROM usr_session_stats';
        $res = $ilDB->query($sql);
        $row = $ilDB->fetchAssoc($res);
        $previous_slot_end = $row['previous_slot_end'];

        // no previous slot?  calculate last complete slot
        // should we use minimum session raw date instead? (problem: table lock)
        if (!$previous_slot_end) {
            $slot = (int) (floor(date('i') / self::SLOT_SIZE));
            // last slot of previous hour
            if (!$slot) {
                $current_slot_begin = mktime((int) date('H', $a_now) - 1, 60 - self::SLOT_SIZE, 0);
            }
            // "normalize" to slot
            else {
                $current_slot_begin = mktime((int) date('H', $a_now), ($slot - 1) * self::SLOT_SIZE, 0);
            }
        } else {
            $current_slot_begin = $previous_slot_end + 1;
        }

        $current_slot_end = $current_slot_begin + (60 * self::SLOT_SIZE) - 1;

        // no complete slot: nothing to do yet
        if ($current_slot_end < $a_now) {
            return [$current_slot_begin, $current_slot_end];
        }

        return null;
    }

    private static function getNumberOfActiveRawSessions(int $a_time): int
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC['ilDB'];

        return (int) $ilDB->fetchAssoc(
            $ilDB->execute(
                self::getNumberOfActiveRawSessionsPreparedStatement(),
                [$a_time, $a_time]
            )
        )['counter'];
    }

    /**
     * @return Generator<array{start_time: int, end_time: ?int, end_context: ?int}>
     */
    private static function getRawData(int $a_begin, int $a_end): Generator
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->execute(
            self::getRawDataPreparedStatement(),
            [$a_end, $a_begin]
        );
        while ($row = $ilDB->fetchAssoc($res)) {
            yield $row;
        }
    }

    /**
     * Create new slot (using table lock)
     * @return array{0: int, 1: int}|null
     */
    private static function createNewAggregationSlot(int $a_now): ?array
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC['ilDB'];

        $ilAtomQuery = $ilDB->buildAtomQuery();
        $ilAtomQuery->addTableLock('usr_session_stats');

        $ilAtomQuery->addQueryCallable(function (ilDBInterface $ilDB) use ($a_now, &$slot) {
            // if we had to wait for the lock, no current slot should be returned here
            $slot = self::getCurrentSlot($a_now);
            if (!is_array($slot)) {
                $slot = null;
                return;
            }

            // save slot to mark as taken
            $fields = [
                'slot_begin' => [ilDBConstants::T_INTEGER, $slot[0]],
                'slot_end' => [ilDBConstants::T_INTEGER, $slot[1]],
            ];
            $ilDB->insert('usr_session_stats', $fields);
        });

        $ilAtomQuery->run();

        return $slot;
    }

    public static function aggregateRaw(int $a_now): void
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

    private static function getNumberOfActiveRawSessionsPreparedStatement(): ilDBStatement
    {
        if (self::$number_of_active_raw_sessions_statement === null) {
            global $DIC;

            /** @var ilDBInterface $ilDB */
            $ilDB = $DIC['ilDB'];

            self::$number_of_active_raw_sessions_statement = $ilDB->prepare(
                'SELECT COUNT(*) counter FROM usr_session_stats_raw '
                . 'WHERE (end_time IS NULL OR end_time >= ?) '
                . 'AND start_time <= ? '
                . 'AND ' . $ilDB->in('type', ilSessionControl::$session_types_controlled, false, ilDBConstants::T_INTEGER),
                [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER]
            );
        }

        return self::$number_of_active_raw_sessions_statement;
    }

    private static function getAggregatedRawDataPreparedStatement(): ilDBStatement
    {
        if (!self::$aggregated_raw_data_statement) {
            global $DIC;

            /** @var ilDBInterface $ilDB */
            $ilDB = $DIC['ilDB'];

            self::$aggregated_raw_data_statement = $ilDB->prepareManip(
                'UPDATE usr_session_stats '
                . 'SET active_min = ?, '
                . 'active_max = ?, '
                . 'active_avg = ?, '
                . 'active_end = ?, '
                . 'opened = ?, '
                . 'closed_manual = ?, '
                . 'closed_expire = ?, '
                . 'closed_login = ?, '
                . 'closed_misc = ? '
                . 'WHERE slot_begin = ? AND slot_end = ?',
                [
                    ilDBConstants::T_INTEGER,
                    ilDBConstants::T_INTEGER,
                    ilDBConstants::T_INTEGER,
                    ilDBConstants::T_INTEGER,
                    ilDBConstants::T_INTEGER,
                    ilDBConstants::T_INTEGER,
                    ilDBConstants::T_INTEGER,
                    ilDBConstants::T_INTEGER,
                    ilDBConstants::T_INTEGER,
                    ilDBConstants::T_INTEGER,
                    ilDBConstants::T_INTEGER
                ]
            );
        }

        return self::$aggregated_raw_data_statement;
    }

    private static function getRawDataPreparedStatement(): ilDBStatement
    {
        if (!self::$raw_data_statement) {
            global $DIC;

            /** @var ilDBInterface $ilDB */
            $ilDB = $DIC['ilDB'];

            self::$raw_data_statement = $ilDB->prepare(
                'SELECT start_time, end_time, end_context FROM usr_session_stats_raw' .
                ' WHERE start_time <= ?' .
                ' AND (end_time IS NULL OR end_time >= ?)' .
                ' AND ' . $ilDB->in('type', ilSessionControl::$session_types_controlled, false, ilDBConstants::T_INTEGER) .
                ' ORDER BY start_time',
                [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER]
            );
        }
        return self::$raw_data_statement;
    }

    private static function aggregateRawHelper(int $a_begin, int $a_end): void
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC['ilDB'];

        // "relevant" closing types
        $separate_closed = [
            ilSession::SESSION_CLOSE_USER,
            ilSession::SESSION_CLOSE_EXPIRE,
            ilSession::SESSION_CLOSE_LOGIN
        ];

        // gather/process data (build event timeline)
        $events = [];
        $closed_counter = $events;
        $opened_counter = 0;
        foreach (self::getRawData($a_begin, $a_end) as $item) {
            // open/close counters are _not_ time related

            // we could filter for undefined/invalid closing contexts
            // and ignore those items, but this would make any debugging
            // close to impossible
            // "closed_other" would have been a good idea...

            // session opened
            if ($item['start_time'] >= $a_begin) {
                $opened_counter++;
                $events[$item['start_time']][] = 1;
            }
            // session closed
            if ($item['end_time'] && $item['end_time'] <= $a_end) {
                if (in_array($item['end_context'], $separate_closed, true)) {
                    if (!isset($closed_counter[$item['end_context']])) {
                        $closed_counter[$item['end_context']] = 0;
                    }

                    $closed_counter[$item['end_context']]++;
                } else {
                    $closed_counter[0] = ($closed_counter[0] ?? 0) + 1;
                }
                $events[$item['end_time']][] = -1;
            }
        }

        // initializing active statistical values
        $active_begin = self::getNumberOfActiveRawSessions($a_begin - 1);
        $active_avg = $active_begin;
        $active_max = $active_begin;
        $active_min = $active_begin;
        $active_end = $active_begin;

        // parsing events / building averages
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

        $ilDB->execute(
            self::getAggregatedRawDataPreparedStatement(),
            [
                $active_min,
                $active_max,
                $active_avg,
                $active_end,
                $opened_counter,
                (int) ($closed_counter[ilSession::SESSION_CLOSE_USER] ?? 0),
                (int) ($closed_counter[ilSession::SESSION_CLOSE_EXPIRE] ?? 0),
                (int) ($closed_counter[ilSession::SESSION_CLOSE_LOGIN] ?? 0),
                (int) ($closed_counter[0] ?? 0),
                $a_begin,
                $a_end
            ]
        );
    }

    private static function deleteAggregatedRaw(int $a_now): void
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC['ilDB'];

        // we are rather defensive here - 7 days BEFORE current aggregation
        $cut = $a_now - (60 * 60 * 24 * 7);

        $ilDB->manipulate(
            'DELETE FROM usr_session_stats_raw' .
            ' WHERE start_time <= ' . $ilDB->quote($cut, ilDBConstants::T_INTEGER)
        );
    }

    /**
     * @return array{opened: int, closed_manual: int, closed_expire: int, closed_login: int, closed_misc: int}
     */
    public static function getNumberOfSessionsByType(int $a_from, int $a_to): array
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC['ilDB'];

        $sql = 'SELECT SUM(opened) opened, SUM(closed_manual) closed_manual,' .
            ' SUM(closed_expire) closed_expire,' .
            ' SUM(closed_login) closed_login, SUM(closed_misc) closed_misc' .
            ' FROM usr_session_stats' .
            ' WHERE slot_end > ' . $ilDB->quote($a_from, ilDBConstants::T_INTEGER) .
            ' AND slot_begin < ' . $ilDB->quote($a_to, ilDBConstants::T_INTEGER);
        $res = $ilDB->query($sql);

        return $ilDB->fetchAssoc($res);
    }

    /**
     * @return list<array{slot_begin: int, slot_end: int, active_min: int, active_max: int, active_avg: int}>
     */
    public static function getActiveSessions(int $a_from, int $a_to): array
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC['ilDB'];

        $sql = 'SELECT slot_begin, slot_end, active_min, active_max, active_avg' .
            ' FROM usr_session_stats' .
            ' WHERE slot_end > ' . $ilDB->quote($a_from, ilDBConstants::T_INTEGER) .
            ' AND slot_begin < ' . $ilDB->quote($a_to, ilDBConstants::T_INTEGER) .
            ' ORDER BY slot_begin';
        $res = $ilDB->query($sql);

        $all = [];
        while ($row = $ilDB->fetchAssoc($res)) {
            $all[] = array_map(intval(...), $row);
        }

        return $all;
    }

    /**
     * Get timestamp of last aggregation
     */
    public static function getLastAggregation(): ?int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $sql = 'SELECT MAX(slot_end) latest FROM usr_session_stats';
        $res = $ilDB->query($sql);
        $row = $ilDB->fetchAssoc($res);
        if ($row['latest'] !== null) {
            return (int) $row['latest'];
        }

        //TODO check if return null as timestamp causes issues
        return null;
    }
}
