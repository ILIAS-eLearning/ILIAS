<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Authoring;

use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionDto;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionList;
use ILIS\AssessmentQuestion\Application\AuthoringApplicationService;

class AuthoringQuestionList implements QuestionList
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
     * AuthoringApplicationService
     */
    protected $authoring_application_service;

    /**
     * ProcessingQuestionList constructor.
     *
     * @param int $container_obj_id
     * @param int $actor_user_id
     */
    public function __construct(int $container_obj_id, int $actor_user_id)
    {
        $this->container_obj_id = $container_obj_id;
        $this->actor_user_id = $actor_user_id;

        $this->authoring_application_service = new AuthoringApplicationService($container_obj_id, $actor_user_id);

    }

    public function getQuestionsOfContainerAsAssocArray() : array
    {
        $assoc_array = [];
        foreach($this->authoring_application_service->GetQuestions() as $question_dto) {
            $assoc_array[] = $this->getArrayFromDto($question_dto);
        }
        return $assoc_array;
    }

    //TODO move and cleanup this method
    protected function getArrayFromDto($dto) {
        $name = get_class ($dto);
        $name = str_replace('\\', "\\\\", $name);
        $raw = (array)$dto;
        $attributes = array();
        foreach ($raw as $attr => $val) {
            if(is_object($val)) {
                $val_arr = $this->getArrayFromDto($val);
                $prefix = preg_replace('('.$name.'|\*|)', '', $attr);
                $prefixed_arr = [];
                foreach($val_arr as $key => $value) {
                    $attributes[preg_replace ( '/[^a-z0-9_ ]/i', '',$prefix."_".$key)] = $value;
                }
            } else {
                $val_arr = $val;


                $attributes[preg_replace ( '/[^a-z0-9 ]/i', '', preg_replace('('.$name.'|\*|)', '', $attr))] = $val_arr;
            }
        }
       return $attributes;
    }


    /**
     * @return QuestionDto[]
     */
    public function getQuestionsOfContainerAsDtoList() : array
    {
        return $this->authoring_application_service->GetQuestions();
    }
}