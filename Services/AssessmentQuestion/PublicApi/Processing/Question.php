<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;

use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\UI\Component\Component;

/**
 * Class QuestionProcessing
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>$
 */
class Question
{

    /**
     * Question constructor.
     *
     * @param AssessmentEntityId $question_revision_uuid
     * @param int                $actor_user_id
     * @param AssessmentEntityId $user_answer_uuid
     */
    public function __construct(AssessmentEntityId $question_revision_uuid, int $actor_user_id, AssessmentEntityId $user_answer_uuid)
    {
        // TODO
    }


    /**
     * @return QuestionFormDto
     */
    public function getQuestionPresentation() : QuestionFormDto
    {
        // TODO: Implement GetQuestionPresentation() method.
    }


    /**
     * @param QuestionResourcesDto       $collector
     * @param                            $image_path
     * @param                            $a_mode
     * @param                            $a_no_interaction
     *
     * @return QuestionFormDto
     */
    //TODO
    public function getStandaloneQuestionExportPresentation(QuestionResourcesDto $collector, $image_path, $a_mode, $a_no_interaction) : QuestionFormDto
    {
        // TODO: Implement GetStandaloneQuestionExportPresentation() method.
    }


    /**
     * @return Component
     */
    public function getGenericFeedbackOutput() : Component
    {
        // TODO: Implement getGenericFeedbackOutput() method.
    }


    /**
     * @return Component
     */
    public function getSpecificFeedbackOutput() : Component
    {
        // TODO: Implement getSpecificFeedbackOutput() method.
    }


    /**
     * @param UserAnswerSubmit $user_answer
     */
    public function storeUserAnswer(UserAnswerSubmit $user_answer) : void
    {
        // TODO: Implement SaveUserAnswer() method.
    }


    /**
     * @return ScoredUserAnswerDto
     */
    public function getUserScore() : ScoredUserAnswerDto
    {
        // TODO: Implement GetUserScore() method.
    }
}