<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;

use ilAsqQuestionPageGUI;
use ILIAS\AssessmentQuestion\Application\ProcessingApplicationService;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\PublishedQuestionRepository;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\QuestionComponent;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\Page;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionCommands;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionConfig;
use \ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
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
     * @var string
     */
    protected $question_revision_uuid;
    /**
     * @var ProcessingApplicationService
     */
    protected $processing_application_service;
    /**
     * @var QuestionDto
     */
    private $question_dto;


    /**
     * Question constructor.
     *
     * @param string         $question_revision_uuid
     * @param int            $actor_user_id
     * @param QuestionConfig $question_config
     *
     */
    public function __construct(string $question_revision_uuid, ProcessingApplicationService $processing_application_service)
    {
        $this->question_revision_uuid = $question_revision_uuid;
        $this->processing_application_service = $processing_application_service;
    }


    /**
     * @return ilAsqQuestionPageGUI
     */
    public function getQuestionPresentation(?QuestionCommands $question_commands = null) : ilAsqQuestionPageGUI
    {
        $question_dto  = $this->getQuestionDto();
        return $this->processing_application_service->getQuestionPresentation($question_dto,$question_commands);
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


    /**
     * @return QuestionDto
     */
    private function getQuestionDto() : QuestionDto
    {
        if (is_null($this->question_dto)) {
            $published_question_repository = new PublishedQuestionRepository();
            $this->question_dto = $published_question_repository->getQuestionByRevisionId($this->question_revision_uuid);
        }

        return $this->question_dto;
    }



}