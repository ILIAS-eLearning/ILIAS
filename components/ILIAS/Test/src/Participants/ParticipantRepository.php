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

namespace ILIAS\Test\Participants;

use ILIAS\Data\Order;
use ILIAS\Data\Range;

use function join;

class ParticipantRepository
{
    public function __construct(
        private readonly \ilDBInterface $database
    ) {
    }

    /**
     * @param int $test_id
     * @param array<string, mixed> $filter
     *
     * @return int
     */
    public function countParticipants(int $test_id, array $filter): int
    {
        $query = "
            SELECT COUNT(participants.user_fi) as number_of_participants
            FROM (
                ({$this->getActiveParticipantsQuery()})
                UNION
                ({$this->getInvitedParticipantsQuery()})
            ) participants
		";
        [$where, $types, $values] = $this->applyFilter($filter, [], ['integer', 'integer'], [$test_id, $test_id]);

        if (!empty($where)) {
            $where = join(' AND ', $where);
            $query .= " WHERE $where";
        }

        $statement = $this->database->queryF($query, $types, $values);
        $result = $this->database->fetchAssoc($statement);

        return $result['number_of_participants'] ?? 0;
    }

    /**
     * @param array<string, mixed> $filter
     */
    public function getParticipants(int $test_id, array $filter, ?Range $range, Order $order): \Generator
    {
        $query = $this->getBaseQuery();
        [$where, $types, $values] = $this->applyFilter(
            $filter,
            [],
            ['integer', 'integer'],
            [$test_id, $test_id]
        );

        if (!empty($where)) {
            $where = join(' AND ', $where);
            $query .= " WHERE $where";
        }

        $orderBy = $this->applyOrder($order);

        if ($orderBy) {
            $query .= " ORDER BY $orderBy";
        }
        if ($range) {
            $query .= " LIMIT {$range->getStart()}, {$range->getLength()}";
        }

        $statement = $this->database->queryF($query, $types, $values);

        while ($row = $this->database->fetchAssoc($statement)) {
            yield $this->arrayToObject($row);
        }
    }

    public function getParticipantByActiveId(int $test_id, int $active_id): ?Participant
    {
        return $this->fetchParticipant(
            "{$this->getBaseQuery()} WHERE active_id = %s",
            ['integer', 'integer', 'integer'],
            [$test_id, $test_id, $active_id]
        );
    }

    public function getParticipantByUserId(int $test_id, int $user_id): ?Participant
    {
        return $this->fetchParticipant(
            "{$this->getBaseQuery()} WHERE user_fi = %s",
            ['integer', 'integer', 'integer'],
            [$test_id, $test_id, $user_id]
        );
    }

    public function updateExtraTime(Participant $participant): void
    {
        $this->database->manipulatef(
            "INSERT INTO tst_addtime (user_fi, test_fi, additionaltime, tstamp) VALUES (%s, %s, %s, %s)
                        ON DUPLICATE KEY UPDATE tstamp = %s, additionaltime = %s",
            ['integer', 'integer', 'integer','timestamp','timestamp', 'integer'],
            [$participant->getUsrId(), $participant->getTestId(), $participant->getExtraTime(), time(), time(), $participant->getExtraTime()]
        );
    }

    /**
     * @param array<Participant> $participants
     */
    public function updateIpRange(array $participants): void
    {
        foreach ($participants as $participant) {
            $this->database->replace(
                [
                    'test_fi' => [\ilDBConstants::T_INTEGER, $participant->getTestId()],
                    'user_fi' => [\ilDBConstants::T_INTEGER, $participant->getUserId]
                ],
                [
                    'ip_range_to' => $participant->getClientIpFrom(),
                    'ip_range_to' => $participant->getClientIpTo(),
                    'tstamp' => time()
                ]
            );
        }
    }

    protected function loadTestStartTime(?int $active_id, int $pass): ?\DateTimeImmutable
    {
        if (!$active_id) {
            return null;
        }

        $statement = $this->database->queryF(
            'SELECT started FROM tst_times WHERE active_fi = %s AND pass = %s ORDER BY started ASC LIMIT 1',
            ['integer', 'integer'],
            [$active_id, $pass]
        );

        $row = $this->database->fetchAssoc($statement);

        if (!$row) {
            return null;
        }

        return new \DateTimeImmutable($row['started']);
    }

    protected function loadTestEndTime(?int $active_id, int $pass): ?\DateTimeImmutable
    {
        if (!$active_id) {
            return null;
        }

        $statement = $this->database->queryF(
            'SELECT finished FROM tst_times WHERE active_fi = %s AND pass = %s ORDER BY started DESC LIMIT 1',
            ['integer', 'integer'],
            [$active_id, $pass]
        );

        $row = $this->database->fetchAssoc($statement);

        if (!$row) {
            return null;
        }

        return new \DateTimeImmutable($row['finished']);
    }

    protected function loadHasSolutions(?int $active_id): bool
    {
        $statement = $this->database->queryF(
            'SELECT MAX(answeredquestions) as answeredquestions FROM tst_pass_result WHERE active_fi = %s',
            ['integer'],
            [$active_id]
        );

        $row = $this->database->fetchAssoc($statement);
        if (!$row) {
            return false;
        }

        return $row['answeredquestions'] > 0;
    }

    /**
     * @param array<int, string> $types
     * @param array<int, mixed> $values
     */
    private function fetchParticipant(string $query, array $types, array $values): ?Participant
    {
        $statement = $this->database->queryF($query, $types, $values);
        $row = $this->database->fetchAssoc($statement);

        if (!$row) {
            return null;
        }

        return $this->arrayToObject($row);
    }

    /**
     * @param array<string, mixed> $filter
     * @param array<int, string> $where
     * @param array<int, string> $types
     * @param array<int, mixed> $values
     *
     * @return array
     */
    private function applyFilter(array $filter, array $where, array $types, array $values): array
    {
        if ($this->isFilterSet($filter, 'name')) {
            $where[] = '(firstname LIKE %s OR lastname LIKE %s)';
            $types = array_merge($types, ['string', 'string']);
            $values = array_merge($values, ["%{$filter['name']}%", "%{$filter['name']}%"]);
        }

        if ($this->isFilterSet($filter, 'login')) {
            $where[] = '(login LIKE %s)';
            $types = array_merge($types, ['string']);
            $values = array_merge($values, ["%{$filter['login']}%"]);
        }

        if ($this->isFilterSet($filter, 'extra_time')) {
            if ($filter['extra_time'] === 'true') {
                $where[] = '(extra_time > 0 AND extra_time IS NOT NULL)';
            } else {
                $where[] = '(extra_time = 0 OR extra_time IS NULL)';
            }
        }

        if ($this->isFilterSet($filter, 'ip_range')) {
            $where[] = '(ip_range_from LIKE %s OR ip_range_to LIKE %s)';
            $types = array_merge($types, ['string', 'string']);
            $values = array_merge($values, ["%{$filter['ip_range']}%", "%{$filter['ip_range']}%"]);
        }

        return [$where, $types, $values];
    }

    private function applyOrder(Order $order): string
    {
        $orderBy = [];
        foreach ($order->get() as $subject => $direction) {
            $orderBy[] = match ($subject) {
                'name' => "lastname $direction, firstname $direction",
                'ip_range' => "ip_range_from $direction, ip_range_to $direction",
                'total_attempts' => "tries $direction",
                'extra_time' => "extra_time $direction",
                default => null
            };
        }
        return trim(join(', ', array_filter($orderBy)));
    }

    private function isFilterSet(array $filter, string $key): bool
    {
        return isset($filter[$key]) && trim($filter[$key]) !== "";
    }


    private function getBaseQuery(): string
    {
        return "
            SELECT particpants.*
            FROM (
                ({$this->getActiveParticipantsQuery()})
                UNION
                ({$this->getInvitedParticipantsQuery()})
            ) as particpants
        ";
    }

    private function arrayToObject(array $row): Participant
    {
        return new Participant(
            $row['user_fi'],
            $row['active_id'],
            $row['test_fi'],
            $row['anonymous_id'],
            $row['firstname'] ?? '',
            $row['lastname'] ?? '',
            $row['login'] ?? '',
            $row['matriculation'] ?? '',
            $row['extra_time'] ?? 0,
            $row['tries'],
            $row['ip_range_from'],
            $row['ip_range_to'],
            $row['invitation_date'],
            $row['submitted'] === 1,
            $row['last_started_pass'],
            $row['last_finished_pass'],
            $row['unfinished_attempts'] === 1,
            $row['first_access'] === null ? null : new \DateTimeImmutable($row['first_access']),
            $row['last_access'] === null ? null : new \DateTimeImmutable($row['last_access'])
        );
    }


    /**
     * @return string
     */
    private function getActiveParticipantsQuery(): string
    {
        return "
            SELECT      ta.active_id,
						ta.user_fi,
						ta.test_fi,
						ta.anonymous_id,
						ta.tries,
						ta.submitted,
						ta.last_finished_pass,
						ta.last_started_pass,
						COALESCE(ta.last_started_pass, -1) <> COALESCE(ta.last_finished_pass, -1) as unfinished_attempts,
						ud.firstname,
						ud.lastname,
						ud.login,
						ud.matriculation,
                        (SELECT MIN(started) FROM tst_times WHERE active_fi = ta.active_id) as first_access,
						(SELECT MAX(finished) FROM tst_times WHERE active_fi = ta.active_id) as last_access,
						tatime.additionaltime extra_time,
			            tinvited.ip_range_from,
			            tinvited.ip_range_to,
                        tinvited.tstamp as invitation_date
			FROM		tst_active ta
			LEFT JOIN	usr_data ud
			ON 			ud.usr_id = ta.user_fi
			LEFT JOIN   tst_addtime tatime
			ON			tatime.user_fi = ta.user_fi
            AND         tatime.test_fi = ta.test_fi
            LEFT JOIN   tst_invited_user tinvited
			ON          tinvited.test_fi = ta.test_fi
            AND         tinvited.user_fi = ta.user_fi
			WHERE		ta.test_fi = %s
        ";
    }

    /**
     * @return string
     */
    private function getInvitedParticipantsQuery(): string
    {
        return "
            SELECT      ta.active_id,
						tinvited.user_fi,
						tinvited.test_fi,
						ta.anonymous_id,
						ta.tries,
						ta.submitted,
						ta.last_finished_pass,
						ta.last_started_pass,
						COALESCE(ta.last_started_pass, -1) <> COALESCE(ta.last_finished_pass, -1) as unfinished_attempts,
						ud.firstname,
						ud.lastname,
						ud.login,
						ud.matriculation,
                        NULL as first_access,
                        NULL as last_access,
						tatime.additionaltime extra_time,
			            tinvited.ip_range_from,
			            tinvited.ip_range_to,
                        tinvited.tstamp as invitation_date
			FROM		tst_invited_user tinvited
			LEFT JOIN	usr_data ud
			ON 			ud.usr_id = tinvited.user_fi
			LEFT JOIN   tst_addtime tatime
			ON			tatime.user_fi = tinvited.user_fi
            AND         tatime.test_fi = tinvited.test_fi
            LEFT JOIN   tst_active ta
			ON          tinvited.test_fi = ta.test_fi
            AND         tinvited.user_fi = ta.user_fi
			WHERE		tinvited.test_fi = %s AND ta.active_id IS NULL
        ";
    }
}
