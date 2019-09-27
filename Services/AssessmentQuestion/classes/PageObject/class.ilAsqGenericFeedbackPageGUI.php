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
    /**
     * ilAsqQuestionPageGUI constructor.
     *
     * @param Page $page
     */
    function __construct(Page $page)
    {
        parent::__construct(
            $page->getParentType(), $page->getId()
        );
    }
}
