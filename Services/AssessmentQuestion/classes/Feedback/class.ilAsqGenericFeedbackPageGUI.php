<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\Page;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\PageConfig;
/**
 * Class ilAsqGenericFeedbackPageGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilAsqGenericFeedbackPageGUI: ilPageEditorGUI
 * @ilCtrl_Calls ilAsqGenericFeedbackPageGUI: ilEditClipboardGUI
 * @ilCtrl_Calls ilAsqGenericFeedbackPageGUI: ilMDEditorGUI
 * @ilCtrl_Calls ilAsqGenericFeedbackPageGUI: ilPublicUserProfileGUI
 * @ilCtrl_Calls ilAsqGenericFeedbackPageGUI: ilNoteGUI
 * @ilCtrl_Calls ilAsqGenericFeedbackPageGUI: ilInternalLinkGUI
 * @ilCtrl_Calls ilAsqGenericFeedbackPageGUI: ilPropertyFormGUI
 */
class ilAsqGenericFeedbackPageGUI extends ilPageObjectGUI
{
    const CMD_EDIT = 'edit';

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
