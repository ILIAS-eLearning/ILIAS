<?php

namespace ILIAS\Modules\Test\Result;

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


    public function persistAnswerResult(string $question_revision_key, int $manual)
    {
        global $DIC;

        $question_processing = $DIC->assessment()->questionProcessing($this->obj_id, $this->user_id, $this->test_pass);

        $user_answer_score = $question_processing->userAnswer($question_revision_key)->getUserAnswerScore();

        if (is_object($user_answer_score)) {
            $test_result_repository = new TestResultRepository();
            $test_result_repository->storeQuestionResult(
                $this->active_id,
                $this->test_pass,
                $question_processing->question($question_revision_key)->getQuestionDto()->getQuestionIntId(),
                $manual,
                $question_processing->userAnswer($question_revision_key)->getUserAnswerScore());
        }

        //TODO SEE Modules/TestQuestionPool/classes/class.assQuestion.php
        /*
        if( ilObjAssessmentFolder::_enabledAssessmentLogging() )
        {
            assQuestion::logAction(
                sprintf(
                    $this->lng->txtlng(
                        "assessment", "log_user_answered_question", ilObjAssessmentFolder::_getLogLanguage()
                    ),
                    $reached_points
                ),
                $active_id,
                $this->getId()
            );
        }*/
        // update test pass results
        /*
        self::_updateTestPassResults($active_id, $pass, $obligationsEnabled, $this->getProcessLocker());

        // Update objective status
        include_once 'Modules/Course/classes/class.ilCourseObjectiveResult.php';
        ilCourseObjectiveResult::_updateObjectiveResult($ilUser->getId(),$active_id,$this->getId());
        */
    }
}