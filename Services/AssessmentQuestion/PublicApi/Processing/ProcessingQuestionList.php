<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;

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


    public function __construct(int $container_obj_id, int $actor_user_id)
    {
        $this->container_obj_id = $container_obj_id;
        $this->actor_user_id = $actor_user_id;
    }


    public function getQuestionsOfContainerAsAssocArray() : array
    {
        // TODO: Implement getQuestionsOfContainerAsAssocArray() method.
    }


    /**
     * @return QuestionDto[]
     */
    public function getQuestionsOfContainerAsDtoList() : array
    {
        // TODO: Implement getQuestionsOfContainerAsDtoList() method.
    }
}