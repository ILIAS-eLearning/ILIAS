<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
* Class ilSAHSPresentationGUI
*
* GUI class for scorm learning module presentation
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilSAHSPresentationGUI: ilSCORMPresentationGUI
* @ilCtrl_Calls ilSAHSPresentationGUI: ilInfoScreenGUI, ilSCORM13PlayerGUI
* @ilCtrl_Calls ilSAHSPresentationGUI: ilLearningProgressGUI
* @ilCtrl_Calls ilSAHSPresentationGUI: ilObjSCORMLearningModuleGUI, ilObjSCORM2004LearningModuleGUI
*
* @ingroup ModulesScormAicc
*/
class ilSAHSPresentationGUI implements ilCtrlBaseClassInterface
{
    protected ilGlobalPageTemplate $tpl;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilObjSCORMLearningModuleGUI $slm_gui;
    protected int $refId;

    /**
     * @throws ilCtrlException
     */
    public function __construct()
    {
        global $DIC;
        $this->tpl = $DIC['tpl'];
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->ctrl->saveParameter($this, "ref_id");
        $this->refId = $DIC->http()->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int());
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        global $DIC;
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $ilNavigationHistory = $DIC['ilNavigationHistory'];
        $ilCtrl = $DIC->ctrl();
        $ilLocator = $DIC['ilLocator'];
        $ilObjDataCache = $DIC['ilObjDataCache'];

        $lng->loadLanguageModule("content");
        $obj_id = ilObject::_lookupObjectId($this->refId);

        // add entry to navigation history
        if ($ilAccess->checkAccess("read", "", $this->refId)) {
            $ilNavigationHistory->addItem(
                $this->refId,
                "ilias.php?cmd=infoScreen&baseClass=ilSAHSPresentationGUI&ref_id=" . $this->refId,
                "lm"
            );
        }

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $type = ilObjSAHSLearningModule::_lookupSubType($obj_id);

        if ($cmd === "downloadCertificate") {
            $scorm_gui = new ilSCORMPresentationGUI();
            $ret = $this->ctrl->forwardCommand($scorm_gui);
        }

        $this->slm_gui = new ilObjSCORMLearningModuleGUI("", $this->refId, true, false);

        if ($next_class !== "ilinfoscreengui" &&
            $cmd !== "infoScreen" &&
            $next_class !== "ilobjscorm2004learningmodulegui" &&
            $next_class !== "ilobjscormlearningmodulegui" &&
            $next_class !== "illearningprogressgui") {
            switch ($type) {
                case "scorm2004":
                    $this->ctrl->setCmdClass("ilscorm13playergui");
                    $this->slm_gui = new ilObjSCORMLearningModuleGUI("", $this->refId, true, false);
                    break;

                case "scorm":
                    $this->ctrl->setCmdClass("ilscormpresentationgui");
                    $this->slm_gui = new ilObjSCORMLearningModuleGUI("", $this->refId, true, false);
                    break;
            }
            $next_class = $this->ctrl->getNextClass($this);
        }

        switch ($next_class) {
            case "ilinfoscreengui":
                $this->outputInfoScreen();
                break;

            case "ilscorm13playergui":
                $scorm_gui = new ilSCORM13PlayerGUI();
                $ret = $this->ctrl->forwardCommand($scorm_gui);
                break;

            case "ilscormpresentationgui":
                $scorm_gui = new ilSCORMPresentationGUI();
                $ret = $this->ctrl->forwardCommand($scorm_gui);
                break;

            case "illearningprogressgui":
                $this->setInfoTabs("learning_progress");
                $new_gui = new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_REPOSITORY, $this->refId);
                $this->ctrl->forwardCommand($new_gui);
                $this->tpl->printToStdout();
                break;

            case "ilobjscorm2004learningmodulegui":
                $new_gui = new ilObjSCORM2004LearningModuleGUI([], $this->refId, true, false);
                $this->ctrl->forwardCommand($new_gui);
                $this->setInfoTabs("cont_tracking_data");
                $this->tpl->printToStdout();
                break;

            case "ilobjscormlearningmodulegui":
                $new_gui = new ilObjSCORMLearningModuleGUI("", $this->refId, true, false);
                $this->ctrl->forwardCommand($new_gui);
                $this->setInfoTabs("cont_tracking_data");
                $this->tpl->printToStdout();
                break;

                default:
                $this->$cmd();
        }
    }



//    /**
//    * output table of content
//    */
//    public function explorer(string $a_target = "sahs_content") : void
//    {
//        global $DIC;
//        $ilBench = $DIC['ilBench'];
//
//        $ilBench->start("SAHSExplorer", "initExplorer");
//
//        $this->tpl = new ilGlobalTemplate("tpl.sahs_exp_main.html", true, true, "Modules/ScormAicc");
//        $exp = new ilSCORMExplorer("ilias.php?baseClass=ilSAHSPresentationGUI&cmd=view&ref_id=" . $this->slm->getRefId(), $this->slm);
//        $exp->setTargetGet("obj_id");
//        $exp->setFrameTarget($a_target);
//
//        //$exp->setFiltered(true);
//
//        if ($_GET["scexpand"] == "") {
//            $mtree = new ilSCORMTree($this->slm->getId());
//            $expanded = $mtree->readRootId();
//        } else {
//            $expanded = $_GET["scexpand"];
//        }
//        $exp->setExpand($expanded);
//
//        $exp->forceExpandAll(true, false);
//
//        // build html-output
//        $ilBench->stop("SAHSExplorer", "initExplorer");
//
//        // set output
//        $ilBench->start("SAHSExplorer", "setOutput");
//        $exp->setOutput(0);
//        $ilBench->stop("SAHSExplorer", "setOutput");
//
//        $ilBench->start("SAHSExplorer", "getOutput");
//        $output = $exp->getOutput();
//        $ilBench->stop("SAHSExplorer", "getOutput");
//
//        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
//        $this->tpl->addBlockFile("CONTENT", "content", "tpl.sahs_explorer.html", "Modules/ScormAicc");
//        //$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_content"));
//        $this->tpl->setVariable("EXP_REFRESH", $this->lng->txt("refresh"));
//        $this->tpl->setVariable("EXPLORER", $output);
//        $this->tpl->setVariable("ACTION", "ilias.php?baseClass=ilSAHSPresentationGUI&cmd=" . $_GET["cmd"] . "&frame=" . $_GET["frame"] .
//            "&ref_id=" . $this->slm->getRefId() . "&scexpand=" . $_GET["scexpand"]);
//        $this->tpl->parseCurrentBlock();
//        $this->tpl->printToStdout();
//    }

    public function view(): void
    {
        $sc_gui_object = ilSCORMObjectGUI::getInstance($this->refId);

        if (is_object($sc_gui_object)) {
            $sc_gui_object->view();
        }

        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        $this->tpl->printToStdout();
    }

    /**
     * this one is called from the info button in the repository
     * not very nice to set cmdClass/Cmd manually, if everything
     * works through ilCtrl in the future this may be changed
     * @throws ilCtrlException
     */
    public function infoScreen(): void
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->outputInfoScreen();
    }

    /**
     * @throws ilCtrlException
     */
    public function setInfoTabs(string $a_active): void
    {
        global $DIC;

        $refId = $this->refId;

        if (
            !$DIC->access()->checkAccess('visible', '', $refId) &&
            !$DIC->access()->checkAccess('read', '', $refId)
        ) {
            $DIC['ilErr']->raiseError($this->lng->txt('msg_no_perm_read'), $DIC['ilErr']->MESSAGE); //todo
        }
        if (ilLearningProgressAccess::checkAccess($refId)) {
            $DIC->tabs()->addTab(
                "info_short",
                $this->lng->txt("info_short"),
                $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary")
            );

            $DIC->tabs()->addTab(
                "learning_progress",
                $this->lng->txt("learning_progress"),
                $this->ctrl->getLinkTargetByClass('illearningprogressgui', '')
            );
        }
        if ($DIC->access()->checkAccess("edit_learning_progress", "", $refId) || $DIC->access()->checkAccess("read_learning_progress", "", $refId)) {
            $privacy = ilPrivacySettings::getInstance();
            if ($privacy->enabledSahsProtocolData()) {
                $obj_id = ilObject::_lookupObjectId($refId);
                $type = ilObjSAHSLearningModule::_lookupSubType($obj_id);
                if ($type === "scorm2004") {
                    $DIC->tabs()->addTab(
                        "cont_tracking_data",
                        $this->lng->txt("cont_tracking_data"),
                        $this->ctrl->getLinkTargetByClass('ilobjscorm2004learningmodulegui', 'showTrackingItems')
                    );
                } elseif ($type === "scorm") {
                    $DIC->tabs()->addTab(
                        "cont_tracking_data",
                        $this->lng->txt("cont_tracking_data"),
                        $this->ctrl->getLinkTargetByClass('ilobjscormlearningmodulegui', 'showTrackingItems')
                    );
                }
            }
        }
        $DIC->tabs()->activateTab($a_active);
        $this->tpl->loadStandardTemplate();
        $this->tpl->setTitle($this->slm_gui->getObject()->getTitle());
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lm.svg"));
        $DIC['ilLocator']->addRepositoryItems();
        $DIC['ilLocator']->addItem(
            $this->slm_gui->getObject()->getTitle(),
            $this->ctrl->getLinkTarget($this, "infoScreen"),
            "",
            $refId
        );
        $this->tpl->setLocator();
    }

    /**
     * info screen
     * @throws ilCtrlException
     */
    public function outputInfoScreen(): void
    {
        global $DIC;
        $ilAccess = $DIC->access();
        $refId = $this->refId;//$this->slm_gui->object->getRefId();

        //$this->tpl->setHeaderPageTitle("PAGETITLE", " - ".$this->lm->getTitle());

        // set style sheets
//        $this->tpl->setStyleSheetLocation(ilUtil::getStyleSheetLocation());

        $this->setInfoTabs("info_short");

        $this->lng->loadLanguageModule("meta");

        $info = new ilInfoScreenGUI($this->slm_gui);
        $info->enablePrivateNotes();
        //$info->enableLearningProgress();

        $info->enableNews();
        if ($ilAccess->checkAccess("write", "", $refId)) {
            $info->enableNewsEditing();
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");
            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", "");
            }
        }

        // add read / back button
        if ($ilAccess->checkAccess("read", "", $refId)) {
            $ilToolbar = $GLOBALS['DIC']->toolbar();
            $ilToolbar->addButtonInstance($this->slm_gui->getObject()->getViewButton());
        }

        // show standard meta data section
        $info->addMetaDataSections(
            $this->slm_gui->getObject()->getId(),
            0,
            $this->slm_gui->getObject()->getType()
        );

        // forward the command
        $this->ctrl->forwardCommand($info);
        $this->tpl->printToStdout();
    }
}
