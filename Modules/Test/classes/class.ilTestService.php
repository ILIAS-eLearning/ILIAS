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
     * @access protected
     * @var ilObjTest
     */
    protected $object = null;

    /**
     * @access public
     * @param	ilObjTest $a_object
     */
    public function __construct(ilObjTest $a_object)
    {
        $this->object = $a_object;
    }

    /**
     * @access public
     * @global	ilObjUser	$ilUser
     * @param	integer		$active_id
     * @param	boolean		$short
     * @return	array		$passOverwiewData
     */
    public function getPassOverviewData($active_id, $short = false): array
    {
        $passOverwiewData = array();

        global $DIC;
        $ilUser = $DIC['ilUser'];

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
     *
     * @access public
     * @param integer $active_id Active ID of the active user
     * @param integer $pass Test pass
     */
    public function getManScoringQuestionGuiList($activeId, $pass): array
    {
        include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
        $manScoringQuestionTypes = ilObjAssessmentFolder::_getManualScoring();

        $testResultData = $this->object->getTestResult($activeId, $pass);

        $manScoringQuestionGuiList = array();

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

    /**
     * reads the flag wether manscoring is done for the given test active or not
     *
     * @access public
     * @static
     * @param int $activeId
     * @return bool
     */
    public static function isManScoringDone(int $activeId): bool
    {
        return (new TestManScoringDoneHelper())->isDone($activeId);
    }

    /**
     * stores the flag wether manscoring is done for the given test active or not
     * @param int  $activeId
     * @param bool $manScoringDone
     */
    public static function setManScoringDone(int $activeId, bool $manScoringDone) : void
    {
        (new TestManScoringDoneHelper())->setDone($activeId, $manScoringDone);
    }

    public function buildVirtualSequence(ilTestSession $testSession)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        $component_repository = $DIC['component.repository'];

        $testSequenceFactory = new ilTestSequenceFactory($ilDB, $lng, $component_repository, $this->object);

        if ($this->object->isRandomTest()) {
            $virtualSequence = new ilTestVirtualSequenceRandomQuestionSet($ilDB, $this->object, $testSequenceFactory);
        } else {
            $virtualSequence = new ilTestVirtualSequence($ilDB, $this->object, $testSequenceFactory);
        }

        $virtualSequence->setActiveId($testSession->getActiveId());

        $virtualSequence->init();

        return $virtualSequence;
    }

    public function getVirtualSequenceUserResults(ilTestVirtualSequence $virtualSequence): array
    {
        $resultsByPass = array();

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

        $virtualPassResults = array();

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

    /**
     * @param ilTestSequenceSummaryProvider $testSequence
     * @param bool $obligationsFilter
     * @return array
     */
    public function getQuestionSummaryData(ilTestSequenceSummaryProvider $testSequence, $obligationsFilterEnabled): array
    {
        $result_array = $testSequence->getSequenceSummary($obligationsFilterEnabled);

        $marked_questions = array();

        if ($this->object->getShowMarker()) {
            include_once "./Modules/Test/classes/class.ilObjTest.php";
            $marked_questions = ilObjTest::_getSolvedQuestions($testSequence->getActiveId());
        }

        $data = array();
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
                'title' => $this->object->getQuestionTitle($value["title"], $value["nr"]),
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
