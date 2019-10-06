<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Page;

class PageFactory
{

    const ASQ_PAGE_TYPE_PREFIX = 'asq';

    const ASQ_PAGE_TYPE_QUESTION = self::ASQ_PAGE_TYPE_PREFIX.'q';
    const ASQ_PAGE_TYPE_GENERIC_FEEDBACK = self::ASQ_PAGE_TYPE_PREFIX.'g';
    const ASQ_PAGE_TYPE_ANSWER_OPTION_FEEDBACK = self::ASQ_PAGE_TYPE_PREFIX.'a';



    /**
     * @var integer
     */
    protected $page_parent_int_id;
    /**
     * @var integer
     */
    protected $page_int_id;
    /**
     * @var string
     */
    protected $page_type;
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
    public function __construct(string $page_type,int $page_parent_int_id, int $page_int_id)
    {
        global $DIC;

        $this->page_parent_int_id = $page_parent_int_id;
        $this->page_int_id = $page_int_id;
        $this->page_type = $page_type;
        //The lng_key could be used in future as parameter in the constructor
        $this->lng_key = $DIC->language()->getDefaultLanguage();
    }

    public function getPage() {
        Page::getPage( $this->page_type,$this->page_parent_int_id,$this->page_int_id,$this->lng_key);
    }

    /**
     * @return Page
     */
    public function getAnswerOptionFeedbackPage():Page
    {
        return Page::getPage(self::ASQ_PAGE_TYPE_ANSWER_OPTION_FEEDBACK,$this->page_parent_int_id,$this->page_int_id,$this->lng_key);
    }


    /**
     * @return Page
     */
    public function getFeedbackPage():Page
    {
        return Page::getPage(self::ASQ_PAGE_TYPE_GENERIC_FEEDBACK,$this->page_parent_int_id,$this->page_int_id,$this->lng_key);
    }


    /**
     * @return Page
     */
    public function getQuestionPage():Page
    {
        return Page::getPage(self::ASQ_PAGE_TYPE_QUESTION,$this->page_parent_int_id,$this->page_int_id,$this->lng_key);
    }

}