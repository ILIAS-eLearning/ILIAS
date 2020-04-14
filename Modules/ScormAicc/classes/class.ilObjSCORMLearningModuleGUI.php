<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";
require_once("./Services/FileSystem/classes/class.ilFileSystemGUI.php");
require_once("Services/User/classes/class.ilObjUser.php");

require_once("./Modules/ScormAicc/classes/class.ilObjSAHSLearningModuleGUI.php");
require_once("./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php");

/**
* Class ilObjSCORMLearningModuleGUI
*
* @author Alex Killing <alex.killing@gmx.de>, Hendrik Holtmann <holtmann@mac.com>
* $Id$
*
* @ilCtrl_Calls ilObjSCORMLearningModuleGUI: ilFileSystemGUI, ilObjectMetaDataGUI, ilPermissionGUI, ilLearningProgressGUI
* @ilCtrl_Calls ilObjSCORMLearningModuleGUI: ilInfoScreenGUI
* @ilCtrl_Calls ilObjSCORMLearningModuleGUI: ilCertificateGUI
* @ilCtrl_Calls ilObjSCORMLearningModuleGUI: ilSCORMOfflineModeUsersTableGUI
* @ilCtrl_Calls ilObjSCORMLearningModuleGUI: ilSCORMTrackingItemsPerScoFilterGUI, ilSCORMTrackingItemsPerUserFilterGUI, ilSCORMTrackingItemsTableGUI
* @ilCtrl_Calls ilObjSCORMLearningModuleGUI: ilLTIProviderObjectSettingGUI
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORMLearningModuleGUI extends ilObjSAHSLearningModuleGUI
{
    const EXPORT_UNDEF = 0;
    const EXPORT_ALL = 1;
    const EXPORT_SELECTED = 2;

    const EXPORT_TYPE_RAW = 1;
    const EXPORT_TYPE_SUCCESS = 2;

    /**
    * Constructor
    *
    * @access	public
    */
    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
    {
        global $DIC;
        $lng = $DIC['lng'];

        $lng->loadLanguageModule("content");
        $lng->loadLanguageModule("search");
        
        $this->type = "sahs";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
    }

    /**
    * assign scorm object to scorm gui object
    */
    public function assignObject()
    {
        if ($this->id != 0) {
            if ($this->call_by_reference) {
                $this->object = new ilObjSCORMLearningModule($this->id, true);
            } else {
                $this->object = new ilObjSCORMLearningModule($this->id, false);
            }
        }
    }

    /**
    * scorm module properties
    */
    public function properties()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $tree = $DIC['tree'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ilToolbar = $DIC['ilToolbar'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilSetting = $DIC['ilSetting'];
        $ilTabs = $DIC['ilTabs'];

        $lng->loadLanguageModule("style");

        ilObjSAHSLearningModuleGUI::setSettingsSubTabs();
        $ilTabs->setSubTabActive('cont_settings');

        // view
        $ilToolbar->addButtonInstance($this->object->getViewButton());

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($ilCtrl->getFormAction($this));
        $this->form->setTitle($this->lng->txt("cont_lm_properties"));
        
        //check/select only once
        $this->object->checkMasteryScoreValues();

        //title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "Fobject_title");
        $ti->setMaxLength(200);
        $ti->setValue($this->object->getTitle());
        $this->form->addItem($ti);
        
        //description
        $ti = new ilTextAreaInputGUI($this->lng->txt("description"), "Fobject_description");
        $ti->setValue($this->object->getDescription());
        $this->form->addItem($ti);

        // SCORM-type
        $ne = new ilNonEditableValueGUI($this->lng->txt("type"), "");
        $ne->setValue($this->lng->txt("lm_type_" . ilObjSAHSLearningModule::_lookupSubType($this->object->getID())));
        $this->form->addItem($ne);

        // version
        $ne = new ilNonEditableValueGUI($this->lng->txt("cont_sc_version"), "");
        $ne->setValue($this->object->getModuleVersion());
        $ne->setInfo($this->lng->txt("cont_sc_version_info"));
        $this->form->addItem($ne);

        //
        // activation
        //
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("activation"));
        $this->form->addItem($sh);

        // online
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_online"), "cobj_online");
        if (!$this->object->getOfflineStatus()) {
            $cb->setChecked(true);
        }
        $cb->setInfo($this->lng->txt("cont_online_info"));
        $this->form->addItem($cb);

        // offline Mode
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_offline_mode_allow"), "cobj_offline_mode");
        $cb->setValue("y");
        $cb->setChecked($this->object->getOfflineMode());
        include_once("./Modules/ScormAicc/classes/class.ilSCORMOfflineMode.php");
        if ($this->object->getOfflineMode() == true && ilSCORMOfflineMode::checkIfAnyoneIsInOfflineMode($this->object->getID()) == true) {
            $cb->setDisabled(true);
            $cb->setInfo($this->lng->txt("cont_offline_mode_disable_not_allowed_info"));
        } else {
            $cb->setInfo($this->lng->txt("cont_offline_mode_allow_info"));
        }
        $this->form->addItem($cb);

        //
        // presentation
        //
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("cont_presentation"));
        $this->form->addItem($sh);
        
        $radg = new ilRadioGroupInputGUI($lng->txt("cont_open"), "open_mode");
        $op0 = new ilRadioOption($this->lng->txt("cont_open_normal"), "0");
        $radg->addOption($op0);
        $op1 = new ilRadioOption($this->lng->txt("cont_open_iframe"), "1");
        $radg->addOption($op1);
        $op2 = new ilRadioOption($this->lng->txt("cont_open_window"), "5");
        $radg->addOption($op2);
        $radg->setValue($this->object->getOpenMode());

        // width
        $ni = new ilNumberInputGUI($this->lng->txt("cont_width"), "width_0");
        $ni->setMaxLength(4);
        $ni->setSize(4);
        $ni->setValue($this->object->getWidth());
        $op1->addSubItem($ni);
        $ni = new ilNumberInputGUI($this->lng->txt("cont_width"), "width_1");
        $ni->setMaxLength(4);
        $ni->setSize(4);
        $ni->setValue($this->object->getWidth());
        $op2->addSubItem($ni);
        // height
        $ni = new ilNumberInputGUI($this->lng->txt("cont_height"), "height_0");
        $ni->setMaxLength(4);
        $ni->setSize(4);
        $ni->setValue($this->object->getHeight());
        $ni->setInfo($this->lng->txt("cont_width_height_info"));
        $op1->addSubItem($ni);
        $ni = new ilNumberInputGUI($this->lng->txt("cont_height"), "height_1");
        $ni->setMaxLength(4);
        $ni->setSize(4);
        $ni->setValue($this->object->getHeight());
        $ni->setInfo($this->lng->txt("cont_width_height_info"));
        $op2->addSubItem($ni);

        $this->form->addItem($radg);
        
        // auto navigation to last visited item
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_auto_last_visited"), "cobj_auto_last_visited");
        $cb->setValue("y");
        $cb->setChecked($this->object->getAuto_last_visited());
        $cb->setInfo($this->lng->txt("cont_auto_last_visited_info"));
        $this->form->addItem($cb);

        // auto continue
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_sc_auto_continue"), "auto_continue");
        $cb->setValue("y");
        $cb->setChecked($this->object->getAutoContinue());
        $cb->setInfo($this->lng->txt("cont_sc_auto_continue_info"));
        $this->form->addItem($cb);

        //
        // scorm options
        //
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("cont_scorm_options"));
        $this->form->addItem($sh);
        
        // lesson mode
        $options = array("normal" => $this->lng->txt("cont_sc_less_mode_normal"),
                "browse" => $this->lng->txt("cont_sc_less_mode_browse"));
        $si = new ilSelectInputGUI($this->lng->txt("cont_def_lesson_mode"), "lesson_mode");
        $si->setOptions($options);
        $si->setValue($this->object->getDefaultLessonMode());
        $this->form->addItem($si);
        
        // credit mode
        $options = array("credit" => $this->lng->txt("cont_credit_on"),
            "no_credit" => $this->lng->txt("cont_credit_off"));
        $si = new ilSelectInputGUI($this->lng->txt("cont_credit_mode"), "credit_mode");
        $si->setOptions($options);
        $si->setValue($this->object->getCreditMode());
        $si->setInfo($this->lng->txt("cont_credit_mode_info"));
        $this->form->addItem($si);
        
        // set lesson mode review when completed
        $options = array(
            "n" => $this->lng->txt("cont_sc_auto_review_no"),
//			"r" => $this->lng->txt("cont_sc_auto_review_completed_not_failed_or_passed"),
//			"p" => $this->lng->txt("cont_sc_auto_review_passed"),
//			"q" => $this->lng->txt("cont_sc_auto_review_passed_or_failed"),
//			"c" => $this->lng->txt("cont_sc_auto_review_completed"),
//			"d" => $this->lng->txt("cont_sc_auto_review_completed_and_passed"),
            "y" => $this->lng->txt("cont_sc_auto_review_completed_or_passed"),
            );
        $si = new ilSelectInputGUI($this->lng->txt("cont_sc_auto_review_2004"), "auto_review");
        $si->setOptions($options);
        $si->setValue($this->object->getAutoReviewChar());
        // $si->setInfo($this->lng->txt("cont_sc_auto_review_info_12"));
        $this->form->addItem($si);

        // mastery_score
        if ($this->object->getMasteryScoreValues() != "") {
            $ni = new ilNumberInputGUI($this->lng->txt("cont_mastery_score_12"), "mastery_score");
            $ni->setMaxLength(3);
            $ni->setSize(3);
            $ni->setValue($this->object->getMasteryScore());
            $ni->setInfo($this->lng->txt("cont_mastery_score_12_info") . $this->object->getMasteryScoreValues());
            $this->form->addItem($ni);
        }

        //
        // rte settings
        //
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("cont_rte_settings"));
        $this->form->addItem($sh);
        
        // unlimited session timeout
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_sc_usession"), "cobj_session");
        $cb->setValue("y");
        $cb->setChecked($this->object->getSession());
        $cb->setInfo($this->lng->txt("cont_sc_usession_info"));
        $this->form->addItem($cb);
        
        // storage of interactions
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_interactions"), "cobj_interactions");
        $cb->setValue("y");
        $cb->setChecked($this->object->getInteractions());
        $cb->setInfo($this->lng->txt("cont_interactions_info_12"));
        $this->form->addItem($cb);
        
        // objectives
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_objectives"), "cobj_objectives");
        $cb->setValue("y");
        $cb->setChecked($this->object->getObjectives());
        $cb->setInfo($this->lng->txt("cont_objectives_info"));
        $this->form->addItem($cb);

        // time from lms
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_time_from_lms"), "cobj_time_from_lms");
        $cb->setValue("y");
        $cb->setChecked($this->object->getTime_from_lms());
        $cb->setInfo($this->lng->txt("cont_time_from_lms_info"));
        $this->form->addItem($cb);

        // check values
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_check_values"), "cobj_check_values");
        $cb->setValue("y");
        $cb->setChecked($this->object->getCheck_values());
        $cb->setInfo($this->lng->txt("cont_check_values_info"));
        $this->form->addItem($cb);

        // auto cmi.core.exit to suspend
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_auto_suspend"), "cobj_auto_suspend");
        $cb->setValue("y");
        $cb->setChecked($this->object->getAutoSuspend());
        $cb->setInfo($this->lng->txt("cont_auto_suspend_info"));
        $this->form->addItem($cb);
        
        // settings for student_id
        $options = array(
            0 => $this->lng->txt("cont_sc_id_setting_user_id"),
            1 => $this->lng->txt("cont_sc_id_setting_user_login"),
            2 => $this->lng->txt("cont_sc_id_setting_user_id_plus_ref_id"),
            3 => $this->lng->txt("cont_sc_id_setting_user_login_plus_ref_id"),
            4 => $this->lng->txt("cont_sc_id_setting_user_id_plus_obj_id"),
            5 => $this->lng->txt("cont_sc_id_setting_user_login_plus_obj_id")
            );
        $si = new ilSelectInputGUI($this->lng->txt("cont_sc_id_setting"), "id_setting");
        $si->setOptions($options);
        $si->setValue($this->object->getIdSetting());
        $si->setInfo($this->lng->txt("cont_sc_id_setting_info"));
        $this->form->addItem($si);

        // settings for student_name
        $options = array(
            0 => $this->lng->txt("cont_sc_name_setting_last_firstname"),
            1 => $this->lng->txt("cont_sc_name_setting_first_lastname"),
            2 => $this->lng->txt("cont_sc_name_setting_fullname"),
            3 => $this->lng->txt("cont_sc_name_setting_salutation_lastname"),
            4 => $this->lng->txt("cont_sc_name_setting_first_name"),
            9 => $this->lng->txt("cont_sc_name_setting_no_name")
            );
        $si = new ilSelectInputGUI($this->lng->txt("cont_sc_name_setting"), "name_setting");
        $si->setOptions($options);
        $si->setValue($this->object->getNameSetting());
        $si->setInfo($this->lng->txt("cont_sc_name_setting_info"));
        $this->form->addItem($si);

        //
        // debugging
        //
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("cont_debugging"));
        $this->form->addItem($sh);

        // test tool
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_debug"), "cobj_debug");
        $cb->setValue("y");
        $cb->setChecked($this->object->getDebug());
        if ($this->object->getDebugActivated() == false) {
            $cb->setDisabled(true);
            $cb->setInfo($this->lng->txt("cont_debug_deactivated"));
        } else {
            $cb->setInfo($this->lng->txt("cont_debug_deactivate12"));
        }
        $this->form->addItem($cb);
        $this->form->addCommandButton("saveProperties", $lng->txt("save"));

        $tpl->setContent($this->form->getHTML());
    }

    /**
    * upload new version of module
    */
    public function newModuleVersion()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];
        ilObjSAHSLearningModuleGUI::setSettingsSubTabs();
        $ilTabs->setSubTabActive('cont_sc_new_version');

        $obj_id = ilObject::_lookupObjectId($_GET['ref_id']);
        $type = ilObjSAHSLearningModule::_lookupSubType($obj_id);

        // display import form
        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.scorm_new_version_import.html", "Modules/ScormAicc");

        $this->tpl->setVariable("TYPE_IMG", ilUtil::getImagePath('icon_lm.svg'));
        $this->tpl->setVariable("ALT_IMG", $this->lng->txt("obj_sahs"));

        $this->ctrl->setParameter($this, "new_type", "sahs");
        $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

        $this->tpl->setVariable("BTN_NAME", "newModuleVersionUpload");
        $this->tpl->setVariable("TARGET", ' target="' .
        ilFrameTargetInfo::_getFrame("MainContent") . '" ');

        $this->tpl->setVariable("TXT_SELECT_LMTYPE", $this->lng->txt("type"));

        if ($type == "scorm2004") {
            $this->tpl->setVariable("TXT_TYPE", $this->lng->txt("lm_type_scorm2004"));
        } else {
            $this->tpl->setVariable("TXT_TYPE", $this->lng->txt("lm_type_scorm"));
        }

        include_once 'Services/FileSystem/classes/class.ilUploadFiles.php';
        if (ilUploadFiles::_getUploadDirectory()) {
            $files = ilUploadFiles::_getUploadFiles();
            foreach ($files as $file) {
                $file = htmlspecialchars($file, ENT_QUOTES, "utf-8");
                $this->tpl->setCurrentBlock("option_uploaded_file");
                $this->tpl->setVariable("UPLOADED_FILENAME", $file);
                $this->tpl->setVariable("TXT_UPLOADED_FILENAME", $file);
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock("select_uploaded_file");
            $this->tpl->setVariable("TXT_SELECT_FROM_UPLOAD_DIR", $this->lng->txt("cont_select_from_upload_dir"));
            $this->tpl->setVariable("TXT_UPLOADED_FILE", $this->lng->txt("cont_uploaded_file"));
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("TXT_UPLOAD", $this->lng->txt("upload"));
        $this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
        $this->tpl->setVariable("TXT_IMPORT_LM", $this->lng->txt("import_sahs"));
        $this->tpl->setVariable("TXT_SELECT_FILE", $this->lng->txt("select_file"));

        // gives out the limit as a little notice
        $this->tpl->setVariable("TXT_FILE_INFO", $this->lng->txt("file_notice") . " " . $this->getMaxFileSize());
    }


    public function getMaxFileSize()
    {
        // get the value for the maximal uploadable filesize from the php.ini (if available)
        $umf = get_cfg_var("upload_max_filesize");
        // get the value for the maximal post data from the php.ini (if available)
        $pms = get_cfg_var("post_max_size");
     
        //convert from short-string representation to "real" bytes
        $multiplier_a = array("K" => 1024, "M" => 1024 * 1024, "G" => 1024 * 1024 * 1024);

        $umf_parts = preg_split("/(\d+)([K|G|M])/", $umf, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $pms_parts = preg_split("/(\d+)([K|G|M])/", $pms, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        if (count($umf_parts) == 2) {
            $umf = $umf_parts[0] * $multiplier_a[$umf_parts[1]];
        }
        if (count($pms_parts) == 2) {
            $pms = $pms_parts[0] * $multiplier_a[$pms_parts[1]];
        }
     
        // use the smaller one as limit
        $max_filesize = min($umf, $pms);

        if (!$max_filesize) {
            $max_filesize = max($umf, $pms);
        }

        //format for display in mega-bytes
        return $max_filesize = sprintf("%.1f MB", $max_filesize / 1024 / 1024);
    }
    
    
    public function newModuleVersionUpload()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];

        $unzip = PATH_TO_UNZIP;
        $tocheck = "imsmanifest.xml";
        
        include_once 'Services/FileSystem/classes/class.ilUploadFiles.php';

        // check create permission before because the uploaded file will be copied
        if (!$rbacsystem->checkAccess("write", $_GET["ref_id"])) {
            $this->ilias->raiseError($this->lng->txt("no_create_permission"), $this->ilias->error_obj->WARNING);
        } elseif ($_FILES["scormfile"]["name"]) {
            // check if file was uploaded
            $source = $_FILES["scormfile"]["tmp_name"];
            if (($source == 'none') || (!$source)) {
                ilUtil::sendInfo($this->lng->txt("upload_error_file_not_found"), true);
                $this->newModuleVersion();
                return;
            }
        } elseif ($_POST["uploaded_file"]) {
            // check if the file is in the ftp directory and readable
            if (!ilUploadFiles::_checkUploadFile($_POST["uploaded_file"])) {
                $this->ilias->raiseError($this->lng->txt("upload_error_file_not_found"), $this->ilias->error_obj->MESSAGE);
            }
            // copy the uploaded file to the client web dir to analyze the imsmanifest
            // the copy will be moved to the lm directory or deleted
            $source = CLIENT_WEB_DIR . "/" . $_POST["uploaded_file"];
            ilUploadFiles::_copyUploadFile($_POST["uploaded_file"], $source);
            $source_is_copy = true;
        } else {
            ilUtil::sendInfo($this->lng->txt("upload_error_file_not_found"), true);
            $this->newModuleVersion();
            return;
        }
        // fim.

        //unzip the imsmanifest-file from new uploaded file
        $pathinfo = pathinfo($source);
        $dir = $pathinfo["dirname"];
        $file = $pathinfo["basename"];
        $cdir = getcwd();
        chdir($dir);

        //we need more flexible unzip here than ILIAS standard classes allow
        $unzipcmd = $unzip . " -o " . ilUtil::escapeShellArg($source) . " " . $tocheck;
        exec($unzipcmd);
        chdir($cdir);
        $tmp_file = $dir . "/" . $_GET["ref_id"] . "." . $tocheck;

        ilFileUtils::rename($dir . "/" . $tocheck, $tmp_file);
        $new_manifest = file_get_contents($tmp_file);

        //remove temp file
        unlink($tmp_file);

        //get old manifest file
        $old_manifest = file_get_contents($this->object->getDataDirectory() . "/" . $tocheck);

        //reload fixed version of file
        $check = '/xmlns="http:\/\/www.imsglobal.org\/xsd\/imscp_v1p1"/';
        $replace = "xmlns=\"http://www.imsproject.org/xsd/imscp_rootv1p1p2\"";
        $reload_manifest = preg_replace($check, $replace, $new_manifest);

        //do testing for converted versions as well as earlier ILIAS version messed up utf8 conversion
        if (strcmp($new_manifest, $old_manifest) == 0 || strcmp(utf8_encode($new_manifest), $old_manifest) == 0 ||
            strcmp($reload_manifest, $old_manifest) == 0 || strcmp(utf8_encode($reload_manifest), $old_manifest) == 0) {

            //get exisiting module version
            $module_version = $this->object->getModuleVersion() + 1;

            if ($_FILES["scormfile"]["name"]) {
                //build targetdir in lm_data
                $file_path = $this->object->getDataDirectory() . "/" . $_FILES["scormfile"]["name"] . "." . $module_version;
                $file_path = str_replace(".zip." . $module_version, "." . $module_version . ".zip", $file_path);
                //move to data directory and add subfix for versioning
                ilUtil::moveUploadedFile($_FILES["scormfile"]["tmp_name"], $_FILES["scormfile"]["name"], $file_path);
            } else {
                //build targetdir in lm_data
                $file_path = $this->object->getDataDirectory() . "/" . $_POST["uploaded_file"] . "." . $module_version;
                $file_path = str_replace(".zip." . $module_version, "." . $module_version . ".zip", $file_path);
                // move the already copied file to the lm_data directory
                ilFileUtils::rename($source, $file_path);
            }
            
            //unzip and replace old extracted files
            ilUtil::unzip($file_path, true);
            ilUtil::renameExecutables($this->object->getDataDirectory()); //(security)
            
            //increase module version
            $this->object->setModuleVersion($module_version);
            $this->object->update();
            
            //redirect to properties and display success
            ilUtil::sendInfo($this->lng->txt("cont_new_module_added"), true);
            ilUtil::redirect("ilias.php?baseClass=ilSAHSEditGUI&ref_id=" . $_GET["ref_id"]);
            exit;
        } else {
            if ($source_is_copy) {
                unlink($source);
            }
            
            ilUtil::sendInfo($this->lng->txt("cont_invalid_new_module"), true);
            $this->newModuleVersion();
        }
    }

    /**
    * save properties
    */
    public function saveProperties()
    {
        $this->object->setTitle($_POST["Fobject_title"]);
        $this->object->setDescription($_POST["Fobject_description"]);

        //check if OfflineMode-Zip has to be created
        $tmpOfflineMode = ilUtil::yn2tf($_POST["cobj_offline_mode"]);
        if ($tmpOfflineMode == true) {
            if ($this->object->getOfflineMode() == false) {
                $this->object->zipLmForOfflineMode();
            }
        }
        if (isset($_POST["mastery_score"])) {
            $this->object->setMasteryScore($_POST["mastery_score"]);
            // $this->object->updateMasteryScoreValues();
        }

        $t_height = $this->object->getHeight();
        if ($_POST["height_0"] != $this->object->getHeight()) {
            $t_height = $_POST["height_0"];
        }
        if ($_POST["height_1"] != $this->object->getHeight()) {
            $t_height = $_POST["height_1"];
        }

        $t_width = $this->object->getWidth();
        if ($_POST["width_0"] != $this->object->getWidth()) {
            $t_width = $_POST["width_0"];
        }
        if ($_POST["width_1"] != $this->object->getWidth()) {
            $t_width = $_POST["width_1"];
        }
        $this->object->setOfflineStatus(!($_POST['cobj_online']));
        $this->object->setOfflineMode($tmpOfflineMode);
        $this->object->setOpenMode($_POST["open_mode"]);
        $this->object->setWidth($t_width);
        $this->object->setHeight($t_height);
        $this->object->setAuto_last_visited(ilUtil::yn2tf($_POST["cobj_auto_last_visited"]));
        $this->object->setAutoContinue(ilUtil::yn2tf($_POST["auto_continue"]));
        $this->object->setMaxAttempt($_POST["max_attempt"]);
        $this->object->setDefaultLessonMode($_POST["lesson_mode"]);
        $this->object->setCreditMode($_POST["credit_mode"]);
        $this->object->setAutoReview(ilUtil::yn2tf($_POST["auto_review"]));
        $this->object->setSession(ilUtil::yn2tf($_POST["cobj_session"]));
        $this->object->setInteractions(ilUtil::yn2tf($_POST["cobj_interactions"]));
        $this->object->setObjectives(ilUtil::yn2tf($_POST["cobj_objectives"]));
        $this->object->setTime_from_lms(ilUtil::yn2tf($_POST["cobj_time_from_lms"]));
        $this->object->setCheck_values(ilUtil::yn2tf($_POST["cobj_check_values"]));
        $this->object->setAutoSuspend(ilUtil::yn2tf($_POST["cobj_auto_suspend"]));
        $this->object->setDebug(ilUtil::yn2tf($_POST["cobj_debug"]));
        $this->object->setIdSetting($_POST["id_setting"]);
        $this->object->setNameSetting($_POST["name_setting"]);
        $this->object->update();
        ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "properties");
    }

    /**
     * show tracking data
     */
    protected function showTrackingItemsBySco()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        ilObjSCORMLearningModuleGUI::setSubTabs();
        $ilTabs->setTabActive("cont_tracking_data");
        $ilTabs->setSubTabActive("cont_tracking_bysco");

        $reports = array('exportSelectedCore','exportSelectedInteractions','exportSelectedObjectives','exportSelectedRaw');//,'tracInteractionItem','tracInteractionUser','tracInteractionUserAnswers'
        $scoSelected = "all";
        if (isset($_GET["scoSelected"])) {
            $scoSelected = ilUtil::stripSlashes($_GET["scoSelected"]);
        }
        if (isset($_POST["scoSelected"])) {
            $scoSelected = ilUtil::stripSlashes($_POST["scoSelected"]);
        }
        $this->ctrl->setParameter($this, 'scoSelected', $scoSelected);

        $report = "choose";
        if (isset($_GET["report"])) {
            $report = ilUtil::stripSlashes($_GET["report"]);
        }
        if (isset($_POST["report"])) {
            $report = ilUtil::stripSlashes($_POST["report"]);
        }
        $this->ctrl->setParameter($this, 'report', $report);

        include_once './Modules/ScormAicc/classes/class.ilSCORMTrackingItemsPerScoFilterGUI.php';
        $filter = new ilSCORMTrackingItemsPerScoFilterGUI($this, 'showTrackingItemsBySco');
        $filter->parse($scoSelected, $report, $reports);
        if ($report == "choose") {
            $this->tpl->setContent($filter->form->getHTML());
        } else {
            $scosSelected = array();
            if ($scoSelected != "all") {
                $scosSelected[] = $scoSelected;
            } else {
                $scos = $this->object->getTrackedItems();
                foreach ($scos as $row) {
                    $scosSelected[] = (int) $row->getId();
                }
            }
            //with check for course ...
            include_once "Services/Tracking/classes/class.ilTrQuery.php";
            $a_users = ilTrQuery::getParticipantsForObject($this->ref_id);
            //			var_dump($this->object->getTrackedUsers(""));
            include_once './Modules/ScormAicc/classes/class.ilSCORMTrackingItemsTableGUI.php';
            $tbl = new ilSCORMTrackingItemsTableGUI($this->object->getId(), $this, 'showTrackingItemsBySco', $a_users, $scosSelected, $report);
            $this->tpl->setContent($filter->form->getHTML() . $tbl->getHTML());
        }
        return true;
    }


    /**
     * Show tracking table
     * @global ilTabs $ilTabs
     * $global ilToolbar $ilToolbar
     */
    public function showTrackingItems()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];
        $ilAccess = $DIC['ilAccess'];

        $ilTabs->setTabActive('cont_tracking_data');

        if ($ilAccess->checkAccess("read_learning_progress", "", $_GET["ref_id"])) {
            ilObjSCORMLearningModuleGUI::setSubTabs();
            $ilTabs->setSubTabActive('cont_tracking_byuser');

            $reports = array('exportSelectedSuccess','exportSelectedCore','exportSelectedInteractions','exportSelectedObjectives','exportSelectedRaw');

            $userSelected = "all";
            if (isset($_GET["userSelected"])) {
                $userSelected = ilUtil::stripSlashes($_GET["userSelected"]);
            }
            if (isset($_POST["userSelected"])) {
                $userSelected = ilUtil::stripSlashes($_POST["userSelected"]);
            }
            $this->ctrl->setParameter($this, 'userSelected', $userSelected);

            $report = "choose";
            if (isset($_GET["report"])) {
                $report = ilUtil::stripSlashes($_GET["report"]);
            }
            if (isset($_POST["report"])) {
                $report = ilUtil::stripSlashes($_POST["report"]);
            }
            $this->ctrl->setParameter($this, 'report', $report);

            include_once './Modules/ScormAicc/classes/class.ilSCORMTrackingItemsPerUserFilterGUI.php';
            $filter = new ilSCORMTrackingItemsPerUserFilterGUI($this, 'showTrackingItems');
            $filter->parse($userSelected, $report, $reports);
            if ($report == "choose") {
                $this->tpl->setContent($filter->form->getHTML());
            } else {
                $usersSelected = array();
                if ($userSelected != "all") {
                    $usersSelected[] = $userSelected;
                } else {
                    include_once "Services/Tracking/classes/class.ilTrQuery.php";
                    $users = ilTrQuery::getParticipantsForObject($this->ref_id);
                    foreach ($users as $user) {
                        if (ilObject::_exists($user) && ilObject::_lookUpType($user) == 'usr') {
                            $usersSelected[] = $user;
                        }
                    }
                }
                $scosSelected = array();
                $scos = $this->object->getTrackedItems();
                foreach ($scos as $row) {
                    $scosSelected[] = (int) $row->getId();
                }

                //with check for course ...
                // include_once "Services/Tracking/classes/class.ilTrQuery.php";
                // $a_users=ilTrQuery::getParticipantsForObject($this->ref_id);
                //			var_dump($this->object->getTrackedUsers(""));
                include_once './Modules/ScormAicc/classes/class.ilSCORMTrackingItemsTableGUI.php';
                $tbl = new ilSCORMTrackingItemsTableGUI($this->object->getId(), $this, 'showTrackingItems', $usersSelected, $scosSelected, $report);
                $this->tpl->setContent($filter->form->getHTML() . $tbl->getHTML());
            }
        } elseif ($ilAccess->checkAccess("edit_learning_progress", "", $_GET["ref_id"])) {
            $this->modifyTrackingItems();
        }
        return true;
    }
    protected function modifyTrackingItems()
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        if ($ilAccess->checkAccess("edit_learning_progress", "", $_GET["ref_id"])) {
            include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
            $privacy = ilPrivacySettings::_getInstance();
            if (!$privacy->enabledSahsProtocolData()) {
                $this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
            }

            global $DIC;
            $ilTabs = $DIC['ilTabs'];
            $ilToolbar = $DIC['ilToolbar'];

            include_once './Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
            $ilToolbar->addButton(
                $this->lng->txt('import'),
                $this->ctrl->getLinkTarget($this, 'importForm')
            );
            $ilToolbar->addButton(
                $this->lng->txt('cont_export_all'),
                $this->ctrl->getLinkTarget($this, 'exportAll')
            );

            ilObjSCORMLearningModuleGUI::setSubTabs();
            $ilTabs->setTabActive('cont_tracking_data');
            $ilTabs->setSubTabActive('cont_tracking_modify');

            include_once './Modules/ScormAicc/classes/class.ilSCORMTrackingUsersTableGUI.php';
            $tbl = new ilSCORMTrackingUsersTableGUI($this->object->getId(), $this, 'modifytrackingItems');
            $tbl->parse();
            $this->tpl->setContent($tbl->getHTML());
        }
    }

    
    /**
     * Apply table filter
     */
    protected function applyUserTableFilter()
    {
        include_once './Modules/ScormAicc/classes/class.ilSCORMTrackingUsersTableGUI.php';
        $tbl = new ilSCORMTrackingUsersTableGUI($this->object->getId(), $this, 'modifytrackingItems');
        $tbl->writeFilterToSession();
        $tbl->resetOffset();
        $this->modifyTrackingItems();
    }

    /**
     * Reset table filter
     */
    protected function resetUserTableFilter()
    {
        include_once './Modules/ScormAicc/classes/class.ilSCORMTrackingUsersTableGUI.php';
        $tbl = new ilSCORMTrackingUsersTableGUI($this->object->getId(), $this, 'modifytrackingItems');
        $tbl->resetFilter();
        $tbl->resetOffset();
        $this->modifyTrackingItems();
    }

    /**
     * display deletion confirmation screen
     */
    public function deleteTrackingForUser()
    {
        if (!isset($_POST["user"])) {
            $this->ilias->raiseError($this->lng->txt("no_checkbox"), $this->ilias->error_obj->MESSAGE);
        }
    
        // display confirmation message
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("info_delete_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelDeleteTracking");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmedDeleteTracking");

        foreach ($_POST["user"] as $id) {
            if (ilObject::_exists($id) && ilObject::_lookUpType($id) == "usr") {
                $user = new ilObjUser($id);

                $caption = ilUtil::getImageTagByType("sahs", $this->tpl->tplPath) .
                    " " . $this->lng->txt("cont_tracking_data") .
                    ": " . $user->getLastname() . ", " . $user->getFirstname();


                $cgui->addItem("user[]", $id, $caption);
            }
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    /**
     * cancel deletion of export files
     */
    public function cancelDeleteTracking()
    {
        ilUtil::sendInfo($this->lng->txt("msg_cancel"), true);
        $this->ctrl->redirect($this, "modifyTrackingItems");
    }

    public function confirmedDeleteTracking()
    {
        $this->object->deleteTrackingDataOfUsers($_POST["user"]);
        $this->ctrl->redirect($this, "modifyTrackingItems");
    }

    /**
     * overwrite..jump back to trackingdata not parent
     */
    public function cancel()
    {
        ilUtil::sendInfo($this->lng->txt("msg_cancel"), true);
        $this->ctrl->redirect($this, "properties");
    }

    /**
     * gui functions for GUI export
     */
    protected function import()
    {
        $form = $this->initImportForm("");
        if ($form->checkInput()) {
            $source = $form->getInput('csv');
            $error = $this->object->importTrackingData($source['tmp_name']);
            switch ($error) {
                case 0:
                    ilUtil::sendInfo('Tracking data imported', true);
                    $this->ctrl->redirect($this, "showTrackingItems");
                    break;
                case -1:
                    ilUtil::sendInfo($this->lng->txt('err_check_input'));
                    $this->importForm();
                    break;
            }
        }
        ilUtil::sendInfo($this->lng->txt('err_check_input'));
        $form->setValuesByPost();
        $this->importForm();
    }

    /**
     * Show import form
     */
    protected function importForm()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'showTrackingItems'));

        $form = $this->initImportForm("");
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init import form
     */
    protected function initImportForm($a_new_type)
    {
        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('cont_import_tracking'));
        $form->addCommandButton('import', $this->lng->txt('import'));
        $form->addCommandButton('showTrackingItems', $this->lng->txt('cancel'));

        $csv = new ilFileInputGUI($this->lng->txt('select_file'), 'csv');
        $csv->setRequired(true);
        $csv->setSuffixes(array('csv'));
        $form->addItem($csv);

        return $form;
    }

    /**
     * Show export section for all users
     */
    protected function exportAll()
    {
        $this->object->exportSelected(1);
    }

    /**
     * Export selection for selected users
     */
    protected function exportSelectionUsers()
    {
        if (!count((array) $_POST['user'])) {
            //ilUtil::sendFailure($this->lng->txt('select_one'),true);
            ilUtil::sendInfo($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, 'modifyTrackingItems');
        } else {
            $this->object->exportSelected(0, $_POST["user"]);
        }
    }


    public function decreaseAttempts()
    {
        if (!isset($_POST["user"])) {
            ilUtil::sendInfo($this->lng->txt("no_checkbox"), true);
        }
        $this->object->decreaseAttemptsForUser($_POST["user"]);
        $this->ctrl->redirect($this, "modifyTrackingItems");
    }
    
    
    /**
     * show tracking data of item
     */
    protected function showTrackingItem()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        include_once "./Services/Table/classes/class.ilTableGUI.php";

        $this->setSubTabs();
        $ilTabs->setTabActive("cont_tracking_data");
        $ilTabs->setSubTabActive("cont_tracking_byuser");

        include_once './Modules/ScormAicc/classes/class.ilSCORMTrackingItemsPerUserTableGUI.php';
        $tbl = new ilSCORMTrackingItemsPerUserTableGUI($this->object->getId(), $this, 'showTrackingItem');
        $tbl->setUserId((int) $_REQUEST['user_id']);
        $tbl->parse();
        $this->tpl->setContent($tbl->getHTML());
        return true;
    }

    /**
     * show tracking data of item
     */
    protected function showTrackingItemSco()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        include_once "./Services/Table/classes/class.ilTableGUI.php";

        $this->setSubTabs();
        $ilTabs->setTabActive("cont_tracking_data");
        $ilTabs->setSubTabActive("cont_tracking_bysco");

        include_once './Modules/ScormAicc/classes/class.ilSCORMTrackingItemsScoTableGUI.php';
        $tbl = new ilSCORMTrackingItemsScoTableGUI($this->object->getId(), $this, 'showTrackingItemSco');
        $tbl->setScoId((int) $_GET['obj_id']);
        $tbl->parse();
        $this->tpl->setContent($tbl->getHTML());
        return true;
    }

    /**
     * show tracking data of item per user
     */
    protected function showTrackingItemPerUser()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        include_once "./Services/Table/classes/class.ilTableGUI.php";

        $this->setSubTabs();
        $ilTabs->setTabActive("cont_tracking_data");
        $ilTabs->setSubTabActive("cont_tracking_byuser");

        $this->ctrl->setParameter($this, 'obj_id', (int) $_REQUEST['obj_id']);
        $this->ctrl->setParameter($this, 'user_id', (int) $_REQUEST['user_id']);

        include_once './Modules/ScormAicc/classes/class.ilSCORMTrackingItemPerUserTableGUI.php';
        $tbl = new ilSCORMTrackingItemPerUserTableGUI($this->object->getId(), $this, 'showTrackingItemPerUser');
        $tbl->setUserId((int) $_REQUEST['user_id']);
        $tbl->setScoId((int) $_REQUEST['obj_id']);
        $tbl->parse();
        $this->tpl->setContent($tbl->getHTML());
        return true;
    }

    //setTabs
    public function setSubTabs()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilTabs = $DIC['ilTabs'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilAccess = $DIC['ilAccess'];

        if ($ilAccess->checkAccess("read_learning_progress", "", $_GET["ref_id"])) {
            $ilTabs->addSubTabTarget(
                "cont_tracking_byuser",
                $this->ctrl->getLinkTarget($this, "showTrackingItems"),
                array("edit", ""),
                get_class($this)
            );

            $ilTabs->addSubTabTarget(
                "cont_tracking_bysco",
                $this->ctrl->getLinkTarget($this, "showTrackingItemsBySco"),
                array("edit", ""),
                get_class($this)
            );
        }
        if ($ilAccess->checkAccess("edit_learning_progress", "", $_GET["ref_id"])) {
            $ilTabs->addSubTabTarget(
                "cont_tracking_modify",
                $this->ctrl->getLinkTarget($this, "modifyTrackingItems"),
                array("edit", ""),
                get_class($this)
            );
        }
    }

    
    /**
     * Manage offline mode for users
     * @global ilTabs $ilTabs
     * $global ilToolbar $ilToolbar
     */
    protected function offlineModeManager()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $tree = $DIC['tree'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ilToolbar = $DIC['ilToolbar'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilSetting = $DIC['ilSetting'];

        include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
        if (!ilLearningProgressAccess::checkAccess($this->object->getRefId()) && !$rbacsystem->checkAccess("edit_permission", "", $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
        }

        include_once './Modules/ScormAicc/classes/class.ilSCORMOfflineModeUsersTableGUI.php';
        $tbl = new ilSCORMOfflineModeUsersTableGUI($this->object->getId(), $this, 'offlineModeManager');
        $tbl->parse();
        $this->tpl->setContent($tbl->getHTML());
    }
    /**
     * Stop offline mode for selected users
     */
    protected function stopUserOfflineMode()
    {
        if (!count((array) $_POST['user'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'offlineModeManager');
        }
        // display confirmation message
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("info_stop_offline_mode_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelStopUserOfflineMode");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmedStopUserOfflineMode");
        foreach ($_POST["user"] as $id) {
            if (ilObject::_exists($id) && ilObject::_lookUpType($id) == "usr") {
                $user = new ilObjUser($id);
                $caption = ilUtil::getImageTagByType("sahs_offline", $this->tpl->tplPath) .
                    " " . $this->lng->txt("stop_user_offline_mode_for_user") .
                    ": " . $user->getLastname() . ", " . $user->getFirstname();
                $cgui->addItem("user[]", $id, $caption);
            }
        }
        $this->tpl->setContent($cgui->getHTML());
    }

    public function cancelStopUserOfflineMode()
    {
        ilUtil::sendInfo($this->lng->txt("msg_cancel"), true);
        $this->ctrl->redirect($this, "offlineModeManager");
    }

    public function confirmedStopUserOfflineMode()
    {
        include_once './Modules/ScormAicc/classes/class.ilSCORMOfflineMode.php';
        foreach ($_POST["user"] as $id) {
            ilSCORMOfflineMode::stopOfflineModeForUser($this->object->getId(), $id);
        }

        $this->offlineModeManager();
    }
}
// END class.ilObjSCORMLearningModule
