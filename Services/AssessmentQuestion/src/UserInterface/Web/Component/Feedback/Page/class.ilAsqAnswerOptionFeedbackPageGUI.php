<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\Page;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\PageConfig;


/**
 * Class ilAsqAnswerOptionFeedbackPageGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilAsqAnswerOptionFeedbackPageGUI: ilPageEditorGUI
 * @ilCtrl_Calls ilAsqAnswerOptionFeedbackPageGUI: ilEditClipboardGUI
 * @ilCtrl_Calls ilAsqAnswerOptionFeedbackPageGUI: ilMDEditorGUI
 * @ilCtrl_Calls ilAsqAnswerOptionFeedbackPageGUI: ilPublicUserProfileGUI
 * @ilCtrl_Calls ilAsqAnswerOptionFeedbackPageGUI: ilNoteGUI
 * @ilCtrl_Calls ilAsqAnswerOptionFeedbackPageGUI: ilInternalLinkGUI
 * @ilCtrl_Calls ilAsqAnswerOptionFeedbackPageGUI: ilPropertyFormGUI
 */
class ilAsqAnswerOptionFeedbackPageGUI extends ilPageObjectGUI
{
    const PAGE_TYPE = 'asqa';
    const CMD_EDIT = 'edit';
    const VAR_ANSWER_OPTION_INT_ID = "answer_option_int_id";
    /**
     * @var QuestionDto
     */
    protected $question;
    /**
     * @var int
     */
    protected $answer_option_int_id;

    /**
     * ilAsqQuestionPageGUI constructor.
     *
     * @param QuestionDto $question
     */
    function __construct(QuestionDto $question)
    {
        /**
         * @var \ILIAS\DI\Container $DIC
         **/
        global $DIC;

        $this->answer_option_int_id = $_GET[self::VAR_ANSWER_OPTION_INT_ID];
        $DIC->ctrl()->saveParameter($this, self::VAR_ANSWER_OPTION_INT_ID);

        $page = Page::getPage(self::PAGE_TYPE,$question->getQuestionIntId(), $this->answer_option_int_id,$DIC->language()->getDefaultLanguage());

        $this->setParentType($page->getParentType());
        $this->setId($page->getId());
        $this->setLanguage($page->getLanguage());

        $this->setPageObject($page);
        $this->setPageConfig($this->getPageObject()->getPageConfig());

        $this->log = $DIC->logger()->root();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->help = $DIC->help();
        $this->tabs_gui = $DIC->tabs();
        $this->tpl  = $DIC->ui()->mainTemplate();

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
}
