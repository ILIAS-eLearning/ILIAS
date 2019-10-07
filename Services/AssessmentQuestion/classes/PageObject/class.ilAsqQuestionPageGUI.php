<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


use ILIAS\AssessmentQuestion\UserInterface\Web\Page\Page;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\PageConfig;

/**
 * Class ilAsqQuestionPageEditorGUI
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
    public $output_mode = IL_PAGE_PRESENTATION;
    /**
     * @var bool
     */
    public $enabledpagefocus = true;
    /**
     * @var bool
     */
    public $a_output = false;


    /**
     * ilAsqQuestionPageGUI constructor.
     *
     * @param Page $page
     */
    function __construct(Page $page)
    {
        /**
          * @var \ILIAS\DI\Container $DIC
        **/
        global $DIC;

        $this->setParentType($page->getParentType());
        $this->setId($page->getId());
        $this->setLanguage($page->getLanguage());

        $this->setPageObject($page);
        $this->setPageConfig($this->getPageObject()->getPageConfig());

        $this->tool_context = $DIC->globalScreen()->tool()->context();
        $this->log = $DIC->logger()->root();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->help = $DIC->help();

        $this->plugin_admin = $DIC["ilPluginAdmin"];

        $this->page_back_title = $this->lng->txt("page");
        $this->lng->loadLanguageModule("content");
        $this->lng->loadLanguageModule("copg");

        $this->ctrl->saveParameter($this, "transl");

        // content and syntax styles
        $DIC->ui()->mainTemplate()->setCurrentBlock("ContentStyle");
        $DIC->ui()->mainTemplate()->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
        $DIC->ui()->mainTemplate()->parseCurrentBlock();
        $DIC->ui()->mainTemplate()->setCurrentBlock("SyntaxStyle");
        $DIC->ui()->mainTemplate()->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
        $DIC->ui()->mainTemplate()->parseCurrentBlock();
    }


    /**
     * @param Page $page
     *
     * @return ilAsqQuestionPageGUI
     */
    public static function getGUI(Page $page):ilAsqQuestionPageGUI {
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
        $this->setOriginalPresentationTitle($this->getPresentationTitle());
        $this->setPresentationTitle(self::TEMP_PRESENTATION_TITLE_PLACEHOLDER);

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

    /**
     * Set page config object
     *
     * @param PageConfig
     */
    function setPageConfig($page_config)
    {
        $this->page_config = $page_config;
    }

    /**
     * Get page config object
     *
     * @return	PageConfig
     */
    function getPageConfig()
    {
        return $this->page_config;
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
