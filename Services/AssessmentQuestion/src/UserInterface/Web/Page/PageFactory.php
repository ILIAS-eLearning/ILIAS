<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Page;

class PageFactory
{

    const ASQ_GLOBAL_PAGE_TYPE = 'asq';
    /**
     * @var integer
     */
    protected $il_object_int_id;
    /**
     * @var integer
     */
    protected $question_int_id;
    /**
     * @var integer
     */
    protected $question_sub_object_int_id;


    public function __construct(int $il_object_int_id, int $question_int_id)
    {
        $this->il_object_int_id = $il_object_int_id;
        $this->question_int_id = $question_int_id;
    }


    public function getFeedbackPage()
    {
        return new GenericFeedbackPageService(self::ASQ_GLOBAL_PAGE_TYPE, $this->il_object_int_id, $this->question_int_id);
    }


    public function getQuestionPageService()
    {
        return new QuestionPageService(self::ASQ_GLOBAL_PAGE_TYPE, $this->il_object_int_id, $this->question_int_id);
    }
}