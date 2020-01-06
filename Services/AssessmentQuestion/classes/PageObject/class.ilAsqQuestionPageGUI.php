<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


use ILIAS\AssessmentQuestion\UserInterface\Web\Component\QuestionComponent;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\AsqPageObject;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\PageConfig;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\AsqPageObjectFactory;

/**
 * Class ilAsqQuestionPageGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilAsqQuestionPageGUI: ilPageEditorGUI
 * @ilCtrl_Calls ilAsqQuestionPageGUI: ilEditClipboardGUI
 * @ilCtrl_Calls ilAsqQuestionPageGUI: ilMDEditorGUI
 * @ilCtrl_Calls ilAsqQuestionPageGUI: ilPublicUserProfileGUI
 * @ilCtrl_Calls ilAsqQuestionPageGUI: ilNoteGUI
 * @ilCtrl_Calls ilAsqQuestionPageGUI: ilInternalLinkGUI
 * @ilCtrl_Calls ilAsqQuestionPageGUI: ilPropertyFormGUI
 */
class ilAsqQuestionPageGUI extends ilPageObjectGUI
{
    const PAGE_TYPE = 'asqq';

    const TEMP_PRESENTATION_TITLE_PLACEHOLDER = '___TEMP_PRESENTATION_TITLE_PLACEHOLDER___';

    /**
     * @var string
     */
    public $originalPresentationTitle = '';
    /**
     * @var string
     */
    public $questionInfoHTML = '';
    /**
     * @var string
     */
    public $questionActionsHTML = '';
    /**
     * @var string
     */
    public $question_html = '';
    /**
     * @var string
     */
    public $question_xml = '';
    /**
     * @var bool
     */
    public $output2template = false;
    /**
     * @var string
     */
    public $template_output_var = "PAGE_CONTENT";
    /**
     * @var string
     */
    public $output_mode = ilPageObjectGUI::PRESENTATION;
    /**
     * @var bool
     */
    public $enabledpagefocus = true;
    /**
     * @var bool
     */
    public $a_output = false;

    /**
     * @var QuestionComponent
     */
    private $component;

    //TODO fix this, so that it properly inherits from pageobjectgui instead to copy the constructor (badly)
    /**
     * ilAsqQuestionPageGUI constructor.
     *
     * @param AsqPageObject $page
     */
    function __construct(int $parent_int_id, int $page_int_id, string $lng_key)
    {
        /**
          * @var \ILIAS\DI\Container $DIC
        **/
        global $DIC;

        $this->createPageIfNotExists(self::PAGE_TYPE, $parent_int_id, $page_int_id, $lng_key);
        
        parent::__construct(self::PAGE_TYPE, $page_int_id, 0, false, $lng_key);

        $this->page_back_title = $this->lng->txt("page");

        // content and syntax styles
        $DIC->ui()->mainTemplate()->setCurrentBlock("ContentStyle");
        $DIC->ui()->mainTemplate()->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
        $DIC->ui()->mainTemplate()->parseCurrentBlock();
        $DIC->ui()->mainTemplate()->setCurrentBlock("SyntaxStyle");
        $DIC->ui()->mainTemplate()->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
        $DIC->ui()->mainTemplate()->parseCurrentBlock();
    }

    private function createPageIfNotExists(string $page_type, int $parent_int_id, int $page_int_id, string $lng_key)
    {
        if (ilPageObject::_exists($page_type, $page_int_id, $lng_key) === false) {
            /**
             * @var \ilPageObject $page
             */
            $page = new AsqPageObject();
            $page->setParentType($page_type);
            $page->setParentId($parent_int_id);
            $page->setId($page_int_id);
            $page->setLanguage($lng_key);
            
            $page->create();
        }
    }
    
    
    //Reset Page GUI Stuffs
    function determineFileDownloadLink() { return ""; }
    function determineFullscreenLink() { return ""; }
    function determineSourcecodeDownloadScript() { return ""; }



    /**
     * @param AsqPageObject $page
     *
     * @return ilAsqQuestionPageGUI
     */
    public static function getGUI(AsqPageObject $page):ilAsqQuestionPageGUI {
        return new self($page);
    }

    public function getOriginalPresentationTitle()
    {
        return $this->originalPresentationTitle;
    }

    public function setOriginalPresentationTitle($originalPresentationTitle)
    {
        $this->originalPresentationTitle = $originalPresentationTitle;
    }

    protected function isPageContainerToBeRendered()
    {
        return $this->getRenderPageContainer();
    }

    public function showPage()
    {
        /**
         * enable page toc as placeholder for info and actions block
         * @see self::insertPageToc
         */

        $config = $this->getPageConfig();
        $config->setEnablePageToc('y');
        $this->setPageConfig($config);

        return parent::showPage();
    }

    function postOutputProcessing($output)
    {
        $output = str_replace(
            self::TEMP_PRESENTATION_TITLE_PLACEHOLDER, $this->getOriginalPresentationTitle(), $output
        );

        $output = preg_replace("/src=\"\\.\\//ims", "src=\"" . ILIAS_HTTP_PATH . "/", $output);

        return $output;
    }


    /**
     * support the addition of question info and actions below the title
     */

    /**
     * Set the HTML of a question info block below the title (number, status, ...)
     * @param string	$a_html
     */
    public function setQuestionInfoHTML($a_html)
    {
        $this->questionInfoHTML = $a_html;
    }

    /**
     * Set the HTML of a question actions block below the title
     * @param string 	$a_html
     */
    public function setQuestionActionsHTML($a_html)
    {
        $this->questionActionsHTML = $a_html;
    }

    function setQuestionComponent(QuestionComponent $component) {
        $this->component = $component;
        $this->setQuestionHTML([$this->getId() => $component->renderHtml()]);
    }
    
    function getQuestionComponent() : QuestionComponent {
        return $this->component;
    }

    function getEnteredAnswer() : string {
        return $this->component->readAnswer();
    }
    
    /**
     * Replace page toc placeholder with question info and actions
     *
     * @todo: 	support question info and actions in the page XSL directly
     * 			the current workaround avoids changing the COPage service
     *
     * @param $a_output
     * @return mixed
     */
    function insertPageToc($a_output)
    {
        if (!empty($this->questionInfoHTML) || !empty($this->questionActionsHTML))
        {
            $tpl = new ilTemplate('tpl.tst_question_subtitle_blocks.html', true, true, 'Modules/TestQuestionPool');
            $tpl->setVariable('QUESTION_INFO',$this->questionInfoHTML);
            $tpl->setVariable('QUESTION_ACTIONS',$this->questionActionsHTML);

            return str_replace("{{{{{PageTOC}}}}}",  $tpl->get(), $a_output);
        }

        return str_replace("{{{{{PageTOC}}}}}",  '', $a_output);
    }

}
