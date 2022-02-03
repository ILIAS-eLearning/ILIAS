<?php declare(strict_types=1);
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
* SCORM Learning Modules
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ilCtrl_Calls ilObjSAHSLearningModuleGUI: ilFileSystemGUI, ilObjectMetaDataGUI, ilPermissionGUI, ilInfoScreenGUI, ilLearningProgressGUI
* @ilCtrl_Calls ilObjSAHSLearningModuleGUI: ilCommonActionDispatcherGUI, ilExportGUI, ilObjectCopyGUI
*
* @ingroup ModulesScormAicc
*/
class ilObjSAHSLearningModuleGUI extends ilObjectGUI
{
    /**
    * Constructor
    *
    * @access	public
    */
    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
    {
        global $DIC;
        $lng = $DIC->language();
        $lng->loadLanguageModule("content");
        $this->type = "sahs";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
    }

    /**
     * execute command
     * @return void
     * @throws ilCtrlException
     * @throws ilException
     */
    public function executeCommand() : void
    {
        global $DIC;
        $ilAccess = $DIC->access();
        $ilTabs = $DIC->tabs();
        $ilErr = $DIC['ilErr'];

        $GLOBALS['DIC']["ilLog"]->write("bc:" . $_GET["baseClass"] . "; nc:" . $this->ctrl->getNextClass($this) . "; cmd:" . $this->ctrl->getCmd());
        if (strtolower($_GET["baseClass"]) == "iladministrationgui" ||
            strtolower($_GET["baseClass"]) == "ilsahspresentationgui" ||
            $this->getCreationMode() == true) {
            $this->prepareOutput();
        } else {
            $this->getTemplate();
            $this->setLocator();
            $this->setTabs();
            $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lm.svg"));
            $this->tpl->setTitle($this->object->getTitle());
        }

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case 'illtiproviderobjectsettinggui':
                $this->setSettingsSubTabs();
                $ilTabs->setSubTabActive('lti_provider');
                $lti_gui = new ilLTIProviderObjectSettingGUI($this->object->getRefId());
                $lti_gui->setCustomRolesForSelection($GLOBALS['DIC']->rbac()->review()->getLocalRoles($this->object->getRefId()));
                $lti_gui->offerLTIRolesForSelection(false);
                $this->ctrl->forwardCommand($lti_gui);
                break;


            case 'ilobjectmetadatagui':
                if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
                }
                $md_gui = new ilObjectMetaDataGUI($this->object);
                $this->ctrl->forwardCommand($md_gui);
                break;

//            case 'ilexportgui':
//                $exp = new ilExportGUI($this);
//                $exp->addFormat('xml');
//                $ret = $this->ctrl->forwardCommand($exp);
//                break;

            case 'ilpermissiongui':
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilfilesystemgui":
                $fs_gui = new ilFileSystemGUI($this->object->getDataDirectory());
                $fs_gui->setUseUploadDirectory(true);
                $fs_gui->setTableId("sahsfs" . $this->object->getId());
                $ret = $this->ctrl->forwardCommand($fs_gui);
                break;

            case "ilcertificategui":
                $this->setSettingsSubTabs();
                $ilTabs->setSubTabActive('certificate');

                $guiFactory = new ilCertificateGUIFactory();
                $output_gui = $guiFactory->create($this->object);

                $ret = $this->ctrl->forwardCommand($output_gui);
                break;

            case "illearningprogressgui":
                $new_gui = new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_REPOSITORY, $this->object->getRefId());
                $this->ctrl->forwardCommand($new_gui);

                break;

            case "ilinfoscreengui":
                $info = new ilInfoScreenGUI($this);
                $info->enablePrivateNotes();
                $info->enableLearningProgress();

                // add read / back button
                if ($ilAccess->checkAccess("read", "", $this->object->getRefId())) {
                    if (!$this->object->getEditable()) {
                        $ilToolbar = $GLOBALS['DIC']->toolbar();
                        $ilToolbar->addButtonInstance($this->object->getViewButton());
                    }
                }

                $info->enableNews();
                if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
                    $info->enableNewsEditing();
                    $news_set = new ilSetting("news");
                    $enable_internal_rss = $news_set->get("enable_rss_for_internal");
                    if ($enable_internal_rss) {
                        $info->setBlockProperty("news", "settings", "");
                    }
                }
                // show standard meta data section
                $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

                // forward the command
                $this->ctrl->forwardCommand($info);
                break;

            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

//            case "ilobjstylesheetgui":
//                //$this->addLocations();
//                $this->ctrl->setReturn($this, "properties");
//                $ilTabs->clearTargets();
//                $style_gui = new ilObjStyleSheetGUI("", $this->object->getStyleSheetId(), false, false);
//                $style_gui->omitLocator();
//                if ($cmd == "create" || $_GET["new_type"] == "sty") {
//                    $style_gui->setCreationMode(true);
//                }
//                //$ret =& $style_gui->executeCommand();
//
//                if ($cmd == "confirmedDelete") {
//                    $this->object->setStyleSheetId(0);
//                    $this->object->update();
//                }
//                $ret = $this->ctrl->forwardCommand($style_gui);
//                if ($cmd == "save" || $cmd == "copyStyle" || $cmd == "importStyle") {
//                    $style_id = $ret;
//                    $this->object->setStyleSheetId($style_id);
//                    $this->object->update();
//                    $this->ctrl->redirectByClass("ilobjstylesheetgui", "edit");
//                }
//                break;


            case 'ilobjectcopygui':
                $this->prepareOutput();
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('sahs');
                $this->ctrl->forwardCommand($cp);
                break;

            default:
//                if ($this->object && !$this->object->getEditable()) {
                    $cmd = $this->ctrl->getCmd("properties");
//                } else {
//                    $cmd = $this->ctrl->getCmd("frameset");
//                }
                if ((strtolower($_GET["baseClass"]) == "iladministrationgui" ||
                    $this->getCreationMode() == true) &&
                    $cmd != "frameset") {
                    $cmd .= "Object";
                }

                // #9225
                if ($cmd == "redrawHeaderAction") {
                    $cmd .= "Object";
                }

                $ret = $this->$cmd();
                break;
        }
    }

    /**
     * @return void
     * @throws ilObjectException
     */
    public function viewObject() : void
    {
        if (strtolower($_GET["baseClass"]) == "iladministrationgui") {
            parent::viewObject();
        } else {
        }
    }

    /**
    * module properties
    */
    public function properties() : void
    {
    }

    /**
    * save properties
    */
    public function saveProperties() : void
    {
    }

    ////
    //// CREATION
    ////

    /**
     * no manual SCORM creation, only import at the time
     * @param $a_new_type
     * @return array|ilPropertyFormGUI[]
     * @throws ilCtrlException
     */
    protected function initCreationForms($a_new_type) : array
    {
        $forms = array();

        $this->initUploadForm();
        $forms[self::CFORM_IMPORT] = $this->form;

        $forms[self::CFORM_CLONE] = $this->fillCloneTemplate(null, $a_new_type);

        return $forms;
    }

    /**
     * @return void
     * @throws ilCtrlException
     */
    public function initUploadForm() : void
    {
        global $DIC;
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $this->form = new ilPropertyFormGUI();

        // type selection
        $options = array(
            "scorm2004" => $lng->txt("lm_type_scorm2004"),
            "scorm" => $lng->txt("lm_type_scorm"),
            "exportFile" => $lng->txt("sahs_export_file")
        );
        $si = new ilSelectInputGUI($this->lng->txt("type"), "sub_type");
        $si->setOptions($options);
        $this->form->addItem($si);

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
            $radg = new ilRadioGroupInputGUI($lng->txt("cont_choose_file_source"), "file_source");
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

        // validate file
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_validate_file"), "validate");
        $cb->setValue("y");
        //$cb->setChecked(true);
        $this->form->addItem($cb);

        $this->form->addCommandButton("upload", $lng->txt("import"));
        $this->form->addCommandButton("cancel", $lng->txt("cancel"));

        $this->form->setTitle($lng->txt("import_sahs"));
        $this->form->setFormAction($ilCtrl->getFormAction($this, "upload"));
        $this->form->setTarget(ilFrameTargetInfo::_getFrame("MainContent"));
    }

    /**
     * display status information or report errors messages
     * in case of error
     * @return void
     * @throws ilDatabaseException
     * @throws ilException
     * @throws ilFileUtilsException
     * @throws ilObjectNotFoundException
     */
    public function uploadObject() : void
    {
        global $DIC;
        $rbacsystem = $DIC->access();
        $ilErr = $DIC['ilErr'];
        $refId = (int) $_GET["ref_id"];

        // check create permission
        if (!$rbacsystem->checkAccess("create", '', $refId, "sahs")) {
            $ilErr->raiseError($this->lng->txt("no_create_permission"), $ilErr->WARNING);
        } elseif ($_FILES["scormfile"]["name"]) {
            // check if file was uploaded
            $source = $_FILES["scormfile"]["tmp_name"];
            if (($source == 'none') || (!$source)) {
                $ilErr->raiseError($this->lng->txt("msg_no_file"), $ilErr->MESSAGE);
            }
            // get_cfg_var("upload_max_filesize"); // get the may filesize form t he php.ini
            switch ($_FILES["scormfile"]["error"]) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $ilErr->raiseError($this->lng->txt("err_max_file_size_exceeds"), $ilErr->MESSAGE);
                    break;

                case UPLOAD_ERR_PARTIAL:
                    $ilErr->raiseError($this->lng->txt("err_partial_file_upload"), $ilErr->MESSAGE);
                    break;

                case UPLOAD_ERR_NO_FILE:
                    $ilErr->raiseError($this->lng->txt("err_no_file_uploaded"), $ilErr->MESSAGE);
                    break;
            }

            $file = pathinfo($_FILES["scormfile"]["name"]);
        } elseif ($_POST["uploaded_file"]) {
            // check if the file is in the upload directory and readable
            if (!ilUploadFiles::_checkUploadFile($_POST["uploaded_file"])) {
                $ilErr->raiseError($this->lng->txt("upload_error_file_not_found"), $ilErr->MESSAGE);
            }

            $file = pathinfo($_POST["uploaded_file"]);
        } else {
            $ilErr->raiseError($this->lng->txt("msg_no_file"), $ilErr->MESSAGE);
        }

        $name = substr($file["basename"], 0, strlen($file["basename"]) - strlen($file["extension"]) - 1);
        if ($name == "") {
            $name = $this->lng->txt("no_title");
        }

        $subType = $_POST["sub_type"];

        // always import authoring packages as scorm2004, see bug #27801
        if ($_POST["editable"] == 'y') {
            $subType = "scorm2004";
        }

        // create and insert object in objecttree
        switch ($subType) {
        case "scorm2004":
            $newObj = new ilObjSCORM2004LearningModule();
//            $newObj->setEditable(false);//$_POST["editable"] == 'y');
//            $newObj->setImportSequencing($_POST["import_sequencing"]);
//            $newObj->setSequencingExpertMode($_POST["import_sequencing"]);
            break;

        case "scorm":
            $newObj = new ilObjSCORMLearningModule();
            break;

        case "exportFile":
            $sFile = $_FILES["scormfile"];
            $fType = $sFile["type"];
            $cFileTypes = ["application/zip", "application/x-compressed","application/x-zip-compressed"];
            if (in_array($fType, $cFileTypes)) {
                $timeStamp = time();
                $tempFile = $sFile["tmp_name"];
                $lmDir = ilFileUtils::getWebspaceDir("filesystem") . "/lm_data/";
                $lmTempDir = $lmDir . $timeStamp;
                if (!file_exists($lmTempDir)) {
                    mkdir($lmTempDir, 0755, true);
                }
                $zar = new ZipArchive();
                $zar->open($tempFile);
                $zar->extractTo($lmTempDir);
                $zar->close();
                $importer = new ilScormAiccImporter();
                $import_dirname = $lmTempDir . '/' . substr($_FILES["scormfile"]["name"], 0, strlen($_FILES["scormfile"]["name"]) - 4);
                if ($importer->importXmlRepresentation("sahs", "", $import_dirname, null) == true) {
                    $importFromXml = true;
                }
                $mprops = [];
                $mprops = $importer->moduleProperties;
                $subType = $mprops["SubType"][0];
                if ($subType == "scorm") {
                    $newObj = new ilObjSCORMLearningModule();
                } else {
                    $newObj = new ilObjSCORM2004LearningModule();
                    // $newObj->setEditable($_POST["editable"]=='y');
                    // $newObj->setImportSequencing($_POST["import_sequencing"]);
                    // $newObj->setSequencingExpertMode($_POST["import_sequencing"]);
                }
            }
            break;
        }

        $newObj->setTitle($name);
        $newObj->setSubType($subType);
        $newObj->setDescription("");
        $newObj->setOfflineStatus(false);
        $newObj->create(true);
        $newObj->createReference();
        $newObj->putInTree($refId);
        $newObj->setPermissions($refId);

        // create data directory, copy file to directory
        $newObj->createDataDirectory();

        if ($_FILES["scormfile"]["name"]) {
            if ($importFromXml) {
                $scormFile = "content.zip";
                $scormFilePath = $import_dirname . "/" . $scormFile;
                $file_path = $newObj->getDataDirectory() . "/" . $scormFile;
                ilFileUtils::rename($scormFilePath, $file_path);
                ilFileUtils::unzip($file_path);
                unlink($file_path);
                ilFileUtils::delDir($lmTempDir, false);
            } else {
                // copy uploaded file to data directory
                $file_path = $newObj->getDataDirectory() . "/" . $_FILES["scormfile"]["name"];
                ilFileUtils::moveUploadedFile(
                    $_FILES["scormfile"]["tmp_name"],
                    $_FILES["scormfile"]["name"],
                    $file_path
                );
                ilFileUtils::unzip($file_path);
            }
        } else {
            // copy uploaded file to data directory
            $file_path = $newObj->getDataDirectory() . "/" . $_POST["uploaded_file"];
            ilUploadFiles::_copyUploadFile($_POST["uploaded_file"], $file_path);
            ilFileUtils::unzip($file_path);
        }
        ilUtil::renameExecutables($newObj->getDataDirectory());

        $title = $newObj->readObject();
        if ($title != "") {
            ilObject::_writeTitle($newObj->getId(), $title);
        }

        //auto set learning progress settings
        $newObj->setLearningProgressSettingsAtUpload();

        if ($importFromXml) {
            $importer->writeData("sahs", "5.1.0", $newObj->getId());
        }

        ilUtil::sendInfo($this->lng->txt($newObj->getType() . "_added"), true);
        ilUtil::redirect("ilias.php?baseClass=ilSAHSEditGUI&ref_id=" . $newObj->getRefId());
    }

    /**
     * @return void
     * @throws ilDatabaseException
     * @throws ilException
     * @throws ilFileUtilsException
     * @throws ilObjectNotFoundException
     */
    public function upload() : void
    {
        $this->uploadObject();
    }



    /**
    * save new learning module to db
    */
//    public function saveObject()
//    {
//        global $DIC;
//        $ilErr = $DIC["ilErr"];
//
//        if (trim($_POST["title"]) == "") {
//            $ilErr->raiseError($this->lng->txt("msg_no_title"), $ilErr->MESSAGE);
//        }
//        $newObj = new ilObjSCORM2004LearningModule();
//        $newObj->setTitle(ilUtil::stripSlashes($_POST["title"]));
//        $newObj->setSubType("scorm2004");
//        $newObj->setEditable(true);
//        $newObj->setDescription(ilUtil::stripSlashes($_POST["desc"]));
//        $newObj->create();
//        $newObj->createReference();
//        $newObj->putInTree($_GET["ref_id"]);
//        $newObj->setPermissions($_GET["ref_id"]);
//        $newObj->createDataDirectory();
//        $newObj->createScorm2004Tree();
//        ilUtil::sendInfo($this->lng->txt($newObj->getType() . "_added"), true);
//
//        // #7375
//        $this->ctrl->setParameterByClass("ilObjSCORM2004LearningModuleGUI", "ref_id", $newObj->getRefId());
//        $this->ctrl->redirectByClass(array("ilSAHSEditGUI", "ilObjSCORM2004LearningModuleGUI"), "showOrganization");
//    }


//    /**
//    * permission form
//    */
//    public function info()
//    {
//        $this->infoObject();
//    }

//    /**
//    * show owner of learning module
//    */
//    public function owner()
//    {
//        $this->ownerObject();
//    }

    /**
     * output main header (title and locator)
     * @return void
     */
    public function getTemplate() : void
    {
        global $DIC;
        $lng = $DIC->language();

        $this->tpl->loadStandardTemplate();
    }

    /**
     * @return void
     */
    protected function setTabs() : void
    {
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lm.svg"));
        $this->tpl->setTitle($this->object->getTitle());
        if (strtolower($_GET["baseClass"]) == "ilsahseditgui") {
            $this->getTabs($this->tabs_gui);
        }
        //if(strtolower($_GET["baseClass"]) == "ilsahseditgui") $this->getTabs();
    }

    /**
     * Shows the certificate editor
     * @return void
     */
    public function certificate() : void
    {
        $guiFactory = new ilCertificateGUIFactory();
        $output_gui = $guiFactory->create($this->object);

        $output_gui->certificateEditor();
    }

    /**
     * adds tabs to tab gui object
     * @return void
     * @throws ilCtrlException
     */
    protected function getTabs() : void
    {
        global $DIC;
        $rbacsystem = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $ilHelp = $DIC->help();

        if ($this->ctrl->getCmd() == "delete") {
            return;
        }

        switch ($this->object->getSubType()) {
            case "scorm2004":
                $ilHelp->setScreenIdComponent("sahs13");
                break;

            case "scorm":
                $ilHelp->setScreenIdComponent("sahs12");
                break;
        }

        // file system gui tabs
        // properties
        $ilCtrl->setParameterByClass("ilfilesystemgui", "resetoffset", 1);
        $this->tabs_gui->addTarget(
            "cont_list_files",
            $this->ctrl->getLinkTargetByClass("ilfilesystemgui", "listFiles"),
            "",
            "ilfilesystemgui"
        );
        $ilCtrl->setParameterByClass("ilfilesystemgui", "resetoffset", "");

        // info screen
        $force_active = ($this->ctrl->getNextClass() == "ilinfoscreengui")
            ? true
            : false;
        $this->tabs_gui->addTarget(
            "info_short",
            $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"),
            "",
            "ilinfoscreengui",
            "",
            $force_active
        );

        // properties
        $this->tabs_gui->addTarget(
            "settings",
            $this->ctrl->getLinkTarget($this, "properties"),
            array("", "properties"),
            get_class($this)
        );
        // if (ilLearningProgressAccess::checkAccess($this->object->getRefId()) || $rbacsystem->checkAccess("edit_permission", "", $this->object->getRefId())) {
        // //if scorm && offline_mode activated
        // if ($this->object->getSubType() == "scorm2004" || $this->object->getSubType() == "scorm") {
        // if ($this->object->getOfflineMode() == true) {
        // $this->tabs_gui->addTarget(
        // "offline_mode_manager",
        // $this->ctrl->getLinkTarget($this, "offlineModeManager"),
        // "offlineModeManager",
        // "ilobjscormlearningmodulegui"
        // );
        // }
        // }
        // }
        if (ilLearningProgressAccess::checkAccess($this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'learning_progress',
                $this->ctrl->getLinkTargetByClass(array('illearningprogressgui'), ''),
                '',
                array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui')
            );
        }

        // tracking data
        if ($rbacsystem->checkAccess("read_learning_progress", "", $this->object->getRefId()) || $rbacsystem->checkAccess("edit_learning_progress", "", $this->object->getRefId())) {
            if ($this->object->getSubType() == "scorm2004" || $this->object->getSubType() == "scorm") {
                $privacy = ilPrivacySettings::getInstance();
                if ($privacy->enabledSahsProtocolData()) {
                    $this->tabs_gui->addTarget(
                        "cont_tracking_data",
                        $this->ctrl->getLinkTarget($this, "showTrackingItems"),
                        "showTrackingItems",
                        get_class($this)
                    );
                }
            }
        }
        $mdgui = new ilObjectMetaDataGUI($this->object);
        $mdtab = $mdgui->getTab();
        if ($mdtab) {
            $this->tabs_gui->addTarget(
                "meta_data",
                $mdtab,
                "",
                "ilmdeditorgui"
            );
        }

        // export
        if ($rbacsystem->checkAccess("edit", "", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "export",
                $this->ctrl->getLinkTarget($this, "export"),
                array("", "export"),
                get_class($this)
            );
        }

        // perm
        if ($rbacsystem->checkAccess('edit', "", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
    }


    /**
     * goto target course
     * @param $a_target
     * @return void
     */
    public static function _goto($a_target) : void
    {
        global $DIC;
        $ilAccess = $DIC->access();
        $ilErr = $DIC['ilErr'];
        $lng = $DIC->language();

        $parts = explode("_", $a_target);

        if ($ilAccess->checkAccess("write", "", (int) $parts[0])) {
            $_GET["cmd"] = "";
            $_GET["baseClass"] = "ilSAHSEditGUI";
            $_GET["ref_id"] = $parts[0];
            $_GET["obj_id"] = $parts[1];
            exit;
        }
        if ($ilAccess->checkAccess("visible", "", (int) $parts[0]) || $ilAccess->checkAccess("read", "", (int) $parts[0])) {
            $_GET["cmd"] = "infoScreen";
            $_GET["baseClass"] = "ilSAHSPresentationGUI";
            $_GET["ref_id"] = $parts[0];
            exit;
        } else {
            if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
                ilUtil::sendInfo(sprintf(
                    $lng->txt("msg_no_perm_read_item"),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($parts[0]))
                ), true);
                ilObjectGUI::_gotoRepositoryRoot();
            }
        }

        $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
    }

    /**
     * @return void
     * @throws ilCtrlException
     */
    public function addLocatorItems() : void
    {
        global $DIC;
        $ilLocator = $DIC['ilLocator'];

        if (is_object($this->object)) {
            $ilLocator->addItem(
                $this->object->getTitle(),
                $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"),
                "",
                (int) $_GET["ref_id"]
            );
        }
    }

//    /**
//     * List files
//     *
//     * @param
//     * @return
//     */
//    public function editContent()
//    {
//        global $DIC;
//        $ilCtrl = $DIC->ctrl();
//
    ////        if (!$this->object->getEditable()) {
    ////            $ilCtrl->redirectByClass("ilfilesystemgui", "listFiles");
    ////        } else {
//        $ilCtrl->redirectByClass("ilobjscorm2004learningmodulegui", "editOrganization");
    ////        }
//    }

    /**
     * @return void
     * @throws ilCtrlException
     */
    public function setSettingsSubTabs() : void
    {
        global $DIC;
        $lng = $DIC->language();
        $ilTabs = $DIC->tabs();
        $ilCtrl = $DIC->ctrl();

        $ilTabs->addSubTabTarget(
            "cont_settings",
            $this->ctrl->getLinkTarget($this, "properties"),
            array("edit", ""),
            get_class($this)
        );

        $ilTabs->addSubTabTarget(
            "cont_sc_new_version",
            $this->ctrl->getLinkTarget($this, "newModuleVersion"),
            array("edit", ""),
            get_class($this)
        );

        $validator = new ilCertificateActiveValidator();
        if (true === $validator->validate()) {
            // // create and insert object in objecttree
            // $ilTabs->addSubTabTarget("certificate",
            // $this->ctrl->getLinkTarget($this, "certificate"),
            // array("certificate", "certificateEditor", "certificateRemoveBackground", "certificateSave",
            // "certificatePreview", "certificateDelete", "certificateUpload", "certificateImport")
            // );
            $ilTabs->addSubTabTarget(
                "certificate",
                $this->ctrl->getLinkTargetByClass("ilcertificategui", "certificateeditor"),
                "",
                "ilcertificategui"
            );
        }

        $lti_settings = new ilLTIProviderObjectSettingGUI($this->object->getRefId());
        if ($lti_settings->hasSettingsAccess()) {
            $ilTabs->addSubTabTarget(
                'lti_provider',
                $this->ctrl->getLinkTargetByClass(ilLTIProviderObjectSettingGUI::class)
            );
        }

        $ilTabs->setTabActive('settings');
    }

    /**
     * @return mixed
     * @throws ilCtrlException
     */
    public function export()
    {
        $GLOBALS['DIC']->tabs()->setTabActive('export');
        $exp_gui = new ilExportGUI($this);
        $this->ctrl->setCmd("listExportFiles");
        $exp_gui->addFormat("xml");
        return $this->ctrl->forwardCommand($exp_gui);
    }

    /**
     * @return void
     */
    public function exportModule() : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $moduleId = ilObject::_lookupObjectId($_GET["ref_id"]);
        $exporter = new ilScormAiccExporter();
//        $xml = $exporter->getXmlRepresentation("sahs", "5.1.0", $moduleId);
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return "sahs";
    }
} // END class.ilObjSAHSLearningModule
