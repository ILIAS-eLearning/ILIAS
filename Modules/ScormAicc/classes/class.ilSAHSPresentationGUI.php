<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilSAHSPresentationGUI
*
* GUI class for scorm learning module presentation
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilSAHSPresentationGUI: ilSCORMPresentationGUI
* @ilCtrl_Calls ilSAHSPresentationGUI: ilInfoScreenGUI, ilscorm13player
* @ilCtrl_Calls ilSAHSPresentationGUI: ilLearningProgressGUI, ilSCORMOfflineModeGUI
* @ilCtrl_Calls ilSAHSPresentationGUI: ilObjSCORMLearningModuleGUI, ilObjSCORM2004LearningModuleGUI
*
* @ingroup ModulesScormAicc
*/
class ilSAHSPresentationGUI
{
    public $ilias;
    public $tpl;
    public $lng;

    public function __construct()
    {
        global $DIC;
        $ilias = $DIC['ilias'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->ilias = $ilias;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        
        $this->ctrl->saveParameter($this, "ref_id");
    }
    
    /**
    * execute command
    */
    public function executeCommand()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $ilNavigationHistory = $DIC['ilNavigationHistory'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilLocator = $DIC['ilLocator'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        
        include_once "./Services/Object/classes/class.ilObjectGUI.php";
        include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";

        $lng->loadLanguageModule("content");
        $obj_id = ilObject::_lookupObjectId($_GET['ref_id']);
        
        // add entry to navigation history
        if ($ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            include_once("./Modules/ScormAicc/classes/class.ilObjSAHSLearningModuleAccess.php");
            $this->offline_mode = ilObjSAHSLearningModuleAccess::_lookupUserIsOfflineMode($obj_id);

            if (!ilObjSAHSLearningModuleAccess::_lookupEditable($obj_id)) {
                $ilNavigationHistory->addItem(
                    $_GET["ref_id"],
                    "ilias.php?cmd=infoScreen&baseClass=ilSAHSPresentationGUI&ref_id=" . $_GET["ref_id"],
                    "lm"
                );
            }
        }

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        
        $type = ilObjSAHSLearningModule::_lookupSubType($obj_id);

        if ($cmd == "downloadCertificate") {
            require_once "./Modules/ScormAicc/classes/SCORM/class.ilSCORMPresentationGUI.php";
            $scorm_gui = new ilSCORMPresentationGUI();
            $ret = $this->ctrl->forwardCommand($scorm_gui);
        }
        
        if (substr($cmd, 0, 11) == "offlineMode" || $this->offline_mode) {
            $next_class = "ilscormofflinemodegui";
        }
        
        include_once("./Modules/ScormAicc/classes/class.ilObjSCORMLearningModuleGUI.php");
        $this->slm_gui = new ilObjSCORMLearningModuleGUI("", $_GET["ref_id"], true, false);

        if ($next_class != "ilinfoscreengui" &&
            $cmd != "infoScreen" &&
            $next_class != "ilobjscorm2004learningmodulegui" &&
            $next_class != "ilobjscormlearningmodulegui" &&
            $next_class != "ilscormofflinemodegui" &&
            $next_class != "illearningprogressgui") {
            switch ($type) {
                case "scorm2004":
                    $this->ctrl->setCmdClass("ilscorm13player");
                    $this->slm_gui = new ilObjSCORMLearningModuleGUI("", $_GET["ref_id"], true, false);
                    break;
                        
                case "scorm":
                    $this->ctrl->setCmdClass("ilscormpresentationgui");
                    $this->slm_gui = new ilObjSCORMLearningModuleGUI("", $_GET["ref_id"], true, false);
                    break;
            }
            $next_class = $this->ctrl->getNextClass($this);
        }

        switch ($next_class) {
            case "ilinfoscreengui":
                $ret = $this->outputInfoScreen();
                break;

            case "ilscorm13player":
                require_once "./Modules/Scorm2004/classes/ilSCORM13Player.php";
                $scorm_gui = new ilSCORM13Player();
                $ret = $this->ctrl->forwardCommand($scorm_gui);
                break;
                
            case "ilscormpresentationgui":
                require_once "./Modules/ScormAicc/classes/SCORM/class.ilSCORMPresentationGUI.php";
                $scorm_gui = new ilSCORMPresentationGUI();
                $ret = $this->ctrl->forwardCommand($scorm_gui);
                break;

            
            case "illearningprogressgui":
                $this->setInfoTabs("learning_progress");
                include_once "./Services/Tracking/classes/class.ilLearningProgressGUI.php";
                $new_gui = new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_REPOSITORY, $_GET['ref_id']);
                $this->ctrl->forwardCommand($new_gui);
                $this->tpl->show();
                break;

            case "ilscormofflinemodegui":
                include_once "./Modules/ScormAicc/classes/class.ilSCORMOfflineModeGUI.php";
                $new_gui = new ilSCORMOfflineModeGUI($type);
                $this->ctrl->forwardCommand($new_gui);
                break;
            
            case "ilobjscorm2004learningmodulegui":
                include_once './Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModuleGUI.php';
                $new_gui = new ilObjSCORM2004LearningModuleGUI("", $_GET["ref_id"], true, false);
                $this->ctrl->forwardCommand($new_gui);
                $this->setInfoTabs("cont_tracking_data");
                $this->tpl->show();
                break;

            case "ilobjscormlearningmodulegui":
                include_once './Modules/ScormAicc/classes/class.ilObjSCORMLearningModuleGUI.php';
                $new_gui = new ilObjSCORMLearningModuleGUI("", $_GET["ref_id"], true, false);
                $this->ctrl->forwardCommand($new_gui);
                $this->setInfoTabs("cont_tracking_data");
                $this->tpl->show();
                break;

                default:
                $this->$cmd();
        }
    }

    public function attrib2arr(&$a_attributes)
    {
        $attr = array();
        
        if (!is_array($a_attributes)) {
            return $attr;
        }
        foreach ($a_attributes as $attribute) {
            $attr[$attribute->name()] = $attribute->value();
        }

        
        return $attr;
    }


    /**
    * output main menu
    */
    public function frameset()
    {
        $this->tpl = new ilTemplate("tpl.sahs_pres_frameset.html", false, false, "Modules/ScormAicc");
        $this->tpl->setVariable("REF_ID", $this->slm->getRefId());
        $this->tpl->show("DEFAULT", false);
        exit;
    }


    /**
    * output table of content
    */
    public function explorer($a_target = "sahs_content")
    {
        global $DIC;
        $ilBench = $DIC['ilBench'];

        $ilBench->start("SAHSExplorer", "initExplorer");
        
        $this->tpl = new ilTemplate("tpl.sahs_exp_main.html", true, true, "Modules/ScormAicc");
        
        require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMExplorer.php");
        $exp = new ilSCORMExplorer("ilias.php?baseClass=ilSAHSPresentationGUI&cmd=view&ref_id=" . $this->slm->getRefId(), $this->slm);
        $exp->setTargetGet("obj_id");
        $exp->setFrameTarget($a_target);
        
        //$exp->setFiltered(true);

        if ($_GET["scexpand"] == "") {
            $mtree = new ilSCORMTree($this->slm->getId());
            $expanded = $mtree->readRootId();
        } else {
            $expanded = $_GET["scexpand"];
        }
        $exp->setExpand($expanded);
        
        $exp->forceExpandAll(true, false);

        // build html-output
        //666$exp->setOutput(0);
        $ilBench->stop("SAHSExplorer", "initExplorer");
        
        // set output
        $ilBench->start("SAHSExplorer", "setOutput");
        $exp->setOutput(0);
        $ilBench->stop("SAHSExplorer", "setOutput");

        $ilBench->start("SAHSExplorer", "getOutput");
        $output = $exp->getOutput();
        $ilBench->stop("SAHSExplorer", "getOutput");

        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        $this->tpl->addBlockFile("CONTENT", "content", "tpl.sahs_explorer.html", "Modules/ScormAicc");
        //$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_content"));
        $this->tpl->setVariable("EXP_REFRESH", $this->lng->txt("refresh"));
        $this->tpl->setVariable("EXPLORER", $output);
        $this->tpl->setVariable("ACTION", "ilias.php?baseClass=ilSAHSPresentationGUI&cmd=" . $_GET["cmd"] . "&frame=" . $_GET["frame"] .
            "&ref_id=" . $this->slm->getRefId() . "&scexpand=" . $_GET["scexpand"]);
        $this->tpl->parseCurrentBlock();
        $this->tpl->show();
    }


    public function view()
    {
        $sc_gui_object = ilSCORMObjectGUI::getInstance($_GET["obj_id"]);

        if (is_object($sc_gui_object)) {
            $sc_gui_object->view();
        }

        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        $this->tpl->show();
    }

    public function api()
    {
        global $DIC;
        $ilias = $DIC['ilias'];

        $slm_obj = new ilObjSCORMLearningModule($_GET["ref_id"]);

        $this->tpl = new ilTemplate("tpl.sahs_api.html", true, true, "Modules/ScormAicc");
        $this->tpl->setVariable("USER_ID", $ilias->account->getId());
        $this->tpl->setVariable("USER_FIRSTNAME", $ilias->account->getFirstname());
        $this->tpl->setVariable("USER_LASTNAME", $ilias->account->getLastname());
        $this->tpl->setVariable("REF_ID", $_GET["ref_id"]);
        $this->tpl->setVariable("SESSION_ID", session_id());

        $this->tpl->setVariable("CODE_BASE", "http://" . $_SERVER['SERVER_NAME'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "/ilias.php")));
        $this->tpl->parseCurrentBlock();

        $this->tpl->show(false);
        exit;
    }

    public function launchSahs()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $ilDB = $DIC['ilDB'];
        
        $sco_id = ($_GET["sahs_id"] == "")
            ? $_POST["sahs_id"]
            : $_GET["sahs_id"];
        $ref_id = ($_GET["ref_id"] == "")
            ? $_POST["ref_id"]
            : $_GET["ref_id"];

        $this->slm = new ilObjSCORMLearningModule($ref_id, true);

        include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php");
        include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResource.php");
        $item = new ilSCORMItem($sco_id);

        $id_ref = $item->getIdentifierRef();
        $resource = new ilSCORMResource();
        $resource->readByIdRef($id_ref, $item->getSLMId());
        //$slm_obj = new ilObjSCORMLearningModule($_GET["ref_id"]);
        $href = $resource->getHref();
        $this->tpl = new ilTemplate("tpl.sahs_launch_cbt.html", true, true, "Modules/ScormAicc");
        $this->tpl->setVariable("HREF", $this->slm->getDataDirectory("output") . "/" . $href);

        // set item data
        $this->tpl->setVariable("LAUNCH_DATA", $item->getDataFromLms());
        $this->tpl->setVariable("MAST_SCORE", $item->getMasteryScore());
        $this->tpl->setVariable("MAX_TIME", $item->getMaxTimeAllowed());
        $this->tpl->setVariable("LIMIT_ACT", $item->getTimeLimitAction());

        // set alternative API name
        if ($this->slm->getAPIAdapterName() != "API") {
            $this->tpl->setCurrentBlock("alt_api_ref");
            $this->tpl->setVariable("API_NAME", $this->slm->getAPIAdapterName());
            $this->tpl->parseCurrentBlock();
        }

        $val_set = $ilDB->queryF(
            '
		SELECT * FROM scorm_tracking 
		WHERE user_id = %s
		AND sco_id = %s
		AND obj_id = %s',
            array('integer','integer','integer'),
            array($ilUser->getId(),$sco_id,$this->slm->getId())
        );
        
        $re_value = array();
        
        while ($val_rec = $ilDB->fetchAssoc($val_set)) {
            $val_rec["rvalue"] = str_replace("\r\n", "\n", $val_rec["rvalue"]);
            $val_rec["rvalue"] = str_replace("\r", "\n", $val_rec["rvalue"]);
            $val_rec["rvalue"] = str_replace("\n", "\\n", $val_rec["rvalue"]);
            $re_value[$val_rec["lvalue"]] = $val_rec["rvalue"];
        }

        foreach ($re_value as $var => $value) {
            switch ($var) {
                case "cmi.core.lesson_location":
                case "cmi.core.lesson_status":
                case "cmi.core.entry":
                case "cmi.core.score.raw":
                case "cmi.core.score.max":
                case "cmi.core.score.min":
                case "cmi.core.total_time":
                case "cmi.core.exit":
                case "cmi.suspend_data":
                case "cmi.comments":
                case "cmi.student_preference.audio":
                case "cmi.student_preference.language":
                case "cmi.student_preference.speed":
                case "cmi.student_preference.text":
                    $this->setSingleVariable($var, $value);
                    break;

                case "cmi.objectives._count":
                    $this->setSingleVariable($var, $value);
                    $this->setArray("cmi.objectives", $value, "id", $re_value);
                    $this->setArray("cmi.objectives", $value, "score.raw", $re_value);
                    $this->setArray("cmi.objectives", $value, "score.max", $re_value);
                    $this->setArray("cmi.objectives", $value, "score.min", $re_value);
                    $this->setArray("cmi.objectives", $value, "status", $re_value);
                    break;

                case "cmi.interactions._count":
                    $this->setSingleVariable($var, $value);
                    $this->setArray("cmi.interactions", $value, "id", $re_value);
                    for ($i=0; $i<$value; $i++) {
                        $var2 = "cmi.interactions." . $i . ".objectives._count";
                        if (isset($v_array[$var2])) {
                            $cnt = $v_array[$var2];
                            $this->setArray(
                                "cmi.interactions." . $i . ".objectives",
                                $cnt,
                                "id",
                                $re_value
                            );
                            /*
                            $this->setArray("cmi.interactions.".$i.".objectives",
                                $cnt, "score.raw", $re_value);
                            $this->setArray("cmi.interactions.".$i.".objectives",
                                $cnt, "score.max", $re_value);
                            $this->setArray("cmi.interactions.".$i.".objectives",
                                $cnt, "score.min", $re_value);
                            $this->setArray("cmi.interactions.".$i.".objectives",
                                $cnt, "status", $re_value);*/
                        }
                    }
                    $this->setArray("cmi.interactions", $value, "time", $re_value);
                    $this->setArray("cmi.interactions", $value, "type", $re_value);
                    for ($i=0; $i<$value; $i++) {
                        $var2 = "cmi.interactions." . $i . ".correct_responses._count";
                        if (isset($v_array[$var2])) {
                            $cnt = $v_array[$var2];
                            $this->setArray(
                                "cmi.interactions." . $i . ".correct_responses",
                                $cnt,
                                "pattern",
                                $re_value
                            );
                            $this->setArray(
                                "cmi.interactions." . $i . ".correct_responses",
                                $cnt,
                                "weighting",
                                $re_value
                            );
                        }
                    }
                    $this->setArray("cmi.interactions", $value, "student_response", $re_value);
                    $this->setArray("cmi.interactions", $value, "result", $re_value);
                    $this->setArray("cmi.interactions", $value, "latency", $re_value);
                    break;
            }
        }

        global $DIC;
        $lng = $DIC['lng'];
        $this->tpl->setCurrentBlock("switch_icon");
        $this->tpl->setVariable("SCO_ID", $_GET["sahs_id"]);
        $this->tpl->setVariable("SCO_ICO", ilUtil::getImagePath("scorm/running.svg"));
        $this->tpl->setVariable(
            "SCO_ALT",
            $lng->txt("cont_status") . ": "
            . $lng->txt("cont_sc_stat_running")
        );
        $this->tpl->parseCurrentBlock();

        // lesson mode
        $lesson_mode = $this->slm->getDefaultLessonMode();
        if ($this->slm->getAutoReview()) {
            if ($re_value["cmi.core.lesson_status"] == "completed" ||
                $re_value["cmi.core.lesson_status"] == "passed" ||
                $re_value["cmi.core.lesson_status"] == "failed") {
                $lesson_mode = "review";
            }
        }
        $this->tpl->setVariable("LESSON_MODE", $lesson_mode);

        // credit mode
        if ($lesson_mode == "normal") {
            $this->tpl->setVariable(
                "CREDIT_MODE",
                str_replace("_", "-", $this->slm->getCreditMode())
            );
        } else {
            $this->tpl->setVariable("CREDIT_MODE", "no-credit");
        }

        // init cmi.core.total_time, cmi.core.lesson_status and cmi.core.entry
        $sahs_obj_id = ilObject::_lookupObjId($_GET["ref_id"]);
        if (!isset($re_value["cmi.core.total_time"])) {
            $item->insertTrackData("cmi.core.total_time", "0000:00:00", $sahs_obj_id);
        }
        if (!isset($re_value["cmi.core.lesson_status"])) {
            $item->insertTrackData("cmi.core.lesson_status", "not attempted", $sahs_obj_id);
        }
        if (!isset($re_value["cmi.core.entry"])) {
            $item->insertTrackData("cmi.core.entry", "", $sahs_obj_id);
        }

        $this->tpl->show();
    }

    public function finishSahs()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $this->tpl = new ilTemplate("tpl.sahs_finish_cbt.html", true, true, "Modules/ScormAicc");
        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

        $this->tpl->setCurrentBlock("switch_icon");
        $this->tpl->setVariable("SCO_ID", $_GET["sahs_id"]);
        $this->tpl->setVariable(
            "SCO_ICO",
            ilUtil::getImagePath(
            "scorm/" . str_replace(" ", "_", $_GET["status"]) . '.svg'
        )
        );
        $this->tpl->setVariable(
            "SCO_ALT",
            $lng->txt("cont_status") . ": "
            . $lng->txt("cont_sc_stat_" . str_replace(" ", "_", $_GET["status"])) . ", "
            . $lng->txt("cont_total_time") . ": "
            . $_GET["totime"]
        );
        $this->tpl->setVariable("SCO_LAUNCH_ID", $_GET["launch"]);
        $this->tpl->parseCurrentBlock();
        $this->tpl->show();
    }

    public function unloadSahs()
    {
        $this->tpl = new ilTemplate("tpl.sahs_unload_cbt.html", true, true, "Modules/ScormAicc");
        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        $this->tpl->setVariable("SCO_ID", $_GET["sahs_id"]);
        $this->tpl->show();
    }


    public function launchAsset()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $ilDB = $DIC['ilDB'];

        $sco_id = ($_GET["asset_id"] == "")
            ? $_POST["asset_id"]
            : $_GET["asset_id"];
        $ref_id = ($_GET["ref_id"] == "")
            ? $_POST["ref_id"]
            : $_GET["ref_id"];

        $this->slm = new ilObjSCORMLearningModule($ref_id, true);

        include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php");
        include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResource.php");
        $item = new ilSCORMItem($sco_id);

        $id_ref = $item->getIdentifierRef();
        $resource = new ilSCORMResource();
        $resource->readByIdRef($id_ref, $item->getSLMId());
        $href = $resource->getHref();
        $this->tpl->setVariable("HREF", $this->slm->getDataDirectory("output") . "/" . $href);
        $this->tpl = new ilTemplate("tpl.scorm_launch_asset.html", true, true, "Modules/ScormAicc");
        $this->tpl->setVariable("HREF", $this->slm->getDataDirectory("output") . "/" . $href);
        $this->tpl->show();
    }


    /**
    * set single value
    */
    public function setSingleVariable($a_var, $a_value)
    {
        $this->tpl->setCurrentBlock("set_value");
        $this->tpl->setVariable("VAR", $a_var);
        $this->tpl->setVariable("VALUE", $a_value);
        $this->tpl->parseCurrentBlock();
    }

    /**
    * set single value
    */
    public function setArray($a_left, $a_value, $a_name, &$v_array)
    {
        for ($i=0; $i<$a_value; $i++) {
            $var = $a_left . "." . $i . "." . $a_name;
            if (isset($v_array[$var])) {
                $this->tpl->setCurrentBlock("set_value");
                $this->tpl->setVariable("VAR", $var);
                $this->tpl->setVariable("VALUE", $v_array[$var]);
                $this->tpl->parseCurrentBlock();
            }
        }
    }
    
    /**
    * this one is called from the info button in the repository
    * not very nice to set cmdClass/Cmd manually, if everything
    * works through ilCtrl in the future this may be changed
    */
    public function infoScreen()
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->outputInfoScreen();
    }
    
    public function setInfoTabs($a_active)
    {
        global $DIC;

        if (
            !$DIC->access()->checkAccess('visible', '', (int) $_GET["ref_id"]) &&
            !$DIC->access()->checkAccess('read', '', (int) $_GET["ref_id"])
        ) {
            $DIC['ilErr']->raiseError($this->lng->txt('msg_no_perm_read'), $DIC['ilErr']->MESSAGE);
        }
        
        // $ilTabs->clearTargets();
        // #9658 / #11753
        include_once "Services/Tracking/classes/class.ilLearningProgressAccess.php";
        if (ilLearningProgressAccess::checkAccess($_GET["ref_id"])) {
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
        if ($DIC->access()->checkAccess("edit_learning_progress", "", $_GET["ref_id"]) || $DIC->access()->checkAccess("read_learning_progress", "", $_GET["ref_id"])) {
            include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
            $privacy = ilPrivacySettings::_getInstance();
            if ($privacy->enabledSahsProtocolData()) {
                include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
                $obj_id = ilObject::_lookupObjectId($_GET['ref_id']);
                $type = ilObjSAHSLearningModule::_lookupSubType($obj_id);
                if ($type == "scorm2004") {
                    $DIC->tabs()->addTab(
                        "cont_tracking_data",
                        $this->lng->txt("cont_tracking_data"),
                        $this->ctrl->getLinkTargetByClass('ilobjscorm2004learningmodulegui', 'showTrackingItems')
                    );
                } elseif ($type == "scorm") {
                    $DIC->tabs()->addTab(
                        "cont_tracking_data",
                        $this->lng->txt("cont_tracking_data"),
                        $this->ctrl->getLinkTargetByClass('ilobjscormlearningmodulegui', 'showTrackingItems')
                    );
                }
            }
        }
        $DIC->tabs()->activateTab($a_active);
        $this->tpl->getStandardTemplate();
        $this->tpl->setTitle($this->slm_gui->object->getTitle());
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lm.svg"));
        $DIC['ilLocator']->addRepositoryItems();
        $DIC['ilLocator']->addItem(
            $this->slm_gui->object->getTitle(),
            $this->ctrl->getLinkTarget($this, "infoScreen"),
            "",
            $_GET["ref_id"]
        );
        $this->tpl->setLocator();
    }

    /**
    * info screen
    */
    public function outputInfoScreen()
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        //$this->tpl->setHeaderPageTitle("PAGETITLE", " - ".$this->lm->getTitle());

        // set style sheets
        /*
        if (!$this->offlineMode())
        {
            $this->tpl->setStyleSheetLocation(ilUtil::getStyleSheetLocation());
        }
        else
        {
            $style_name = $this->ilias->account->prefs["style"].".css";;
            $this->tpl->setStyleSheetLocation("./".$style_name);
        }*/
                
        $this->setInfoTabs("info_short");
        
        $this->lng->loadLanguageModule("meta");

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");

        $info = new ilInfoScreenGUI($this->slm_gui);
        $info->enablePrivateNotes();
        //$info->enableLearningProgress();

        $info->enableNews();
        if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) {
            $info->enableNewsEditing();
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");
            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", true);
            }
        }

        // add read / back button
        if ($ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            $ilToolbar = $GLOBALS['DIC']->toolbar();
            $ilToolbar->addButtonInstance($this->slm_gui->object->getViewButton());
        }
        
        // show standard meta data section
        $info->addMetaDataSections(
            $this->slm_gui->object->getId(),
            0,
            $this->slm_gui->object->getType()
        );

        /*
        if ($this->offlineMode())
        {
            $this->tpl->setContent($info->getHTML());
            return $this->tpl->get();
        }
        else
        {*/
        // forward the command
        $this->ctrl->forwardCommand($info);
        $this->tpl->show();
        //}
    }
}
