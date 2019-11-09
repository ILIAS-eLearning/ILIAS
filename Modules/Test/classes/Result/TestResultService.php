<?php

namespace ILIAS\Modules\Test\Result;

use assQuestion;
use ilCourseObjectiveResult;
use ilObjAssessmentFolder;
use ilObjTest;

/**
 * Class TestResultService
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class TestResultService
{

    /**
     * @var int
     */
    protected $obj_id;
    /**
     * @var int
     */
    protected $active_id;
    /**
     * @var int
     */
    protected $test_pass;
    /**
     * @var int
     */
    protected $user_id;


    public function __construct(int $obj_id, int $active_id, int $test_pass, int $user_id)
    {
        $this->obj_id = $obj_id;
        $this->active_id = $active_id;
        $this->test_pass = $test_pass;
        $this->user_id = $user_id;
    }

    public function getResultsAsAssocArray(): array {
        global $DIC;

        $test_result_repository = new TestResultRepository();

        $question_processing = $DIC->assessment()->questionProcessing($this->obj_id, $this->user_id, $this->test_pass);

        $arr_results = [];
        foreach($test_result_repository->getTestResults($this->active_id, $this->test_pass) as $test_result) {

            $question_dto = $question_processing->question($test_result->getRevisionKey())->getQuestionDto();

            $result['nr'] = $test_result->getOrder();
            $result['title'] = $question_dto->getData()->getTitle();
            $result['max'] = $test_result->getMaxPoints();
            $result['reached'] = $test_result->getPoints();
            $result['requested_hints'] = $test_result->getHintCount();
            $result['hint_points'] = $test_result->getHintPoints();
            $result['percent'] = $test_result->getPercent();
            $result['solution'] = ''; //TODO
            $result['type'] = ''; //TODO
            $result['qid'] = $question_dto->getQuestionIntId();
            $result['original_id'] = '';//TODO
            $result['workedthrough'] = 1; //TODO
            $result['answered'] = $test_result->getAnswered();
            $result['revision_key'] = $test_result->getRevisionKey();

            $arr_results[] = $result;
        }

        return $arr_results;
    }


    public function persistAnswerResult(string $question_revision_key, int $manual, int $order)
    {
        global $DIC;

        //SEE Modules/TestQuestionPool/classes/class.assQuestion.php

        $question_processing = $DIC->assessment()->questionProcessing($this->obj_id, $this->user_id, $this->test_pass);

        $user_answer_score_dto = $question_processing->userAnswer($question_revision_key)->getUserAnswerScore();

        if (is_object($user_answer_score_dto)) {
            $test_result_repository = new TestResultRepository();
            $test_result_repository->persistQuestionResult(
                $this->active_id,
                $this->test_pass,
                $question_processing->question($question_revision_key)->getQuestionDto()->getQuestionIntId(),
                $question_revision_key,
                $manual,
                $order,
                $user_answer_score_dto);
        }

        if( ilObjAssessmentFolder::_enabledAssessmentLogging() )
        {
            assQuestion::logAction(
                sprintf(
                    $this->lng->txtlng(
                        "assessment", "log_user_answered_question", ilObjAssessmentFolder::_getLogLanguage()
                    ),
                    $user_answer_score_dto->getReachedPoints()
                ),
                $this->active_id,
                $question_processing->question($question_revision_key)->getQuestionDto()->getQuestionIntId()
            );
        }

        $test_pass_result = $test_result_repository->calculateTestPassResult(
            $this->active_id,
            $this->test_pass,
            $this->getWorkingTimeOfParticipantForPass(),
            ilObjTest::buildExamId( $this->active_id, $this->test_pass, $this->obj_id)
            );


        $test_pass_result_repository = new TestPassResultRepository();
        $test_pass_result_repository->persistPassResult($test_pass_result);


        // Update objective status
        include_once 'Modules/Course/classes/class.ilCourseObjectiveResult.php';
        ilCourseObjectiveResult::_updateObjectiveResult($this->user_id,$this->active_id,$this->obj_id);


    }


    /**
     * Returns the complete working time in seconds for a test participant
     *
     * @return integer The working time in seconds for the test participant
     * @access public
     */
    private function getWorkingTimeOfParticipantForPass()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF("SELECT * FROM tst_times WHERE active_fi = %s AND pass = %s ORDER BY started",
            array('integer','integer'),
            array($this->active_id, $this->test_pass)
        );
        $time = 0;
        while ($row = $ilDB->fetchAssoc($result))
        {
            preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["started"], $matches);
            $epoch_1 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
            preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["finished"], $matches);
            $epoch_2 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
            $time += ($epoch_2 - $epoch_1);
        }
        return $time;
    }
}