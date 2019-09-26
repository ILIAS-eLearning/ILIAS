<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Page;

use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AuthoringContextContainer;
use ILIAS\Services\UICore\MetaTemplate\PageContentGUI;

/**
 * Class AbstractPageService
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class AbstractPageService
{

    /**
     * @var string
     */
    protected $asq_global_page_type;
    /**
     * @var string
     */
    protected $page_sub_type;
    /**
     * @var string
     */
    protected $page_gui_class_name_;
    /**
     * @var integer
     */
    protected $il_object_int_id;
    /**
     * @var integer
     */
    protected $question_int_id;


    /**
     * AbstractPageService constructor.
     *
     * @param string $asq_global_page_type
     * @param int    $question_int_id
     * @param int    $question_sub_object_int_id
     */
    public function __construct(
        string $asq_global_page_type,
        int $il_object_int_id,
        int $question_int_id
    ) {
        $this->asq_global_page_type = $asq_global_page_type;
        $this->il_object_int_id = $il_object_int_id;
        $this->question_int_id = $question_int_id;
    }

    abstract public function getPage() : Page;


    public function getParentType() : string
    {
        $page = $this->getPage();

        return $page->getParentType();
    }


    public function createPage() : Page
    {
        $page =$this->getPage();
        $page->setId($this->getQuestionIntId());
        $page->setParentId($this->getIlObjectIntId());
        $page->create();

        return $page;
    }


    /**
     * @param string $pageObjectType
     * @param int    $feedbackIntId
     *
     * @return string
     */
    public function getPageContent()
    {
        $class = $this->getPageGUIClassName();
        /**
         * @var ilPageObjectGUI $page_object_gui
         */
        $page_object_gui = new $class($this->getQuestionSubObjectIntId());
        $page_object_gui->setOutputMode(IL_PAGE_PRESENTATION);

        return $page_object_gui->presentation(IL_PAGE_PRESENTATION);
    }


    /**
     * @param string $pageObjectType
     * @param int    $feedbackIntId
     *
     * @return string
     */
    public function getPageEditingLink(
        string $pageObjectType,
        int $feedbackIntId
    ) {
        global $DIC;

        $DIC->ctrl()->setParameterByClass($this->getPageGUIClassName(), 'page_sub_type', $this->getPageSubType());
        $DIC->ctrl()->setParameterByClass($this->getPageGUIClassName(), 'question_sub_object_int_id', $this->getQuestionSubObjectIntId());

        $linkHREF = $DIC->ctrl()->getLinkTargetByClass($this->getPageGUIClassName(), 'edit');
        $linkTEXT = $DIC->language()->txt('asq_link_edit_feedback_page');

        return "<a href='$linkHREF'>$linkTEXT</a>";
    }


    /**
     * @return string
     */
    protected
    function getAsqGlobalPageType() : string
    {
        return $this->asq_global_page_type;
    }


    /**
     * @return int
     */
    protected
    function getQuestionIntId() : int
    {
        return $this->question_int_id;
    }


    /**
     * @return int
     */
    protected
    function getIlObjectIntId() : int
    {
        return $this->il_object_int_id;
    }





    abstract protected function getPageGUIClassName() : string;
}
