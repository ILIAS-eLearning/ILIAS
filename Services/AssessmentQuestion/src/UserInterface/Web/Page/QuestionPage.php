<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Page;

/**
 * Class QuestionPage
 *
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author Adrian Lüthi <al@studer-raimann.ch>
 * @author Björn Heyser <bh@bjoernheyser.de>
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * Only used because ilPageObject has a final constructer and by / before Constructing the Parent Type has to be defined.
 */
class QuestionPage extends Page
{

    const PAGE_SUB_TYPE = 'q';

    /**
     * @param int $question_id
     *
     * @return Page
     */
    public static function getPage(string $page_class_name, int $il_object_int_id, int $question_id, string $lng_key) : Page
    {
        self::createPageIfNotExists($page_class_name,PageFactory::ASQ_GLOBAL_PAGE_TYPE . self::PAGE_SUB_TYPE , $il_object_int_id, $question_id, $lng_key);

        return new self($question_id,0,$lng_key);
    }

    /**
     * @return string parent type
     */
    function getParentType() : string
    {
        $this->parent_type = PageFactory::ASQ_GLOBAL_PAGE_TYPE . self::PAGE_SUB_TYPE;

        return $this->parent_type;
    }



    public static function createPageIfNotExists(string $page_class_name, string $subtype, int $il_object_int_id, int $question_id, string $lng_key)
    {
        if (parent::_exists($subtype, $question_id, $lng_key) === false) {
            /**
             * @var \ilPageObject $page
             */
            $page = new self();
            $page->setParentId($il_object_int_id);
            $page->setId($question_id);
            $page->setLanguage($lng_key);

            $page->create();
        }
    }

    /**
     * @param int $questionIntId
     *
     * @return string
     */
    public function getXMLContent($a_incl_head = false)
    {
        $xml = "<PageObject>";
        $xml .= "<PageContent>";
        $xml .= "<Question QRef=\"il__qst_{$this->getId()}\"/>";
        $xml .= "</PageContent>";
        $xml .= "</PageObject>";

        return $xml;
    }
}