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
use ilTestParticipant;

use function array_keys;
use function ILIAS\UI\examples\Table\Action\Multi\getExampleTable;
use function time;

class ParticipantRepository
{
    private \ilDBInterface $database;

    public function __construct(\ilDBInterface $db)
    {
        $this->database = $db;
    }

    public function lookupTestIdByActiveId(int $active_id): int
    {
        $result = $this->database->queryF(
            "SELECT test_fi FROM tst_active WHERE active_id = %s",
            ['integer'],
            [$active_id]
        );
        $test_id = -1;
        if ($this->database->numRows($result) > 0) {
            $row = $this->database->fetchAssoc($result);
            $test_id = (int) $row["test_fi"];
        }

        return $test_id;
    }

    public function countParticipants(int $test_id): int
    {
        $query = "
			SELECT COUNT(ta.active_id) as number_of_participants
			FROM		tst_active ta
			WHERE		test_fi = %s
		";

        $statement = $this->database->queryF($query, ['integer'], [$test_id]);
        $result = $this->database->fetchAssoc($statement);

        return $result['number_of_participants'] ?? 0;
    }

    public function loadParticipants(int $test_id): \Generator
    {
        $query_invited_participants = "
            SELECT      ta.active_id,
						tinvited.user_fi,
						tinvited.test_fi,
						ta.anonymous_id,
						ta.tries,
						ud.firstname,
						ud.lastname,
						ud.login,
						ud.matriculation,
						0 as additionaltime,
			            tinvited.ip_range_from,
			            tinvited.ip_range_to,
                        tinvited.tstamp as invitation_date
			FROM		tst_invited_user tinvited
			LEFT JOIN	usr_data ud
			ON 			ud.usr_id = tinvited.user_fi
            LEFT JOIN   tst_active ta
			ON          tinvited.test_fi = ta.test_fi
            AND         tinvited.user_fi = ta.user_fi
			WHERE		tinvited.test_fi = %s AND ta.active_id IS NULL
        ";
        $query_active_participants = "
            SELECT      ta.active_id,
						ta.user_fi,
						ta.test_fi,
						ta.anonymous_id,
						ta.tries,
						ud.firstname,
						ud.lastname,
						ud.login,
						ud.matriculation,
						ttime.additionaltime,
			            tinvited.ip_range_from,
			            tinvited.ip_range_to,
                        tinvited.tstamp as invitation_date
			FROM		tst_active ta
			LEFT JOIN	usr_data ud
			ON 			ud.usr_id = ta.user_fi
			LEFT JOIN   tst_addtime ttime
			ON			ttime.active_fi = ta.active_id
            LEFT JOIN   tst_invited_user tinvited
			ON          tinvited.test_fi = ta.test_fi
            AND         tinvited.user_fi = ta.user_fi
			WHERE		ta.test_fi = %s        
        ";

        $query = "
            SELECT  participants.active_id,
                    participants.user_fi,
                    participants.test_fi,
                    participants.anonymous_id,
                    participants.tries,
                    participants.firstname,
                    participants.lastname,
                    participants.login,
                    participants.matriculation,
                    participants.additionaltime,
                    participants.ip_range_from,
                    participants.ip_range_to,
                    participants.invitation_date
            FROM (
                $query_active_participants
                UNION
                $query_invited_participants
            ) as participants
            ORDER BY participants.lastname, participants.firstname
        ";


        //        $query = "
        //			SELECT		active_id,
        //						user_fi,
        //						test_fi,
        //						anonymous_id,
        //						tries,
        //						firstname,
        //						lastname,
        //						login,
        //						matriculation,
        //						additionaltime,
        //			            ip_range_from,
        //			            ip_range_to
        //			FROM		tst_active ta
        //			LEFT JOIN	usr_data ud
        //			ON 			ud.usr_id = ta.user_fi
        //			LEFT JOIN   tst_addtime ttime
        //			ON			ttime.active_fi = ta.active_id
        //            LEFT JOIN   tst_invited_user tinvited
        //			ON          tinvited.test_fi = ta.test_fi
        //            AND         tinvited.user_fi = ta.user_fi
        //			WHERE		ta.test_fi = %s
        //		";

        $statement = $this->database->queryF($query, ['integer', 'integer'], [$test_id, $test_id]);

        while ($row = $this->database->fetchAssoc($statement)) {
            yield $this->arrayToObject($row);
        }
    }

    public function loadParticipantByActiveId(int $active_id): ?Participant
    {
        $query = "
			SELECT		ta.active_id,
						ta.user_fi,
						ta.test_fi,
						ta.anonymous_id,
						ta.tries,
						ud.firstname,
						ud.lastname,
						ud.login,
						ud.matriculation,
						ttime.additionaltime,
			            tinvited.ip_range_from,
			            tinvited.ip_range_to
			FROM		tst_active ta
			LEFT JOIN	usr_data ud
			ON 			ud.usr_id = ta.user_fi
			LEFT JOIN   tst_addtime ttime
			ON			ttime.active_fi = ta.active_id
			LEFT JOIN   tst_invited_user tinvited
			ON          tinvited.test_fi = ta.test_fi
            AND         tinvited.user_fi = ta.user_fi
			WHERE		ta.active_id = %s
		";

        $statement = $this->database->queryF($query, ['integer'], [$active_id]);
        $row = $this->database->fetchAssoc($statement);

        if ($row === false) {
            return null;
        }

        return $this->arrayToObject($row);
    }

    private function arrayToObject(array $row): Participant
    {
        $participant = new Participant();
        $participant->setActiveId((int) $row['active_id']);
        $participant->setUsrId((int) $row['user_fi']);
        $participant->setTestId((int) $row['test_fi']);
        $participant->setAnonymousId((int) $row['anonymous_id']);
        $participant->setFirstname($row['firstname']);
        $participant->setLastname($row['lastname']);
        $participant->setLogin($row['login']);
        $participant->setMatriculation($row['matriculation']);
        $participant->setExtraTime((int) $row['additionaltime']);
        $participant->setTries((int) $row['tries']);
        $participant->setClientIpFrom($row['ip_range_from']);
        $participant->setClientIpTo($row['ip_range_to']);
        $participant->setInvitationDate((int) $row['invitation_date']);

        return $participant;
    }

    /**
     * @param array<Participant> $participants
     * @param int   $minutes
     *
     * @return void
     */
    public function updateExtraTime(array $participants, int $minutes): void
    {
        foreach ($participants as $participant) {
            $participant->setExtraTime($participant->getExtraTime() + $minutes);


            $this->database->manipulatef(
                "INSERT INTO tst_addtime (active_fi, additionaltime, tstamp) VALUES (%s, %s, %s) 
                        ON DUPLICATE KEY UPDATE tstamp = %s, additionaltime = %s",
                ['integer', 'integer','timestamp','timestamp', 'integer'],
                [$participant->getActiveId(), $participant->getExtraTime(), time(), time(), $participant->getExtraTime()]
            );
        }
    }

    /**
     * @param array<Participant>        $participants
     * @param array{from: string, to: string} $ip_range
     *
     * @return void
     */
    public function updateIpRange(array $participants, array $ip_range): void
    {
        foreach ($participants as $participant) {
            $participant->setClientIpFrom($ip_range['from']);
            $participant->setClientIpTo($ip_range['to']);

            $this->database->manipulatef(
                "INSERT INTO tst_invited_user (test_fi, user_fi, ip_range_from, ip_range_to, tstamp) VALUES (%s, %s, %s, %s, %s) 
                        ON DUPLICATE KEY UPDATE ip_range_from = %s, ip_range_to = %s, tstamp = %s",
                [
                    'integer',
                    'integer',
                    'text',
                    'text',
                    'timestamp',
                    'text',
                    'text',
                    'timestamp',
                ],
                [
                    $participant->getTestId(),
                    $participant->getUsrId(),
                    $participant->getClientIpFrom(),
                    $participant->getClientIpTo(),
                    time(),
                    $participant->getClientIpFrom(),
                    $participant->getClientIpTo(),
                    time()
                ]
            );
        }
    }

    public function invite(int $test_id, int $usr_id): void
    {
        $this->database->manipulatef(
            "INSERT INTO tst_invited_user (test_fi, user_fi, tstamp) VALUES (%s, %s, %s) 
                    ON Duplicate KEY UPDATE tstamp = %s",
            ['integer', 'integer', 'timestamp', 'timestamp'],
            [$test_id, $usr_id, time(), time()]
        );
    }
}
