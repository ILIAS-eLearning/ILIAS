<?php

use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringService;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\entityIdBuilder;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionConfig;
use ILIAS\Services\AssessmentQuestion\PublicApi\Processing\ProcessingQuestion;
use ILIAS\Services\AssessmentQuestion\PublicApi\Processing\ProcessingService;
use ILIAS\UI\Component\Link\Link;

/**
 * Class asqDebugGUI
 *
 * @author            studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author            Adrian Lüthi <al@studer-raimann.ch>
 * @author            Björn Heyser <bh@bjoernheyser.de>
 * @author            Martin Studer <ms@studer-raimann.ch>
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_Calls      asqDebugGUI: asqPlayerGUI
 * @ilCtrl_Calls      asqDebugGUI: asqImportGUI
 * @ilCtrl_Calls      asqDebugGUI: asqAuthoringGUI
 * @ilCtrl_IsCalledBy asqDebugGUI: ilObjTestGUI
 */
class asqDebugGUI
{

    public function __construct()
    {
        $this->renderSubTabs();
    }


    /**
     * execute command
     */
    function executeCommand()
    {
        global $DIC;

        switch (strtolower($DIC->ctrl()->getNextClass())) {
            case strtolower(asqPlayerGUI::class):
            default:
                $asq_player_gui = new asqPlayerGUI();
                $DIC->ctrl()->forwardCommand($asq_player_gui);
                break;
            case strtolower(asqImportGUI::class):
                $asq_import_gui = new asqImportGUI();
                $DIC->ctrl()->forwardCommand($asq_import_gui);
                break;
        }
    }


    public function renderSubTabs()
    {
        global $DIC;

        $DIC->tabs()
            ->addSubTab(asqPlayerGUI::CMD_SHOW_TEST_START, asqPlayerGUI::CMD_SHOW_TEST_START,
                $DIC->ctrl()->getLinkTargetByClass(['ilRepositoryGUI', 'ilObjTestGUI', 'asqDebugGUI', 'asqPlayerGUI'], asqPlayerGUI::CMD_SHOW_TEST_START));

        $DIC->tabs()
            ->addSubTab(asqAuthoringGUI::CMD_SHOW_EDIT_LIST, asqAuthoringGUI::CMD_SHOW_EDIT_LIST,
                $DIC->ctrl()->getLinkTargetByClass(['ilRepositoryGUI', 'ilObjTestGUI', 'asqDebugGUI', 'asqAuthoringGUI'], asqAuthoringGUI::CMD_SHOW_EDIT_LIST));

        $DIC->tabs()
            ->addSubTab(asqImportGUI::CMD_SHOW_IMPORT_FORM, asqImportGUI::CMD_SHOW_IMPORT_FORM,
                $DIC->ctrl()->getLinkTargetByClass(['ilRepositoryGUI', 'ilObjTestGUI', 'asqDebugGUI', 'asqImportGUI'], asqImportGUI::CMD_SHOW_IMPORT_FORM));
    }
}