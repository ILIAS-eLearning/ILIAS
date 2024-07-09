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

use ILIAS\Test\TestManScoringDoneHelper;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

/**
 * Service class for tests.
 *
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @ingroup components\ILIASTest
 */
class ilTestService
{
    /**
     * @access public
     * @param	ilObjTest $a_object
     */
    public function __construct(
        protected ilObjTest $object,
        protected ilDBInterface $db,
        protected GeneralQuestionPropertiesRepository $questionrepository
    ) {
    }

    public function getPassOverviewData(int $active_id, bool $short = false): array
    {
        $passOverwiewData = [];

        $scoredPass = $this->object->_getResultPass($active_id);
        $lastPass = ilObjTest::_getPass($active_id);

        $testPercentage = 0;
        $testReachedPoints = 0;
        $testMaxPoints = 0;

        for ($pass = 0; $pass <= $lastPass; $pass++) {
            $passFinishDate = ilObjTest::lookupPassResultsUpdateTimestamp($active_id, $pass);

            if ($passFinishDate <= 0) {
                continue;
            }

            if (!$short) {
                $result_data = $this->object->getTestResult($active_id, $pass);

                if (!$result_data["pass"]["total_max_points"]) {
                    $passPercentage = 0;
                } else {
                    $passPercentage = ($result_data["pass"]["total_reached_points"] / $result_data["pass"]["total_max_points"]) * 100;
                }

                $passMaxPoints = $result_data["pass"]["total_max_points"];
                $passReachedPoints = $result_data["pass"]["total_reached_points"];

                $passAnsweredQuestions = $this->object->getAnsweredQuestionCount($active_id, $pass);
                $passTotalQuestions = count($result_data) - 2;

                if ($pass == $scoredPass) {
                    $isScoredPass = true;

                    if (!$result_data["test"]["total_max_points"]) {
                        $testPercentage = 0;
                    } else {
                        $testPercentage = ($result_data["test"]["total_reached_points"] / $result_data["test"]["total_max_points"]) * 100;
                    }

                    $testMaxPoints = $result_data["test"]["total_max_points"];
                    $testReachedPoints = $result_data["test"]["total_reached_points"];

                    $passOverwiewData['test'] = [
                        'active_id' => $active_id,
                        'scored_pass' => $scoredPass,
                        'max_points' => $testMaxPoints,
                        'reached_points' => $testReachedPoints,
                        'percentage' => $testPercentage
                    ];
                } else {
                    $isScoredPass = false;
                }

                $passOverwiewData['passes'][] = [
                    'active_id' => $active_id,
                    'pass' => $pass,
                    'finishdate' => $passFinishDate,
                    'max_points' => $passMaxPoints,
                    'reached_points' => $passReachedPoints,
                    'percentage' => $passPercentage,
                    'answered_questions' => $passAnsweredQuestions,
                    'total_questions' => $passTotalQuestions,
                    'is_scored_pass' => $isScoredPass
                ];
            }
        }

        return $passOverwiewData;
    }

    /**
     * Returns the list of answers of a users test pass and offers a scoring option
     */
    public function getManScoringQuestionGuiList(int $active_id, int $pass): array
    {
        $man_scoring_question_types = ilObjTestFolder::_getManualScoring();

        $test_result_data = $this->object->getTestResult($active_id, $pass);

        $man_scoring_question_gui_list = [];

        foreach ($test_result_data as $question_data) {
            if (!isset($question_data['qid'])) {
                continue;
            }

            if (!isset($question_data['type'])) {
                throw new ilTestException('no question type given!');
            }

            $question_gui = $this->object->createQuestionGUI("", $question_data['qid']);

            if (!in_array($question_gui->getObject()->getQuestionTypeID(), $man_scoring_question_types)) {
                continue;
            }

            $man_scoring_question_gui_list[ $question_data['qid'] ] = $question_gui;
        }

        return $man_scoring_question_gui_list;
    }

    public static function isManScoringDone(int $active_id): bool
    {
        return (new TestManScoringDoneHelper())->isDone($active_id);
    }

    public static function setManScoringDone(int $activeId, bool $manScoringDone): void
    {
        (new TestManScoringDoneHelper())->setDone($activeId, $manScoringDone);
    }

    public function buildVirtualSequence(ilTestSession $testSession): ilTestVirtualSequence
    {
        $test_sequence_factory = new ilTestSequenceFactory($this->object, $this->db, $this->questionrepository);

        if ($this->object->isRandomTest()) {
            $virtual_sequence = new ilTestVirtualSequenceRandomQuestionSet($this->db, $this->object, $test_sequence_factory);
        } else {
            $virtual_sequence = new ilTestVirtualSequence($this->db, $this->object, $test_sequence_factory);
        }

        $virtual_sequence->setActiveId($testSession->getActiveId());

        $virtual_sequence->init();

        return $virtual_sequence;
    }

    public function getVirtualSequenceUserResults(ilTestVirtualSequence $virtualSequence): array
    {
        $resultsByPass = [];

        foreach ($virtualSequence->getUniquePasses() as $pass) {
            $results = $this->object->getTestResult(
                $virtualSequence->getActiveId(),
                $pass,
                false,
                true,
                true
            );

            $resultsByPass[$pass] = $results;
        }

        $virtualPassResults = [];

        foreach ($virtualSequence->getQuestionsPassMap() as $questionId => $pass) {
            foreach ($resultsByPass[$pass] as $key => $questionResult) {
                if ($key === 'test' || $key === 'pass') {
                    continue;
                }

                if ($questionResult['qid'] == $questionId) {
                    $questionResult['pass'] = $pass;
                    $virtualPassResults[$questionId] = $questionResult;
                    break;
                }
            }
        }

        return $virtualPassResults;
    }

    public function getQuestionSummaryData(ilTestSequenceSummaryProvider $testSequence, bool $obligationsFilterEnabled): array
    {
        $result_array = $testSequence->getSequenceSummary($obligationsFilterEnabled);

        $marked_questions = [];

        if ($this->object->getShowMarker()) {
            $marked_questions = ilObjTest::_getSolvedQuestions($testSequence->getActiveId());
        }

        $data = [];
        $firstQuestion = true;

        foreach ($result_array as $key => $value) {
            $disableLink = (
                $this->object->isFollowupQuestionAnswerFixationEnabled()
                && !$value['presented'] && !$firstQuestion
            );

            $description = "";
            if ($this->object->getListOfQuestionsDescription()) {
                $description = $value["description"];
            }

            $points = "";
            if (!$this->object->getTitleOutput()) {
                $points = $value["points"];
            }

            $marked = false;
            if (count($marked_questions)) {
                if (array_key_exists($value["qid"], $marked_questions)) {
                    $obj = $marked_questions[$value["qid"]];
                    if ($obj["solved"] == 1) {
                        $marked = true;
                    }
                }
            }



            // fau: testNav - add number parameter for getQuestionTitle()
            $data[] = [
                'order' => $value["nr"],
                'title' => $this->object->getQuestionTitle($value["title"], $value["nr"], $value["points"]),
                'description' => $description,
                'disabled' => $disableLink,
                'worked_through' => $value["worked_through"],
                'postponed' => $value["postponed"],
                'points' => $points,
                'marked' => $marked,
                'sequence' => $value["sequence"],
                'obligatory' => $value['obligatory'],
                'isAnswered' => $value['isAnswered']
            ];

            $firstQuestion = false;
            // fau.
        }

        return $data;
    }
}
