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

/**
 * Service class for tests.
 *
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @ingroup ModulesTest
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
        protected \ILIAS\TestQuestionPool\QuestionInfoService $questioninfo
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
                $resultData = &$this->object->getTestResult($active_id, $pass);

                if (!$resultData["pass"]["total_max_points"]) {
                    $passPercentage = 0;
                } else {
                    $passPercentage = ($resultData["pass"]["total_reached_points"] / $resultData["pass"]["total_max_points"]) * 100;
                }

                $passMaxPoints = $resultData["pass"]["total_max_points"];
                $passReachedPoints = $resultData["pass"]["total_reached_points"];

                $passAnsweredQuestions = $this->object->getAnsweredQuestionCount($active_id, $pass);
                $passTotalQuestions = count($resultData) - 2;

                if ($pass == $scoredPass) {
                    $isScoredPass = true;

                    if (!$resultData["test"]["total_max_points"]) {
                        $testPercentage = 0;
                    } else {
                        $testPercentage = ($resultData["test"]["total_reached_points"] / $resultData["test"]["total_max_points"]) * 100;
                    }

                    $testMaxPoints = $resultData["test"]["total_max_points"];
                    $testReachedPoints = $resultData["test"]["total_reached_points"];

                    $passOverwiewData['test'] = array(
                        'active_id' => $active_id,
                        'scored_pass' => $scoredPass,
                        'max_points' => $testMaxPoints,
                        'reached_points' => $testReachedPoints,
                        'percentage' => $testPercentage
                    );
                } else {
                    $isScoredPass = false;
                }

                $passOverwiewData['passes'][] = array(
                    'active_id' => $active_id,
                    'pass' => $pass,
                    'finishdate' => $passFinishDate,
                    'max_points' => $passMaxPoints,
                    'reached_points' => $passReachedPoints,
                    'percentage' => $passPercentage,
                    'answered_questions' => $passAnsweredQuestions,
                    'total_questions' => $passTotalQuestions,
                    'is_scored_pass' => $isScoredPass
                );
            }
        }

        return $passOverwiewData;
    }

    /**
     * Returns the list of answers of a users test pass and offers a scoring option
     */
    public function getManScoringQuestionGuiList(int $active_id, int $pass): array
    {
        $manScoringQuestionTypes = ilObjAssessmentFolder::_getManualScoring();

        $testResultData = $this->object->getTestResult($active_id, $pass);

        $manScoringQuestionGuiList = [];

        foreach ($testResultData as $questionData) {
            if (!isset($questionData['qid'])) {
                continue;
            }

            if (!isset($questionData['type'])) {
                throw new ilTestException('no question type given!');
            }

            $questionGUI = $this->object->createQuestionGUI("", $questionData['qid']);

            if (!in_array($questionGUI->object->getQuestionTypeID(), $manScoringQuestionTypes)) {
                continue;
            }

            $manScoringQuestionGuiList[ $questionData['qid'] ] = $questionGUI;
        }

        return $manScoringQuestionGuiList;
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
        $test_sequence_factory = new ilTestSequenceFactory($this->object, $this->db, $this->questioninfo);

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
            $data[] = array(
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
            );

            $firstQuestion = false;
            // fau.
        }

        return $data;
    }
}
