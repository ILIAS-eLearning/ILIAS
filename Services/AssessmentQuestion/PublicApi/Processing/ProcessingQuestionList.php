<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;

use ILIAS\AssessmentQuestion\Application\ProcessingApplicationService;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionConfig;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionList;

class ProcessingQuestionList implements QuestionList
{
    /**
     * @var ProcessingApplicationService
     */
    protected $processing_application_service;


    /**
     * ProcessingQuestionList constructor.
     *
     * @param int            $container_obj_id
     * @param int            $actor_user_id
     * @param QuestionConfig $question_config
     * @param int            $attempt_number
     * @param string         $lng_key
     */
    public function __construct(int $container_obj_id, int $actor_user_id,  int $attempt_number, string $lng_key)
    {
        $this->processing_application_service = new ProcessingApplicationService($container_obj_id, $actor_user_id, $attempt_number, $lng_key);
    }

    public function scoreAndProjectTestAttempt() : void
    {
        $this->processing_application_service->scoreAndProjectTestAttempt();
    }


    public function getQuestionsOfContainerAsAssocArray() : array
    {
        $questions = [];

        foreach($this->getQuestionsOfContainerAsDtoList() as $questionDto)
        {
            $questions[] = [
                //'question_id' => $questionDto->getQuestionIntId(),
                'title' => $questionDto->getData()->getTitle(),
                'description' => $questionDto->getData()->getDescription(),
            ];
        }

        return $questions;
    }


    /**
     * @return QuestionDto[]
     */
    public function getQuestionsOfContainerAsDtoList() : array
    {
        return $this->processing_application_service->GetQuestions();
    }

    /**
     * @return QuestionDto[]
     */
    public function getAnsweredQuestionsOfContainerAsDtoList() : array
    {
        return $this->processing_application_service->getAnswersFromAnsweredQuestions();
    }

    /**
     * @return QuestionDto[]
     */
    public function getUnAnsweredQuestionsOfContainerAsDtoList() : array
    {
        return $this->processing_application_service->getUnansweredQuestions();
    }
}