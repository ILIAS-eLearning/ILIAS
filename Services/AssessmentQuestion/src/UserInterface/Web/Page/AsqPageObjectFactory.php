<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Page;

/**
 * Class AsqPageObjectFactory
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AsqPageObjectFactory
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
        AsqPageObject::getPage( $this->page_type,$this->page_parent_int_id,$this->page_int_id,$this->lng_key);
    }

    /**
     * @return AsqPageObject
     */
    public function getAnswerOptionFeedbackPage():AsqPageObject
    {
        return AsqPageObject::getPage(self::ASQ_PAGE_TYPE_ANSWER_OPTION_FEEDBACK,$this->page_parent_int_id,$this->page_int_id,$this->lng_key);
    }


    /**
     * @return AsqPageObject
     */
    public function getFeedbackPage():AsqPageObject
    {
        return AsqPageObject::getPage(self::ASQ_PAGE_TYPE_GENERIC_FEEDBACK,$this->page_parent_int_id,$this->page_int_id,$this->lng_key);
    }


    /**
     * @return AsqPageObject
     */
    public function getQuestionPage():AsqPageObject
    {
        return AsqPageObject::getPage(self::ASQ_PAGE_TYPE_QUESTION,$this->page_parent_int_id,$this->page_int_id,$this->lng_key);
    }

}