<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;

use ilAsqQuestionAuthoringGUI;
use ilAsqQuestionPageGUI;
use ilAsqQuestionProcessingGUI;
use ILIAS\AssessmentQuestion\Application\ProcessingApplicationService;
use ILIAS\AssessmentQuestion\DomainModel\AnswerScoreDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\AbstractScoring;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\PublishedQuestionRepository;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\ProcessingContextContainer;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionCommands;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionConfig;
use \ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Link\Standard as UiStandardLink;

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
class ProcessingQuestion
{

    /**
     * @var string
     */
    protected $question_revision_key;
    /**
     * int
     */
    protected $attempt_number;
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
     * @param string         $question_revision_key
     * @param int            $actor_user_id
     * @param QuestionConfig $question_config
     *
     */
    public function __construct(string $question_revision_key, int $container_obj_id, int $actor_user_id, int $attempt_number, string $lng_key)
    {
        $this->question_revision_key = $question_revision_key;
        $this->attempt_number = $attempt_number;
        $this->container_obj_id = $container_obj_id;

        $this->processing_application_service = new ProcessingApplicationService($container_obj_id, $actor_user_id, $attempt_number, $lng_key);
    }


    /**
     *
     * @return ilAsqQuestionProcessingGUI
     */
    public function getProcessingQuestionGUI(QuestionConfig $question_config) : ilAsqQuestionProcessingGUI
    {
        return $this->processing_application_service->getProcessingQuestionGUI($this->question_revision_key, $question_config);
    }

    /**
     * @return ilAsqQuestionPageGUI
     */
    public function getQuestionPresentation(QuestionConfig $question_config, ?QuestionCommands $question_commands = null) : ilAsqQuestionPageGUI
    {
        $question_dto = $this->getQuestionDto();

        return $this->processing_application_service->getQuestionPresentation($question_dto, $question_config, $question_commands);
    }





    /**
     * @return UiStandardLink
     */
    public function getQuestionLink(array $ctrl_stack) : UiStandardLink
    {
        global $DIC;
        array_push($ctrl_stack, ilAsqQuestionAuthoringGUI::class);
        array_push($ctrl_stack, \ilAsqQuestionConfigEditorGUI::class);

        $this->setQuestionUidParameter();

        return $DIC->ui()->factory()->link()->standard(
            $DIC->language()->txt('asq_authoring_tab_config'),
            $DIC->ctrl()->getLinkTargetByClass($ctrl_stack));
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
     * @return QuestionDto
     */
    public function getQuestionDto() : QuestionDto
    {
        if (is_null($this->question_dto)) {
            $published_question_repository = new PublishedQuestionRepository();
            $this->question_dto = $published_question_repository->getQuestionByRevisionId($this->question_revision_key);
        }

        return $this->question_dto;
    }


    /**
     * sets the question uid parameter for the ctrl hub gui ilAsqQuestionAuthoringGUI
     */
    protected function setQuestionUidParameter()
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        $DIC->ctrl()->setParameterByClass(
            ilAsqQuestionProcessingGUI::class,
            ilAsqQuestionProcessingGUI::VAR_QUESTION_REVISION_UID,
            $this->question_revision_key
        );
    }
}