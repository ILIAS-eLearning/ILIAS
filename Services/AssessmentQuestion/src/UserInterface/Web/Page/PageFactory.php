<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Page;

class PageFactory
{

    const ASQ_PAGE_TYPE_PREFIX = 'asq';

    const ASQ_PAGE_TYPE_QUESTION = self::ASQ_PAGE_TYPE_PREFIX.'q';
    const ASQ_PAGE_TYPE_GENERIC_FEEDBACK = self::ASQ_PAGE_TYPE_PREFIX.'g';



    /**
     * @var integer
     */
    protected $il_object_int_id;
    /**
     * @var integer
     */
    protected $question_int_id;
    /**
     * @var string
     */
    protected $lng_key;

    /**
     * PageFactory constructor.
     *
     * @param int $il_object_int_id
     * @param int $question_int_id
     * @param     $lng_key
     */
    public function __construct(int $il_object_int_id, int $question_int_id)
    {
        global $DIC;

        $this->il_object_int_id = $il_object_int_id;
        $this->question_int_id = $question_int_id;
        //The lng_key could be used in future as parameter in the constructor
        $this->lng_key = $DIC->language()->getDefaultLanguage();
    }


    /**
     * @return Page
     */
    public function getFeedbackPage():Page
    {
        return Page::getPage(self::ASQ_PAGE_TYPE_GENERIC_FEEDBACK,$this->il_object_int_id,$this->question_int_id,$this->lng_key);
    }


    /**
     * @return Page
     */
    public function getQuestionPage():Page
    {
        return Page::getPage(self::ASQ_PAGE_TYPE_QUESTION,$this->il_object_int_id,$this->question_int_id,$this->lng_key);
    }

}