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
     * @var int
     */
    protected $container_obj_id;
    /**
     * @var int
     */
    protected $actor_user_id;
    /**
     * @var QuestionConfig
     */
    protected $question_config;
    /**
     * @var ProcessingApplicationService
     */
    protected $processing_application_service;


    public function __construct(ProcessingApplicationService $processing_application_service)
    {
        $this->processing_application_service = $processing_application_service;
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
}