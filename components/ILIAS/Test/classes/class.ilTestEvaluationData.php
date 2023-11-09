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
* Class ilTestEvaluationData
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @author		Björn Heyser <bheyser@databay.de>
* @version		$Id$
*
* @defgroup ModulesTest Modules/Test
* @extends ilObject
*/

class ilTestEvaluationData
{
    public const FILTER_BY_NONE = '';
    public const FILTER_BY_NAME = 'name';
    public const FILTER_BY_GROUP = 'group';
    public const FILTER_BY_COURSE = 'course';
    public const FILTER_BY_ACTIVE_ID = 'active_id';

    public array $question_titles;

    /**
    * @var array<ilTestEvaluationUserData>
    */
    protected $participants;
    protected $statistics;
    protected ?array $arr_filter = null;
    protected int $datasets;
    protected ?ilTestParticipantList $access_filtered_participant_list = null;

    public function __sleep(): array
    {
        return ['question_titles', 'participants', 'statistics', 'arr_filter', 'datasets', 'test'];
    }

    public function __construct(
        protected ilDBInterface $db,
        protected ?ilObjTest $test = null
    ) {
        $this->participants = [];
        $this->question_titles = [];
        if ($test !== null) {
            if ($this->getTest()->getAccessFilteredParticipantList()) {
                $this->setAccessFilteredParticipantList(
                    $this->getTest()->getAccessFilteredParticipantList()
                );
            }

            $this->generateOverview();
        }
    }

    public function getAccessFilteredParticipantList(): ?ilTestParticipantList
    {
        return $this->access_filtered_participant_list;
    }

    public function setAccessFilteredParticipantList(ilTestParticipantList $access_filtered_participant_list)
    {
        $this->access_filtered_participant_list = $access_filtered_participant_list;
    }

    protected function checkParticipantAccess($activeId): bool
    {
        if ($this->getAccessFilteredParticipantList() === null) {
            return true;
        }

        return $this->getAccessFilteredParticipantList()->isActiveIdInList($activeId);
    }

    protected function loadRows(): array
    {
        $query = '
			SELECT			usr_data.usr_id,
							usr_data.firstname,
							usr_data.lastname,
							usr_data.title,
							usr_data.login,
							tst_pass_result.*,
							tst_active.submitted,
							tst_active.last_finished_pass
			FROM			tst_pass_result, tst_active
			LEFT JOIN		usr_data
			ON				tst_active.user_fi = usr_data.usr_id
			WHERE			tst_active.active_id = tst_pass_result.active_fi
			AND				tst_active.test_fi = %s
			ORDER BY		usr_data.lastname,
							usr_data.firstname,
							tst_pass_result.active_fi,
							tst_pass_result.pass,
							tst_pass_result.tstamp
		';

        $result = $this->db->queryF(
            $query,
            ['integer'],
            [$this->getTest()->getTestId()]
        );

        $rows = [];

        while ($row = $this->db->fetchAssoc($result)) {
            if (!$this->checkParticipantAccess($row['active_fi'])) {
                continue;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    public function generateOverview()
    {
        $this->participants = [];

        $pass = null;
        $thissets = 0;

        foreach ($this->loadRows() as $row) {
            $thissets++;

            $remove = false;

            if (!$this->participantExists($row['active_fi'])) {
                $this->addParticipant($row['active_fi'], new ilTestEvaluationUserData($this->getTest()->getPassScoring()));

                $this->getParticipant($row['active_fi'])->setName(
                    $this->getTest()->buildName($row['usr_id'], $row['firstname'], $row['lastname'], $row['title'])
                );

                $this->getParticipant($row['active_fi'])->setLogin($row['login']);

                $this->getParticipant($row['active_fi'])->setUserID($row['usr_id']);

                $this->getParticipant($row['active_fi'])->setSubmitted((bool) $row['submitted']);

                $this->getParticipant($row['active_fi'])->setLastFinishedPass($row['last_finished_pass']);
            }

            if (!is_object($this->getParticipant($row['active_fi'])->getPass($row['pass']))) {
                $pass = new ilTestEvaluationPassData();
                $pass->setPass($row['pass']);
                $this->getParticipant($row['active_fi'])->addPass($row['pass'], $pass);
            }

            $this->getParticipant($row['active_fi'])->getPass($row['pass'])->setReachedPoints($row['points']);
            $this->getParticipant($row['active_fi'])->getPass($row['pass'])->setObligationsAnswered((bool) $row['obligations_answered']);

            if ($row['questioncount'] == 0) {
                $data = ilObjTest::_getQuestionCountAndPointsForPassOfParticipant($row['active_fi'], $row['pass']);
                $this->getParticipant($row['active_fi'])->getPass($row['pass'])->setMaxPoints($data['points']);
                $this->getParticipant($row['active_fi'])->getPass($row['pass'])->setQuestionCount($data['count']);
            } else {
                $this->getParticipant($row['active_fi'])->getPass($row['pass'])->setMaxPoints($row['maxpoints']);
                $this->getParticipant($row['active_fi'])->getPass($row['pass'])->setQuestionCount($row['questioncount']);
            }

            $this->getParticipant($row['active_fi'])->getPass($row['pass'])->setNrOfAnsweredQuestions($row['answeredquestions']);
            $this->getParticipant($row['active_fi'])->getPass($row['pass'])->setWorkingTime($row['workingtime']);
            $this->getParticipant($row['active_fi'])->getPass($row['pass'])->setExamId((string) $row['exam_id']);

            $this->getParticipant($row['active_fi'])->getPass($row['pass'])->setRequestedHintsCount($row['hint_count']);
            $this->getParticipant($row['active_fi'])->getPass($row['pass'])->setDeductedHintPoints($row['hint_points']);
        }
    }

    public function getTest(): ilObjTest
    {
        return $this->test;
    }

    public function setTest($test)
    {
        $this->test = &$test;
    }

    public function setDatasets(int $datasets)
    {
        $this->datasets = $datasets;
    }

    public function getDatasets(): int
    {
        return $this->datasets;
    }

    public function addQuestionTitle($question_id, $question_title)
    {
        $this->question_titles[$question_id] = $question_title;
    }

    public function getQuestionTitles(): array
    {
        return $this->question_titles;
    }

    public function getQuestionTitle($question_id)
    {
        if (array_key_exists($question_id, $this->question_titles)) {
            return $this->question_titles[$question_id];
        } else {
            return '';
        }
    }

    public function calculateStatistics()
    {
        $this->statistics = new ilTestStatistics($this);
    }

    public function getTotalFinishedParticipants(): int
    {
        $finishedParticipants = 0;

        foreach ($this->participants as $active_id => $participant) {
            if (!$participant->isSubmitted()) {
                continue;
            }

            $finishedParticipants++;
        }

        return $finishedParticipants;
    }

    public function getParticipants(): array
    {
        if (is_array($this->arr_filter) && count($this->arr_filter) > 0) {
            $filtered_participants = [];
            $courseids = [];
            $groupids = [];

            if (array_key_exists(self::FILTER_BY_GROUP, $this->arr_filter)) {
                $ids = ilObject::_getIdsForTitle($this->arr_filter[self::FILTER_BY_GROUP], 'grp', true);
                $groupids = array_merge($groupids, $ids);
            }
            if (array_key_exists(self::FILTER_BY_COURSE, $this->arr_filter)) {
                $ids = ilObject::_getIdsForTitle($this->arr_filter[self::FILTER_BY_COURSE], 'crs', true);
                $courseids = array_merge($courseids, $ids);
            }
            foreach ($this->participants as $active_id => $participant) {
                $remove = false;
                if (array_key_exists(self::FILTER_BY_NAME, $this->arr_filter)) {
                    if (!(strpos(strtolower($participant->getName()), strtolower((string) $this->arr_filter[self::FILTER_BY_NAME])) !== false)) {
                        $remove = true;
                    }
                }
                if (!$remove) {
                    if (array_key_exists(self::FILTER_BY_GROUP, $this->arr_filter)) {
                        $groups = ilParticipants::_getMembershipByType($participant->getUserID(), ['grp']);
                        $foundfilter = false;
                        if (count(array_intersect($groupids, $groups))) {
                            $foundfilter = true;
                        }
                        if (!$foundfilter) {
                            $remove = true;
                        }
                    }
                }
                if (!$remove) {
                    if (array_key_exists(self::FILTER_BY_COURSE, $this->arr_filter)) {
                        $courses = ilParticipants::_getMembershipByType($participant->getUserID(), ['crs']);
                        $foundfilter = false;
                        if (count(array_intersect($courseids, $courses))) {
                            $foundfilter = true;
                        }
                        if (!$foundfilter) {
                            $remove = true;
                        }
                    }
                }
                if (!$remove) {
                    if (array_key_exists(self::FILTER_BY_ACTIVE_ID, $this->arr_filter)) {
                        if ($active_id != $this->arr_filter[self::FILTER_BY_ACTIVE_ID]) {
                            $remove = true;
                        }
                    }
                }
                if (!$remove) {
                    $filtered_participants[$active_id] = $participant;
                }
            }
            return $filtered_participants;
        } else {
            return $this->participants;
        }
    }

    public function resetFilter()
    {
        $this->arr_filter = [];
    }

    /*
    * Set an output filter for getParticipants
    *
    * @param string $by name, course, group, active_id
    * @param string $text Filter text
    */
    public function setFilter(string $by, string $text): void
    {
        if (in_array(
            $by,
            [self::FILTER_BY_ACTIVE_ID, self::FILTER_BY_NAME, self::FILTER_BY_COURSE, self::FILTER_BY_GROUP],
            true
        )) {
            $this->arr_filter = [$by => $text];
        }
    }

    /*
    * Set an output filter for getParticipants
    */
    public function setFilterArray(array $arr_filter): void
    {
        $this->arr_filter = $arr_filter;
    }

    public function addParticipant($active_id, $participant)
    {
        $this->participants[$active_id] = $participant;
    }

    /**
     * @param integer $active_id
     * @return ilTestEvaluationUserData
     */
    public function getParticipant($active_id): ilTestEvaluationUserData
    {
        return $this->participants[$active_id];
    }

    public function participantExists($active_id): bool
    {
        return array_key_exists($active_id, $this->participants);
    }

    public function removeParticipant($active_id)
    {
        unset($this->participants[$active_id]);
    }

    public function getStatistics(): object
    {
        return $this->statistics;
    }

    public function getParticipantIds(): array
    {
        return array_keys($this->participants);
    }
} // END ilTestEvaluationData
