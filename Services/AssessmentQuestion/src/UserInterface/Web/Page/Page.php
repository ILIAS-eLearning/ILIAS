<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Page;

use ilAsqAnswerOptionFeedbackPageGUI;
use ilAsqGenericFeedbackPageGUI;
use ilAsqQuestionFeedbackEditorGUI;
use ILIAS\UI\Implementation\Component\Link\Standard;
use ReflectionClass;

/**
 * Class Page
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class Page extends \ilPageObject
{
    public $parent_type = PageFactory::ASQ_PAGE_TYPE_QUESTION;


    /**
     * @param int $question_id
     *
     * @return Page
     */
    public static function getPage(string $page_type, int $parent_int_id, int $page_int_id, string $lng_key) : Page
    {
        self::createPageIfNotExists($page_type, $parent_int_id, $page_int_id, $lng_key);

        $reflector = new ReflectionClass(self::class);
        /**
         * @var Page $page
         */
        $page = $reflector->newInstanceWithoutConstructor();

        $page->setParentType($page_type);
        $page->__construct($parent_int_id,0,$lng_key);

        return $page;
    }

    /**
     * @return string parent type
     */
    public function getParentType() : string
    {
        return $this->parent_type;
    }

    /**
     * @return string parent type
     */
    public function setParentType($parent_type) : void
    {
        $this->parent_type = $parent_type;
    }



    public static function createPageIfNotExists(string $page_type, int $parent_int_id, int $page_int_id, string $lng_key)
    {
        if (parent::_exists($page_type, $page_int_id, $lng_key) === false) {
            /**
             * @var \ilPageObject $page
             */
            $page = new self();
            $page->setParentType($page_type);
            $page->setParentId($parent_int_id);
            $page->setId($page_int_id);
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


    /**
     * @return string
     */
    public function getPageEditingLink():string {
        global $DIC;

        $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), 'page_type', $this->getParentType());
        $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), 'parent_int_id', $this->getParentId());
        $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), 'answer_option_int_id', $this->getId());
        $label = $DIC->language()->txt('asq_link_edit_feedback_page');

        //TODO
        $action = $DIC->ctrl()->getLinkTargetByClass([ilAsqQuestionFeedbackEditorGUI::class,ilAsqAnswerOptionFeedbackPageGUI::class], ilAsqAnswerOptionFeedbackPageGUI::CMD_EDIT);

        $link = new Standard($label,$action);

        return $DIC->ui()->renderer()->render($link);
    }

    /**
     * @param string $pageObjectType
     * @param int    $feedbackIntId
     *
     * @return string
     */
    public function getPageContent()
    {
        global $DIC;
        $class = $DIC->ctrl()->getCmdClass();
        /**
         * @var \ilPageObjectGUI $page_object_gui
         */
        $page_object_gui = new $class($this->getId());
        $page_object_gui->setOutputMode(IL_PAGE_PRESENTATION);

        return $page_object_gui->presentation(IL_PAGE_PRESENTATION);
    }

}