<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\Page;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\PageConfig;
/**
 * Class ilAsqGenericHintsPageGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilAsqGenericHintsPageGUI: ilPageEditorGUI
 * @ilCtrl_Calls ilAsqGenericHintsPageGUI: ilEditClipboardGUI
 * @ilCtrl_Calls ilAsqGenericHintsPageGUI: ilMDEditorGUI
 * @ilCtrl_Calls ilAsqGenericHintsPageGUI: ilPublicUserProfileGUI
 * @ilCtrl_Calls ilAsqGenericHintsPageGUI: ilNoteGUI
 * @ilCtrl_Calls ilAsqGenericHintsPageGUI: ilInternalLinkGUI
 * @ilCtrl_Calls ilAsqGenericHintsPageGUI: ilPropertyFormGUI
 */
class ilAsqHintsPageGUI extends ilPageObjectGUI
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
