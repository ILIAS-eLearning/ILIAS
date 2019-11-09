<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;

use ilAsqQuestionPageGUI;
use ILIAS\AssessmentQuestion\Application\ProcessingApplicationService;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\AnswerScoreDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\AbstractScoring;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\PublishedQuestionRepository;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionCommands;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionConfig;

/**
 * Class ProcessingUserAnswer
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>$
 */
class ProcessingUserAnswer
{

    /**
     * @var ProcessingApplicationService
     */
    protected $processing_application_service;
    /**
     * @var QuestionDto
     */
    private $question_dto;


    /**
     * ProcessingUserAnswer constructor.
     *
     * @param int    $processing_obj_id
     * @param int    $actor_user_id
     * @param int    $attempt_number
     * @param string $question_revision_key
     */
    public function __construct(int $processing_obj_id, int $actor_user_id, int $attempt_number, string $question_revision_key, string $lng_key)
    {
        $this->processing_application_service = new ProcessingApplicationService($processing_obj_id, $actor_user_id, $attempt_number, $lng_key);

        $published_questions = new PublishedQuestionRepository();

        $this->question_dto = $published_questions->getQuestionByRevisionId($question_revision_key);
    }


    public function getUserAnswer() : ?Answer
    {
        return $this->processing_application_service->getUserAnswer($this->question_dto->getRevisionId());
    }


    /**
     * @return AnswerScoreDto|null
     */
    public function getUserAnswerScore() : ?AnswerScoreDto
    {
        $scoring_class = QuestionPlayConfiguration::getScoringClass($this->question_dto->getPlayConfiguration());
        /**
         * @var AbstractScoring $scoring
         */
        if (is_object($this->getUserAnswer())) {
            $scoring = new $scoring_class($this->question_dto);;

            return $scoring->score($this->getUserAnswer());
        }

        return null;
    }

    /**
     * @return ilAsqQuestionPageGUI
     */
    public function getAnsweredQuestionPresentation(QuestionConfig $question_config) : ilAsqQuestionPageGUI
    {
        $page_gui = $this->processing_application_service->getQuestionPageGUI( $this->question_dto, $question_config);

        $question_component = $this->processing_application_service->getQuestionComponent( $this->question_dto, $question_config, new QuestionCommands());
        $question_component->setAnswer($this->processing_application_service->getUserAnswer( $this->question_dto->getRevisionId()));

        $page_gui->setQuestionHTML([ $this->question_dto->getQuestionIntId() => $question_component->renderHtml()]);

        return $page_gui;
    }

    public function getBestAnswerQuestionPresentation(QuestionConfig $question_config, ?QuestionCommands $question_commands = null) : ilAsqQuestionPageGUI
    {

        $page_gui = $this->processing_application_service->getQuestionPageGUI( $this->question_dto, $question_config);

        $scoring_class = QuestionPlayConfiguration::getScoringClass( $this->question_dto->getPlayConfiguration());

        $score = new $scoring_class( $this->question_dto);

        $best_answer = $score->getBestAnswer();

        $question_component = $this->processing_application_service->getQuestionComponent( $this->question_dto, $question_config, new QuestionCommands());
        $question_component->setAnswer($best_answer);

        $page_gui->setQuestionHTML([ $this->question_dto->getQuestionIntId() => $question_component->renderHtml()]);

        return $page_gui;
    }


}