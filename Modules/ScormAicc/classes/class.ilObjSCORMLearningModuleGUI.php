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
* Class ilObjSCORMLearningModuleGUI
*
* @author Alex Killing <alex.killing@gmx.de>, Hendrik Holtmann <holtmann@mac.com>
* $Id$
*
* @ilCtrl_Calls ilObjSCORMLearningModuleGUI: ilFileSystemGUI, ilObjectMetaDataGUI, ilPermissionGUI, ilLearningProgressGUI
* @ilCtrl_Calls ilObjSCORMLearningModuleGUI: ilInfoScreenGUI
* @ilCtrl_Calls ilObjSCORMLearningModuleGUI: ilCertificateGUI
* @ilCtrl_Calls ilObjSCORMLearningModuleGUI: ilSCORMTrackingItemsPerScoFilterGUI, ilSCORMTrackingItemsPerUserFilterGUI, ilSCORMTrackingItemsTableGUI
* @ilCtrl_Calls ilObjSCORMLearningModuleGUI: ilLTIProviderObjectSettingGUI
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORMLearningModuleGUI extends ilObjSAHSLearningModuleGUI
{
    private ilPropertyFormGUI $form;
    protected \ILIAS\DI\Container $dic;

    protected int $refId;
    protected ilCtrl $ctrl;

    /**
    * Constructor
    */
    public function __construct($data, int $id, bool $call_by_reference, bool $prepare_output = true) //missing typehint because mixed
    {
        global $DIC;
        $this->dic = $DIC;
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];

        $this->lng->loadLanguageModule("content");
        $this->lng->loadLanguageModule("search");

        $this->refId = $DIC->http()->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int());

        $this->type = "sahs";
        parent::__construct($data, $id, $call_by_reference, false);
    }

    /**
     * assign scorm object to scorm gui object
     */
    protected function assignObject(): void
    {
        if ($this->id != 0) {
            if ($this->call_by_reference) {
                $this->object = new ilObjSCORMLearningModule((int) $this->id, true);
            } else {
                $this->object = new ilObjSCORMLearningModule((int) $this->id, false);
            }
        }
    }

    /**
    * scorm module properties
    */
    public function properties(): void
    {
        global $DIC;
        $ilToolbar = $DIC->toolbar();
        $ilTabs = $DIC->tabs();

//        $lng->loadLanguageModule("style");

        $this->setSettingsSubTabs();
        $ilTabs->setSubTabActive('cont_settings');

        // view
        $ilToolbar->addButtonInstance($this->object->getViewButton());

        // lm properties
        $this->initPropertiesForm();
        $this->getPropertiesFormValues();
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * Init properties form
     */
    public function initPropertiesForm(): void
    {
        $obj_service = $this->object_service;
        $this->form = new ilPropertyFormGUI();
        $this->form->setTitle($this->lng->txt("cont_lm_properties"));

        //check/select only once
        $this->object->checkMasteryScoreValues();

        //title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "Fobject_title");
        $ti->setMaxLength(200);
        $this->form->addItem($ti);

        //description
        $ti = new ilTextAreaInputGUI($this->lng->txt("description"), "Fobject_description");
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
        $cb->setInfo($this->lng->txt("cont_online_info"));
        $this->form->addItem($cb);


        //
        // presentation
        //
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("cont_presentation"));
        $this->form->addItem($sh);

        $radg = new ilRadioGroupInputGUI($this->lng->txt("cont_open"), "open_mode");
        $op0 = new ilRadioOption($this->lng->txt("cont_open_normal"), "0");
        $radg->addOption($op0);
        $op1 = new ilRadioOption($this->lng->txt("cont_open_iframe"), "1");
        $radg->addOption($op1);
        $op2 = new ilRadioOption($this->lng->txt("cont_open_window"), "5");
        $radg->addOption($op2);

        // width
        $ni = new ilNumberInputGUI($this->lng->txt("cont_width"), "width_0");
        $ni->setMaxLength(4);
        $ni->setSize(4);
        $op1->addSubItem($ni);
        $ni = new ilNumberInputGUI($this->lng->txt("cont_width"), "width_1");
        $ni->setMaxLength(4);
        $ni->setSize(4);
        $op2->addSubItem($ni);
        // height
        $ni = new ilNumberInputGUI($this->lng->txt("cont_height"), "height_0");
        $ni->setMaxLength(4);
        $ni->setSize(4);
        $ni->setInfo($this->lng->txt("cont_width_height_info"));
        $op1->addSubItem($ni);
        $ni = new ilNumberInputGUI($this->lng->txt("cont_height"), "height_1");
        $ni->setMaxLength(4);
        $ni->setSize(4);
        $ni->setInfo($this->lng->txt("cont_width_height_info"));
        $op2->addSubItem($ni);

        $this->form->addItem($radg);

        // auto navigation to last visited item
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_auto_last_visited"), "cobj_auto_last_visited");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_auto_last_visited_info"));
        $this->form->addItem($cb);

        // auto continue
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_sc_auto_continue"), "auto_continue");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_sc_auto_continue_info"));
        $this->form->addItem($cb);

        // tile image
        $obj_service->commonSettings()->legacyForm($this->form, $this->object)->addTileImage();

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
        $this->form->addItem($si);

        // credit mode
        $options = array("credit" => $this->lng->txt("cont_credit_on"),
            "no_credit" => $this->lng->txt("cont_credit_off"));
        $si = new ilSelectInputGUI($this->lng->txt("cont_credit_mode"), "credit_mode");
        $si->setOptions($options);
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
        // $si->setInfo($this->lng->txt("cont_sc_auto_review_info_12"));
        $this->form->addItem($si);

        // mastery_score
        if ($this->object->getMasteryScoreValues() != "") {
            $ni = new ilNumberInputGUI($this->lng->txt("cont_mastery_score_12"), "mastery_score");
            $ni->setMaxLength(3);
            $ni->setSize(3);
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
        $cb->setInfo($this->lng->txt("cont_sc_usession_info"));
        $this->form->addItem($cb);

        // storage of interactions
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_interactions"), "cobj_interactions");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_interactions_info_12"));
        $this->form->addItem($cb);

        // objectives
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_objectives"), "cobj_objectives");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_objectives_info"));
        $this->form->addItem($cb);

        // time from lms
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_time_from_lms"), "cobj_time_from_lms");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_time_from_lms_info"));
        $this->form->addItem($cb);

        // check values
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_check_values"), "cobj_check_values");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_check_values_info"));
        $this->form->addItem($cb);

        // auto cmi.core.exit to suspend
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_auto_suspend"), "cobj_auto_suspend");
        $cb->setValue("y");
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
        if ($this->object->getDebugActivated() == false) {
            $cb->setDisabled(true);
            $cb->setInfo($this->lng->txt("cont_debug_deactivated"));
        } else {
            $cb->setInfo($this->lng->txt("cont_debug_deactivate12"));
        }
        $this->form->addItem($cb);
        $this->form->addCommandButton("saveProperties", $this->lng->txt("save"));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }


    /**
     * Get values for properties form
     */
    public function getPropertiesFormValues(): void
    {
        $values = array();
        $values["Fobject_title"] = $this->object->getTitle();
        $values["Fobject_description"] = $this->object->getDescription();
        if (!$this->object->getOfflineStatus()) {
            $values["cobj_online"] = true;
        }
//        $values["cobj_offline_mode"] = $this->object->getOfflineMode();
        $values["open_mode"] = $this->object->getOpenMode();
        $values["width_0"] = $this->object->getWidth();
        $values["width_1"] = $this->object->getWidth();
        $values["height_0"] = $this->object->getHeight();
        $values["height_1"] = $this->object->getHeight();
        $values["cobj_auto_last_visited"] = $this->object->getAuto_last_visited();
        $values["auto_continue"] = $this->object->getAutoContinue();
        $values["lesson_mode"] = $this->object->getDefaultLessonMode();
        $values["credit_mode"] = $this->object->getCreditMode();
        $values["auto_review"] = $this->object->getAutoReviewChar();
        $values["mastery_score"] = $this->object->getMasteryScore();
        $values["cobj_session"] = $this->object->getSession();
        $values["cobj_interactions"] = $this->object->getInteractions();
        $values["cobj_objectives"] = $this->object->getObjectives();
        $values["cobj_time_from_lms"] = $this->object->getTime_from_lms();
        $values["cobj_check_values"] = $this->object->getCheck_values();
        $values["cobj_auto_suspend"] = $this->object->getAutoSuspend();
        $values["id_setting"] = $this->object->getIdSetting();
        $values["name_setting"] = $this->object->getNameSetting();
        $values["cobj_debug"] = $this->object->getDebug();
        $this->form->setValuesByArray($values);
    }

    /**
    * upload new version of module
    */
    public function newModuleVersion(): void
    {
        global $DIC;
        $ilTabs = $DIC->tabs();
        $this->setSettingsSubTabs();
        $ilTabs->setSubTabActive('cont_sc_new_version');

        $obj_id = ilObject::_lookupObjectId($this->refId);
        $type = ilObjSAHSLearningModule::_lookupSubType($obj_id);
        $this->form = new ilPropertyFormGUI();
        //title
        $this->form->setTitle($this->lng->txt("import_sahs"));

        // SCORM-type
        $ne = new ilNonEditableValueGUI($this->lng->txt("type"), "");
        $ne->setValue($this->lng->txt("lm_type_" . ilObjSAHSLearningModule::_lookupSubType($this->object->getID())));
        $this->form->addItem($ne);

        $options = array();
        if (ilUploadFiles::_getUploadDirectory()) {
            $options[""] = $this->lng->txt("cont_select_from_upload_dir");
            $files = ilUploadFiles::_getUploadFiles();
            foreach ($files as $file) {
                $file = htmlspecialchars($file, ENT_QUOTES, "utf-8");
                $options[$file] = $file;
            }
        }
        if (count($options) > 1) {
            // choose upload directory
            $radg = new ilRadioGroupInputGUI($this->lng->txt("cont_choose_file_source"), "file_source");
            $op0 = new ilRadioOption($this->lng->txt("cont_choose_local"), "local");
            $radg->addOption($op0);
            $op1 = new ilRadioOption($this->lng->txt("cont_choose_upload_dir"), "upload_dir");
            $radg->addOption($op1);
            $radg->setValue("local");

            $fi = new ilFileInputGUI($this->lng->txt("select_file"), "scormfile");
            $fi->setRequired(true);
            $op0->addSubItem($fi);

            $si = new ilSelectInputGUI($this->lng->txt("cont_uploaded_file"), "uploaded_file");
            $si->setOptions($options);
            $op1->addSubItem($si);

            $this->form->addItem($radg);
        } else {
            $fi = new ilFileInputGUI($this->lng->txt("select_file"), "scormfile");
            $fi->setRequired(true);
            $this->form->addItem($fi);
        }
        $this->form->addCommandButton("newModuleVersionUpload", $this->lng->txt("upload"));
        $this->form->addCommandButton("cancel", $this->lng->txt("cancel"));
        $this->form->setFormAction($DIC->ctrl()->getFormAction($this, "newModuleVersionUpload"));
        $DIC['tpl']->setContent($this->form->getHTML());
    }

    public function getMaxFileSize(): string
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

    /**
     * @throws ilException
     * @throws ilFileUtilsException
     */
    public function newModuleVersionUpload(): void
    {
        global $DIC;
        $rbacsystem = $DIC->access();
        $ilErr = $DIC["ilErr"];

        $unzip = PATH_TO_UNZIP;
        $tocheck = "imsmanifest.xml";

        // check create permission before because the uploaded file will be copied
        if (!$rbacsystem->checkAccess("write", "", $this->refId)) {
            $ilErr->raiseError($this->lng->txt("no_create_permission"), $ilErr->WARNING);
        } elseif ($_FILES["scormfile"]["name"]) {
            // check if file was uploaded
            $source = $_FILES["scormfile"]["tmp_name"];
            if (($source === 'none') || (!$source)) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt("upload_error_file_not_found"), true);
                $this->newModuleVersion();
                return;
            }
        } elseif ($DIC->http()->wrapper()->post()->has('uploaded_file')) {
            $uploadedFile = $DIC->http()->wrapper()->post()->retrieve('uploaded_file', $DIC->refinery()->kindlyTo()->string());
            // check if the file is in the ftp directory and readable
            if (!ilUploadFiles::_checkUploadFile($uploadedFile)) {
                $ilErr->raiseError($this->lng->txt("upload_error_file_not_found"), $ilErr->MESSAGE);
            }
            // copy the uploaded file to the client web dir to analyze the imsmanifest
            // the copy will be moved to the lm directory or deleted
            $source = CLIENT_WEB_DIR . "/" . $uploadedFile;
            ilUploadFiles::_copyUploadFile($uploadedFile, $source);
            $source_is_copy = true;
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("upload_error_file_not_found"), true);
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
        $unzipcmd = $unzip . " -o " . ilShellUtil::escapeShellArg($source) . " " . $tocheck;
        exec($unzipcmd);
        chdir($cdir);
        $tmp_file = $dir . "/" . $this->refId . "." . $tocheck;

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
                ilFileUtils::moveUploadedFile(
                    $_FILES["scormfile"]["tmp_name"],
                    $_FILES["scormfile"]["name"],
                    $file_path
                );
            } else {
                //build targetdir in lm_data
                $uploadedFile = $DIC->http()->wrapper()->post()->retrieve('uploaded_file', $DIC->refinery()->kindlyTo()->string());
                $file_path = $this->object->getDataDirectory() . "/" . $uploadedFile . "." . $module_version;
                $file_path = str_replace(".zip." . $module_version, "." . $module_version . ".zip", $file_path);
                // move the already copied file to the lm_data directory
                ilFileUtils::rename($source, $file_path);
            }

            //unzip and replace old extracted files
            ilFileUtils::unzip($file_path, true);
            ilFileUtils::renameExecutables($this->object->getDataDirectory()); //(security)

            //increase module version
            $this->object->setModuleVersion($module_version);
            $this->object->update();

            //redirect to properties and display success
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cont_new_module_added"), true);
            ilUtil::redirect("ilias.php?baseClass=ilSAHSEditGUI&ref_id=" . $this->refId);
            exit;
        }

        if ($source_is_copy) {
            unlink($source);
        }

        $this->tpl->setOnScreenMessage('info', $this->lng->txt("cont_invalid_new_module"), true);
        $this->newModuleVersion();
    }

    /**
     * @throws ilCtrlException
     */
    public function saveProperties(): void
    {
        $obj_service = $this->object_service;
        $this->initPropertiesForm();
        if ($this->form->checkInput()) {
            $this->object->setTitle($this->dic->http()->wrapper()->post()->retrieve('Fobject_title', $this->dic->refinery()->kindlyTo()->string()));
            $this->object->setDescription($this->dic->http()->wrapper()->post()->retrieve('Fobject_description', $this->dic->refinery()->kindlyTo()->string()));

            //check if OfflineMode-Zip has to be created
//            $tmpOfflineMode = ilUtil::yn2tf($_POST["cobj_offline_mode"]);
//            if ($tmpOfflineMode == true) {
//                if ($this->object->getOfflineMode() == false) {
//                    $this->object->zipLmForOfflineMode();
//                }
//            }
            if ($this->dic->http()->wrapper()->post()->has('mastery_score')) {
                $this->object->setMasteryScore($this->dic->http()->wrapper()->post()->retrieve('mastery_score', $this->dic->refinery()->kindlyTo()->int()));
                // $this->object->updateMasteryScoreValues();
            }

            $t_height = $this->object->getHeight();
            if ($this->dic->http()->wrapper()->post()->retrieve('height_0', $this->dic->refinery()->kindlyTo()->int()) != $this->object->getHeight()) {
                $t_height = $this->dic->http()->wrapper()->post()->retrieve('height_0', $this->dic->refinery()->kindlyTo()->int());
            }
            if ($this->dic->http()->wrapper()->post()->retrieve('height_1', $this->dic->refinery()->kindlyTo()->int()) != $this->object->getHeight()) {
                $t_height = $this->dic->http()->wrapper()->post()->retrieve('height_1', $this->dic->refinery()->kindlyTo()->int());
            }

            $t_width = $this->object->getWidth();
            if ($this->dic->http()->wrapper()->post()->retrieve('width_0', $this->dic->refinery()->kindlyTo()->int()) != $this->object->getWidth()) {
                $t_width = $this->dic->http()->wrapper()->post()->retrieve('width_0', $this->dic->refinery()->kindlyTo()->int());
            }
            if ($this->dic->http()->wrapper()->post()->retrieve('width_1', $this->dic->refinery()->kindlyTo()->int()) != $this->object->getWidth()) {
                $t_width = $this->dic->http()->wrapper()->post()->retrieve('width_1', $this->dic->refinery()->kindlyTo()->int());
            }

            $this->object->setOfflineStatus(!($this->dic->http()->wrapper()->post()->has('cobj_online')));
//            $this->object->setOfflineMode($tmpOfflineMode);
            $this->object->setOpenMode($this->dic->http()->wrapper()->post()->retrieve('open_mode', $this->dic->refinery()->kindlyTo()->int()));
            $this->object->setWidth($t_width);
            $this->object->setHeight($t_height);
            $this->object->setAuto_last_visited($this->dic->http()->wrapper()->post()->has('cobj_auto_last_visited'));
            $this->object->setAutoContinue($this->dic->http()->wrapper()->post()->has('auto_continue'));
//            $this->object->setMaxAttempt((int) $_POST["max_attempt"]);
            $this->object->setDefaultLessonMode($this->dic->http()->wrapper()->post()->retrieve('lesson_mode', $this->dic->refinery()->kindlyTo()->string()));
            $this->object->setCreditMode($this->dic->http()->wrapper()->post()->retrieve('credit_mode', $this->dic->refinery()->kindlyTo()->string()));
            $this->object->setAutoReview(ilUtil::yn2tf($this->dic->http()->wrapper()->post()->retrieve('auto_review', $this->dic->refinery()->kindlyTo()->string())));
            $this->object->setSession($this->dic->http()->wrapper()->post()->has('cobj_session'));
            $this->object->setInteractions($this->dic->http()->wrapper()->post()->has('cobj_interactions'));
            $this->object->setObjectives($this->dic->http()->wrapper()->post()->has('cobj_objectives'));
            $this->object->setTime_from_lms($this->dic->http()->wrapper()->post()->has('cobj_time_from_lms'));
            $this->object->setCheck_values($this->dic->http()->wrapper()->post()->has('cobj_check_values'));
            $this->object->setAutoSuspend($this->dic->http()->wrapper()->post()->has('cobj_auto_suspend'));
            $this->object->setDebug($this->dic->http()->wrapper()->post()->has('cobj_debug'));
            $this->object->setIdSetting($this->dic->http()->wrapper()->post()->retrieve('id_setting', $this->dic->refinery()->kindlyTo()->int()));
            $this->object->setNameSetting($this->dic->http()->wrapper()->post()->retrieve('name_setting', $this->dic->refinery()->kindlyTo()->int()));
            $this->object->update();

            // tile image
            $obj_service->commonSettings()->legacyForm($this->form, $this->object)->saveTileImage();
        }
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "properties");
    }

    /**
     * @throws ilCtrlException
     */
    protected function showTrackingItemsBySco(): bool
    {
        global $DIC;
        $ilTabs = $DIC->tabs();

        $this->setSubTabs();
        $ilTabs->setTabActive("cont_tracking_data");
        $ilTabs->setSubTabActive("cont_tracking_bysco");

        $reports = array('exportSelectedCore','exportSelectedInteractions','exportSelectedObjectives','exportSelectedRaw');//,'tracInteractionItem','tracInteractionUser','tracInteractionUserAnswers'
        $scoSelected = "all";
        if ($this->dic->http()->wrapper()->post()->has('scoSelected')) {
            $scoSelected = ilUtil::stripSlashes($this->dic->http()->wrapper()->post()->retrieve('scoSelected', $this->dic->refinery()->kindlyTo()->string()));
        }
        $this->ctrl->setParameter($this, 'scoSelected', $scoSelected);

        $report = "choose";
        if ($this->dic->http()->wrapper()->post()->has('report')) {
            $report = ilUtil::stripSlashes($this->dic->http()->wrapper()->post()->retrieve('report', $this->dic->refinery()->kindlyTo()->string()));
        }
        $this->ctrl->setParameter($this, 'report', $report);
        $filter = new ilSCORMTrackingItemsPerScoFilterGUI($this, 'showTrackingItemsBySco');
        $filter->parse($scoSelected, $report, $reports);
        if ($report === "choose") {
            $this->tpl->setContent($filter->form->getHTML());
        } else {
            $scosSelected = array();
            if ($scoSelected !== "all") {
                $scosSelected[] = $scoSelected;
            } else {
                $scos = $this->object->getTrackedItems();
                foreach ($scos as $row) {
                    $scosSelected[] = (int) $row->getId();
                }
            }
            $a_users = ilTrQuery::getParticipantsForObject($this->ref_id);
            $tbl = new ilSCORMTrackingItemsTableGUI($this->object->getId(), $this, 'showTrackingItemsBySco', $a_users, $scosSelected, $report);
            $this->tpl->setContent($filter->form->getHTML() . $tbl->getHTML());
        }
        return true;
    }

    /**
     * Show tracking table
     * @throws ilCtrlException
     */
    public function showTrackingItems(): bool
    {
        global $DIC;
        $ilTabs = $DIC->tabs();
        $ilAccess = $DIC->access();

        $ilTabs->setTabActive('cont_tracking_data');

        if ($ilAccess->checkAccess("read_learning_progress", "", $this->refId)) {
            $this->setSubTabs();
            $ilTabs->setSubTabActive('cont_tracking_byuser');

            $reports = array('exportSelectedSuccess','exportSelectedCore','exportSelectedInteractions','exportSelectedObjectives','exportSelectedRaw');

            $userSelected = "all";
            if ($this->dic->http()->wrapper()->post()->has('userSelected')) {
                $userSelected = ilUtil::stripSlashes($this->dic->http()->wrapper()->post()->retrieve('userSelected', $this->dic->refinery()->kindlyTo()->string()));
            }
            $this->ctrl->setParameter($this, 'userSelected', $userSelected);

            $report = "choose";
            if ($this->dic->http()->wrapper()->post()->has('report')) {
                $report = ilUtil::stripSlashes($this->dic->http()->wrapper()->post()->retrieve('report', $this->dic->refinery()->kindlyTo()->string()));
            }
            $this->ctrl->setParameter($this, 'report', $report);
            $filter = new ilSCORMTrackingItemsPerUserFilterGUI($this, 'showTrackingItems');
            $filter->parse($userSelected, $report, $reports);
            if ($report === "choose") {
                $this->tpl->setContent($filter->form->getHTML());
            } else {
                $usersSelected = array();
                if ($userSelected !== "all") {
                    $usersSelected[] = $userSelected;
                } else {
                    $users = ilTrQuery::getParticipantsForObject($this->ref_id);
                    foreach ($users as $user) {
                        if (ilObject::_exists((int) $user) && ilObject::_lookUpType((int) $user) === 'usr') {
                            $usersSelected[] = (int) $user;
                        }
                    }
                }
                $scosSelected = array();
                $scos = $this->object->getTrackedItems();
                foreach ($scos as $row) {
                    $scosSelected[] = (int) $row->getId();
                }
                $tbl = new ilSCORMTrackingItemsTableGUI($this->object->getId(), $this, 'showTrackingItems', $usersSelected, $scosSelected, $report);
                $this->tpl->setContent($filter->form->getHTML() . $tbl->getHTML());
            }
        } elseif ($ilAccess->checkAccess("edit_learning_progress", "", $this->refId)) {
            $this->modifyTrackingItems();
        }
        return true;
    }

    /**
     * @throws ilCtrlException
     */
    protected function modifyTrackingItems(): void
    {
        global $DIC;
        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];
        if ($ilAccess->checkAccess("edit_learning_progress", "", $this->refId)) {
            $privacy = ilPrivacySettings::getInstance();
            if (!$privacy->enabledSahsProtocolData()) {
                $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
            }

            global $DIC;
            $ilTabs = $DIC->tabs();
            $ilToolbar = $DIC->toolbar();
            $ilToolbar->addButton(
                $this->lng->txt('import'),
                $this->ctrl->getLinkTarget($this, 'importForm')
            );
            $ilToolbar->addButton(
                $this->lng->txt('cont_export_all'),
                $this->ctrl->getLinkTarget($this, 'exportAll')
            );

            $this->setSubTabs();
            $ilTabs->setTabActive('cont_tracking_data');
            $ilTabs->setSubTabActive('cont_tracking_modify');
            $tbl = new ilSCORMTrackingUsersTableGUI($this->object->getId(), $this, 'modifytrackingItems');
            $tbl->parse();
            $this->tpl->setContent($tbl->getHTML());
        }
    }

    /**
     * @throws ilCtrlException
     */
    protected function applyUserTableFilter(): void
    {
        $tbl = new ilSCORMTrackingUsersTableGUI($this->object->getId(), $this, 'modifytrackingItems');
        $tbl->writeFilterToSession();
        $tbl->resetOffset();
        $this->modifyTrackingItems();
    }

    /**
     * Reset table filter
     * @throws ilCtrlException
     */
    protected function resetUserTableFilter(): void
    {
        $tbl = new ilSCORMTrackingUsersTableGUI($this->object->getId(), $this, 'modifytrackingItems');
        $tbl->resetFilter();
        $tbl->resetOffset();
        $this->modifyTrackingItems();
    }

    /**
     * display deletion confirmation screen
     * @throws ilCtrlException
     */
    public function deleteTrackingForUser(): void
    {
        global $DIC;
        $ilErr = $DIC["ilErr"];

        if (!$DIC->http()->wrapper()->post()->has('user')) {
            $ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
        }

        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("info_delete_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelDeleteTracking");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmedDeleteTracking");
        foreach ($DIC->http()->wrapper()->post()->retrieve('user', $DIC->refinery()->kindlyTo()->listOf($DIC->refinery()->kindlyTo()->int())) as $id) {
            if (ilObject::_exists((int) $id) && ilObject::_lookUpType((int) $id) === "usr") {
                $user = new ilObjUser((int) $id);

                $caption = ilUtil::getImageTagByType("sahs", (string) $this->tpl->tplPath) .
                    " " . $this->lng->txt("cont_tracking_data") .
                    ": " . $user->getLastname() . ", " . $user->getFirstname();


                $cgui->addItem("user[]", $id, $caption);
            }
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    /**
     * cancel deletion of export files
     * @throws ilCtrlException
     */
    public function cancelDeleteTracking(): void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_cancel"), true);
        $this->ctrl->redirect($this, "modifyTrackingItems");
    }

    /**
     * @throws ilCtrlException
     */
    public function confirmedDeleteTracking(): void
    {
        $this->object->deleteTrackingDataOfUsers($this->post_wrapper->retrieve('user', $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())));
        $this->ctrl->redirect($this, "modifyTrackingItems");
    }

    /**
     * overwrite..jump back to trackingdata not parent
     * @throws ilCtrlException
     */
    public function cancel(): void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_cancel"), true);
        $this->ctrl->redirect($this, "properties");
    }

    /**
     * gui functions for GUI export
     * @throws ilCtrlException
     */
    protected function import(): void
    {
        $form = $this->initImportForm("");
        if ($form->checkInput()) {
            $source = $form->getInput('csv');
            $success = $this->object->importTrackingData($source['tmp_name']);
            switch ($success) {
                case true:
                    $this->tpl->setOnScreenMessage('info', 'Tracking data imported', true);
                    $this->ctrl->redirect($this, "showTrackingItems");
                    break;
                case false:
                    $this->tpl->setOnScreenMessage('info', $this->lng->txt('err_check_input'));
                    $this->importForm();
                    break;
            }
        }
        $this->tpl->setOnScreenMessage('info', $this->lng->txt('err_check_input'));
        $form->setValuesByPost();
        $this->importForm();
    }

    /**
     * Show import form
     * @throws ilCtrlException
     */
    protected function importForm(): void
    {
        global $DIC;
        $ilTabs = $DIC->tabs();

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'showTrackingItems'));

        $form = $this->initImportForm("");
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init import form
     * @throws ilCtrlException
     */
    protected function initImportForm(string $new_type): ilPropertyFormGUI
    {
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
    protected function exportAll(): void
    {
        $this->object->exportSelected(true);
    }

    /**
     * Export selection for selected users
     * @throws ilCtrlException
     */
    protected function exportSelectionUsers(): void
    {
        if (!$this->post_wrapper->has('user')) {
            //was if (!count((array) $_POST['user'])) {
            //ilUtil::sendFailure($this->lng->txt('select_one'),true);
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, 'modifyTrackingItems');
        } else {
            $this->object->exportSelected(false, $this->post_wrapper->retrieve('user', $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())));
        }
    }


    /**
     * @throws ilCtrlException
     */
    public function setSubTabs(): void
    {
        global $DIC;
        $lng = $DIC->language();
        $ilTabs = $DIC->tabs();
        $ilCtrl = $DIC->ctrl();
        $ilAccess = $DIC->access();

        if ($ilAccess->checkAccess("read_learning_progress", "", $this->refId)) {
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
        if ($ilAccess->checkAccess("edit_learning_progress", "", $this->refId)) {
            $ilTabs->addSubTabTarget(
                "cont_tracking_modify",
                $this->ctrl->getLinkTarget($this, "modifyTrackingItems"),
                array("edit", ""),
                get_class($this)
            );
        }
    }
}
// END class.ilObjSCORMLearningModule
