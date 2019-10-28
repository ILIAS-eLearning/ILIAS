<?php

require_once "./Services/AssessmentQuestion/examples/class.exAsqPlayerGUI.php";

/**
 * Class exAsqExamplesGUI
 *
 * @author            studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author            Adrian Lüthi <al@studer-raimann.ch>
 * @author            Björn Heyser <bh@bjoernheyser.de>
 * @author            Martin Studer <ms@studer-raimann.ch>
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_Calls      exAsqExamplesGUI: exAsqPlayerGUI
 * @ilCtrl_Calls      exAsqExamplesGUI: exAsqImportGUI
 * @ilCtrl_Calls      exAsqExamplesGUI: exAsqAuthoringGUI
 * @ilCtrl_IsCalledBy exAsqExamplesGUI: ilObjTestGUI
 */
class exAsqExamplesGUI
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
            case strtolower(exAsqPlayerGUI::class):
            default:
                $asq_player_gui = new exAsqPlayerGUI();
                $DIC->ctrl()->forwardCommand($asq_player_gui);
                break;
            case strtolower(exAsqImportGUI::class):
                $asq_import_gui = new exAsqImportGUI();
                $DIC->ctrl()->forwardCommand($asq_import_gui);
                break;
        }
    }


    public function renderSubTabs()
    {
        global $DIC;

        $DIC->tabs()
            ->addSubTab(exAsqPlayerGUI::CMD_SHOW_TEST_START, exAsqPlayerGUI::CMD_SHOW_TEST_START,
                $DIC->ctrl()->getLinkTargetByClass(['ilRepositoryGUI', 'ilObjTestGUI', 'exAsqExamplesGUI', 'exAsqPlayerGUI'], exAsqPlayerGUI::CMD_SHOW_TEST_START));

        $DIC->tabs()
            ->addSubTab(exAsqAuthoringGUI::CMD_SHOW_EDIT_LIST, exAsqAuthoringGUI::CMD_SHOW_EDIT_LIST,
                $DIC->ctrl()->getLinkTargetByClass(['ilRepositoryGUI', 'ilObjTestGUI', 'exAsqExamplesGUI', 'exAsqAuthoringGUI'], exAsqAuthoringGUI::CMD_SHOW_EDIT_LIST));

        $DIC->tabs()
            ->addSubTab(exAsqImportGUI::CMD_SHOW_IMPORT_FORM, exAsqImportGUI::CMD_SHOW_IMPORT_FORM,
                $DIC->ctrl()->getLinkTargetByClass(['ilRepositoryGUI', 'ilObjTestGUI', 'exAsqExamplesGUI', 'exAsqImportGUI'], exAsqImportGUI::CMD_SHOW_IMPORT_FORM));
    }
}