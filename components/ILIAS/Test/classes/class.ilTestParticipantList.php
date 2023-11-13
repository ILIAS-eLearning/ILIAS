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
 * Class ilTestParticipantList
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components\ILIAS/Test
 * @implements Iterator<ilTestParticipant>
 */
class ilTestParticipantList implements Iterator
{
    /**
     * @var array<ilTestParticipant>
     */
    protected array $participants = [];

    public function __construct(
        private ilObjTest $test_obj,
        private ilObjUser $user,
        private ilLanguage $lng,
        private ilDBInterface $db
    ) {
    }

    private function getTestObj(): ilObjTest
    {
        return $this->test_obj;
    }

    public function setTestObj(ilObjTest $test_obj): void
    {
        $this->test_obj = $test_obj;
    }

    public function addParticipant(ilTestParticipant $participant): void
    {
        $this->participants[] = $participant;
    }

    public function getParticipantByUsrId(int $usr_id): ?ilTestParticipant
    {
        foreach ($this as $participant) {
            if ($participant->getUsrId() != $usr_id) {
                continue;
            }

            return $participant;
        }
        return null;
    }

    public function getParticipantByActiveId($active_id): ?ilTestParticipant
    {
        foreach ($this as $participant) {
            if ($participant->getActiveId() != $active_id) {
                continue;
            }

            return $participant;
        }
        return null;
    }

    public function hasUnfinishedPasses(): bool
    {
        foreach ($this as $participant) {
            if ($participant->hasUnfinishedPasses()) {
                return true;
            }
        }

        return false;
    }

    public function hasScorings(): bool
    {
        foreach ($this as $participant) {
            if ($participant->getScoring() instanceof ilTestParticipantScoring) {
                return true;
            }
        }

        return false;
    }

    public function getAllUserIds(): array
    {
        $usrIds = array();

        foreach ($this as $participant) {
            $usrIds[] = $participant->getUsrId();
        }

        return $usrIds;
    }

    public function getAllActiveIds(): array
    {
        $activeIds = array();

        foreach ($this as $participant) {
            $activeIds[] = $participant->getActiveId();
        }

        return $activeIds;
    }

    public function isActiveIdInList(int $active_id): bool
    {
        foreach ($this as $participant) {
            if ($participant->getActiveId() == $active_id) {
                return true;
            }
        }

        return false;
    }

    public function getAccessFilteredList(Closure $user_access_filter): ilTestParticipantList
    {
        $usr_ids = $user_access_filter($this->getAllUserIds());

        $access_filtered_list = new self($this->getTestObj(), $this->user, $this->lng, $this->db);

        foreach ($this as $participant) {
            if (in_array($participant->getUsrId(), $usr_ids)) {
                $participant = clone $participant;
                $access_filtered_list->addParticipant($participant);
            }
        }

        return $access_filtered_list;
    }

    public function current(): ilTestParticipant
    {
        return current($this->participants);
    }
    public function next(): void
    {
        next($this->participants);
    }
    public function key(): int
    {
        return key($this->participants);
    }
    public function valid(): bool
    {
        return key($this->participants) !== null;
    }
    public function rewind(): void
    {
        reset($this->participants);
    }

    public function initializeFromDbRows(array $db_rows): void
    {
        foreach ($db_rows as $row_data) {
            $participant = new ilTestParticipant();

            if ((int) $row_data['active_id']) {
                $participant->setActiveId((int) $row_data['active_id']);
            }

            $participant->setUsrId((int) $row_data['usr_id']);

            $participant->setLogin($row_data['login'] ?? '');
            $participant->setLastname($row_data['lastname']);
            $participant->setFirstname($row_data['firstname'] ?? '');
            $participant->setMatriculation($row_data['matriculation'] ?? '');

            $participant->setActiveStatus((bool) ($row_data['active'] ?? false));

            if (isset($row_data['clientip'])) {
                $participant->setClientIp($row_data['clientip']);
            }

            $participant->setFinishedTries((int) $row_data['tries']);
            $participant->setTestFinished((bool) $row_data['test_finished']);
            $participant->setUnfinishedPasses((bool) $row_data['unfinished_passes']);

            $this->addParticipant($participant);
        }
    }

    public function getScoredParticipantList(): ilTestParticipantList
    {
        $scored_participant_list = new self($this->getTestObj(), $this->user, $this->lng, $this->db);

        $res = $this->db->query($this->buildScoringsQuery());

        while ($row = $this->db->fetchAssoc($res)) {
            $scoring = new ilTestParticipantScoring();

            $scoring->setActiveId((int) $row['active_fi']);
            $scoring->setScoredPass((int) $row['pass']);

            $scoring->setAnsweredQuestions((int) $row['answeredquestions']);
            $scoring->setTotalQuestions((int) $row['questioncount']);

            $scoring->setReachedPoints((float) $row['reached_points']);
            $scoring->setMaxPoints((float) $row['max_points']);

            $scoring->setPassed((bool) $row['passed']);
            $scoring->setFinalMark((string) $row['mark_short']);

            $this->getParticipantByActiveId($row['active_fi'])->setScoring($scoring);

            $scored_participant_list->addParticipant(
                $this->getParticipantByActiveId($row['active_fi'])
            );
        }

        return $scored_participant_list;
    }

    public function buildScoringsQuery(): string
    {
        $IN_activeIds = $this->db->in(
            'tres.active_fi',
            $this->getAllActiveIds(),
            false,
            'integer'
        );

        $query = "
			SELECT * FROM tst_result_cache tres

			INNER JOIN tst_pass_result pres
			ON pres.active_fi = tres.active_fi
			AND pres.pass = tres.pass
			WHERE $IN_activeIds
		";

        return $query;
    }

    public function getParticipantsTableRows(): array
    {
        $rows = [];

        foreach ($this as $participant) {
            $row = [
                'usr_id' => $participant->getUsrId(),
                'active_id' => $participant->getActiveId(),
                'login' => $participant->getLogin(),
                'clientip' => $participant->getClientIp(),
                'firstname' => $participant->getFirstname(),
                'lastname' => $participant->getLastname(),
                'name' => $this->buildFullname($participant),
                'started' => ($participant->getActiveId() > 0) ? 1 : 0,
                'unfinished' => $participant->hasUnfinishedPasses() ? 1 : 0,
                'finished' => $participant->isTestFinished() ? 1 : 0,
                'access' => $this->lookupLastAccess($participant->getActiveId()),
                'tries' => $this->lookupNrOfTries($participant->getActiveId())
            ];

            $rows[] = $row;
        }

        return $rows;
    }

    public function getScoringsTableRows(): array
    {
        $rows = [];

        foreach ($this as $participant) {
            if (!$participant->hasScoring()) {
                continue;
            }

            $row = [
                'usr_id' => $participant->getUsrId(),
                'active_id' => $participant->getActiveId(),
                'login' => $participant->getLogin(),
                'firstname' => $participant->getFirstname(),
                'lastname' => $participant->getLastname(),
                'name' => $this->buildFullname($participant)
            ];

            if ($participant->getScoring()) {
                $row['scored_pass'] = $participant->getScoring()->getScoredPass();
                $row['answered_questions'] = $participant->getScoring()->getAnsweredQuestions();
                $row['total_questions'] = $participant->getScoring()->getTotalQuestions();
                $row['reached_points'] = $participant->getScoring()->getReachedPoints();
                $row['max_points'] = $participant->getScoring()->getMaxPoints();
                $row['percent_result'] = $participant->getScoring()->getPercentResult();
                $row['passed_status'] = $participant->getScoring()->isPassed();
                $row['final_mark'] = $participant->getScoring()->getFinalMark();
                $row['scored_pass_finished_timestamp'] = ilObjTest::lookupLastTestPassAccess(
                    $participant->getActiveId(),
                    $participant->getScoring()->getScoredPass()
                );
                $row['finished_passes'] = $participant->getFinishedTries();
                $row['has_unfinished_passes'] = $participant->hasUnfinishedPasses();
            }

            $rows[] = $row;
        }

        return $rows;
    }

    public function lookupNrOfTries(?int $active_id): ?int
    {
        if ($active_id === null) {
            return null;
        }

        $max_pass_index = ilObjTest::_getMaxPass($active_id);

        if ($max_pass_index === null) {
            return null;
        }

        return $max_pass_index + 1;
    }

    protected function lookupLastAccess(?int $active_id): string
    {
        if ($active_id === null) {
            return '';
        }

        return $this->getTestObj()->_getLastAccess($active_id);
    }

    protected function buildFullname(ilTestParticipant $participant): string
    {
        if ($this->getTestObj()->getMainSettings()->getAccessSettings()->getFixedParticipants() && !$participant->getActiveId()) {
            return $this->buildInviteeFullname($participant);
        }

        return $this->buildParticipantsFullname($participant);
    }

    protected function buildInviteeFullname(ilTestParticipant $participant): string
    {
        if (strlen($participant->getFirstname() . $participant->getLastname()) == 0) {
            return $this->lng->txt("deleted_user");
        }

        if ($this->getTestObj()->getAnonymity()) {
            return $this->lng->txt('anonymous');
        }

        return trim($participant->getLastname() . ", " . $participant->getFirstname());
    }

    protected function buildParticipantsFullname(ilTestParticipant $participant): string
    {
        return ilObjTestAccess::_getParticipantData($participant->getActiveId());
    }
}
