<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;

use ILIAS\AssessmentQuestion\Application\PlayApplicationService;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionDto;
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
     * @var PlayApplicationService
     */
    protected $processing_application_service;


    public function __construct(int $container_obj_id, int $actor_user_id)
    {
        $this->container_obj_id = $container_obj_id;
        $this->actor_user_id = $actor_user_id;
        $this->processing_application_service = new PlayApplicationService($container_obj_id, $actor_user_id);
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