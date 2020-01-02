<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once "./Services/Object/classes/class.ilObjectGUI.php";
require_once("./Services/FileSystem/classes/class.ilFileSystemGUI.php");

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
        $lng = $DIC['lng'];

        $lng->loadLanguageModule("content");
        $this->type = "sahs";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        $ilTabs = $DIC['ilTabs'];
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
                include_once 'Services/Object/classes/class.ilObjectMetaDataGUI.php';
                $md_gui = new ilObjectMetaDataGUI($this->object);
                $this->ctrl->forwardCommand($md_gui);
                break;
                
            case 'ilexportgui':
            case 'ilpermissiongui':
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilfilesystemgui":
                $this->fs_gui = new ilFileSystemGUI($this->object->getDataDirectory());
                $this->fs_gui->setUseUploadDirectory(true);
                $this->fs_gui->setTableId("sahsfs" . $this->object->getId());
                $ret = $this->ctrl->forwardCommand($this->fs_gui);
                break;

            case "ilcertificategui":
                $this->setSettingsSubTabs();
                $ilTabs->setSubTabActive('certificate');

                $guiFactory = new ilCertificateGUIFactory();
                $output_gui = $guiFactory->create($this->object);

                $ret = $this->ctrl->forwardCommand($output_gui);
                break;

            case "illearningprogressgui":
                include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';

                $new_gui = new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_REPOSITORY, $this->object->getRefId());
                $this->ctrl->forwardCommand($new_gui);

                break;

            case "ilinfoscreengui":
                include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");

                $info = new ilInfoScreenGUI($this);
                $info->enablePrivateNotes();
                $info->enableLearningProgress();
                
                // add read / back button
                if ($ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
                    if (!$this->object->getEditable()) {
                        $ilToolbar = $GLOBALS['DIC']->toolbar();
                        $ilToolbar->addButtonInstance($this->object->getViewButton());
                    }
                }

                $info->enableNews();
                if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) {
                    $info->enableNewsEditing();
                    $news_set = new ilSetting("news");
                    $enable_internal_rss = $news_set->get("enable_rss_for_internal");
                    if ($enable_internal_rss) {
                        $info->setBlockProperty("news", "settings", true);
                    }
                }
                // show standard meta data section
                $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
        
                // forward the command
                $this->ctrl->forwardCommand($info);
                break;
                
            case "ilcommonactiondispatchergui":
                include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            case "ilobjstylesheetgui":
                //$this->addLocations();
                $this->ctrl->setReturn($this, "properties");
                $ilTabs->clearTargets();
                $style_gui = new ilObjStyleSheetGUI("", $this->object->getStyleSheetId(), false, false);
                $style_gui->omitLocator();
                if ($cmd == "create" || $_GET["new_type"]=="sty") {
                    $style_gui->setCreationMode(true);
                }
                //$ret =& $style_gui->executeCommand();

                if ($cmd == "confirmedDelete") {
                    $this->object->setStyleSheetId(0);
                    $this->object->update();
                }
                $ret = $this->ctrl->forwardCommand($style_gui);
                if ($cmd == "save" || $cmd == "copyStyle" || $cmd == "importStyle") {
                    $style_id = $ret;
                    $this->object->setStyleSheetId($style_id);
                    $this->object->update();
                    $this->ctrl->redirectByClass("ilobjstylesheetgui", "edit");
                }
                break;


            case 'ilobjectcopygui':
                $this->prepareOutput();
                include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('sahs');
                $this->ctrl->forwardCommand($cp);
                break;

            default:
                if ($this->object && !$this->object->getEditable()) {
                    $cmd = $this->ctrl->getCmd("properties");
                } else {
                    $cmd = $this->ctrl->getCmd("frameset");
                }
                if ((strtolower($_GET["baseClass"]) == "iladministrationgui" ||
                    $this->getCreationMode() == true) &&
                    $cmd != "frameset") {
                    $cmd.= "Object";
                }
                
                // #9225
                if ($cmd == "redrawHeaderAction") {
                    $cmd .= "Object";
                }

                $ret = $this->$cmd();
                break;
        }
    }


    public function viewObject()
    {
        if (strtolower($_GET["baseClass"]) == "iladministrationgui") {
            parent::viewObject();
        } else {
        }
    }

    /**
    * module properties
    */
    public function properties()
    {
    }

    /**
    * save properties
    */
    public function saveProperties()
    {
    }

    ////
    //// CREATION
    ////

    /**
    * no manual SCORM creation, only import at the time
    */
    public function initCreationForms($a_new_type)
    {
        $forms = array();

        $this->initUploadForm();
        $forms[self::CFORM_IMPORT] = $this->form;

        $this->initCreationForm();
        $forms[self::CFORM_NEW] = $this->form;

        $forms[self::CFORM_CLONE] = $this->fillCloneTemplate(null, $a_new_type);
    
        return $forms;
    }

    /**
    * Init  form.
    *
    * @param        int        $a_mode        Edit Mode
    */
    public function initCreationForm()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
    
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
    
        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $ti->setSize(min(40, ilObject::TITLE_LENGTH));
        $ti->setMaxLength(ilObject::TITLE_LENGTH);
        $ti->setRequired(true);
        $this->form->addItem($ti);
        
        // text area
        $ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
        $ta->setCols(40);
        $ta->setRows(2);
        $this->form->addItem($ta);
        
    
        $this->form->addCommandButton("save", $lng->txt("sahs_add"));
        $this->form->addCommandButton("cancel", $lng->txt("cancel"));
                    
        $this->form->setTitle($lng->txt("scorm_new"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
        $this->form->setTarget(ilFrameTargetInfo::_getFrame("MainContent"));
    }
    
    /**
    * Init upload form.
    */
    public function initUploadForm()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
    
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
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
        
        
        // todo wysiwyg editor removement
        
        $options = array();
        include_once 'Services/FileSystem/classes/class.ilUploadFiles.php';
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

        // import for editing
        $cb = new ilCheckboxInputGUI($this->lng->txt("sahs_authoring_mode"), "editable");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("sahs_authoring_mode_info"));
        $this->form->addItem($cb);
        
        //
        $radg = new ilRadioGroupInputGUI($lng->txt("sahs_sequencing"), "import_sequencing");
        $radg->setValue(0);
        $op1 = new ilRadioOption($lng->txt("sahs_std_sequencing"), 0, $lng->txt("sahs_std_sequencing_info"));
        $radg->addOption($op1);
        $op1 = new ilRadioOption($lng->txt("sahs_import_sequencing"), 1, $lng->txt("sahs_import_sequencing_info"));
        $radg->addOption($op1);
        $cb->addSubItem($radg);
        

        $this->form->addCommandButton("upload", $lng->txt("import"));
        $this->form->addCommandButton("cancel", $lng->txt("cancel"));
                    
        $this->form->setTitle($lng->txt("import_sahs"));
        $this->form->setFormAction($ilCtrl->getFormAction($this, "upload"));
        $this->form->setTarget(ilFrameTargetInfo::_getFrame("MainContent"));
    }
    
    /**
    * display status information or report errors messages
    * in case of error
    *
    * @access	public
    */
    public function uploadObject()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];

        include_once 'Services/FileSystem/classes/class.ilUploadFiles.php';

        // check create permission
        if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], "sahs")) {
            $this->ilias->raiseError($this->lng->txt("no_create_permission"), $this->ilias->error_obj->WARNING);
        } elseif ($_FILES["scormfile"]["name"]) {
            // check if file was uploaded
            $source = $_FILES["scormfile"]["tmp_name"];
            if (($source == 'none') || (!$source)) {
                $this->ilias->raiseError($this->lng->txt("msg_no_file"), $this->ilias->error_obj->MESSAGE);
            }
            // get_cfg_var("upload_max_filesize"); // get the may filesize form t he php.ini
            switch ($_FILES["scormfile"]["error"]) {
                case UPLOAD_ERR_INI_SIZE:
                    $this->ilias->raiseError($this->lng->txt("err_max_file_size_exceeds"), $this->ilias->error_obj->MESSAGE);
                    break;
    
                case UPLOAD_ERR_FORM_SIZE:
                    $this->ilias->raiseError($this->lng->txt("err_max_file_size_exceeds"), $this->ilias->error_obj->MESSAGE);
                    break;
    
                case UPLOAD_ERR_PARTIAL:
                    $this->ilias->raiseError($this->lng->txt("err_partial_file_upload"), $this->ilias->error_obj->MESSAGE);
                    break;
    
                case UPLOAD_ERR_NO_FILE:
                    $this->ilias->raiseError($this->lng->txt("err_no_file_uploaded"), $this->ilias->error_obj->MESSAGE);
                    break;
            }
    
            $file = pathinfo($_FILES["scormfile"]["name"]);
        } elseif ($_POST["uploaded_file"]) {
            // check if the file is in the upload directory and readable
            if (!ilUploadFiles::_checkUploadFile($_POST["uploaded_file"])) {
                $this->ilias->raiseError($this->lng->txt("upload_error_file_not_found"), $this->ilias->error_obj->MESSAGE);
            }

            $file = pathinfo($_POST["uploaded_file"]);
        } else {
            $this->ilias->raiseError($this->lng->txt("msg_no_file"), $this->ilias->error_obj->MESSAGE);
        }

        $name = substr($file["basename"], 0, strlen($file["basename"]) - strlen($file["extension"]) - 1);
        if ($name == "") {
            $name = $this->lng->txt("no_title");
        }

        $subType = $_POST["sub_type"];
        // create and insert object in objecttree
        switch ($subType) {
        case "scorm2004":
            include_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");
            $newObj = new ilObjSCORM2004LearningModule();
            $newObj->setEditable($_POST["editable"]=='y');
            $newObj->setImportSequencing($_POST["import_sequencing"]);
            $newObj->setSequencingExpertMode($_POST["import_sequencing"]);
            break;

        case "scorm":
            include_once("./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php");
            $newObj = new ilObjSCORMLearningModule();
            break;

        case "exportFile":
            $sFile = $_FILES["scormfile"];
            $fType = $sFile["type"];
            $cFileTypes = ["application/zip", "application/x-compressed","application/x-zip-compressed"];
            if (in_array($fType, $cFileTypes)) {
                $timeStamp = time();
                $tempFile = $sFile["tmp_name"];
                $lmDir = ilUtil::getWebspaceDir("filesystem") . "/lm_data/";
                $lmTempDir = $lmDir . $timeStamp;
                if (!file_exists($lmTempDir)) {
                    mkdir($lmTempDir, 0755, true);
                }
                $zar = new ZipArchive();
                $zar->open($tempFile);
                $zar->extractTo($lmTempDir);
                $zar->close();
                require_once "./Modules/ScormAicc/classes/class.ilScormAiccImporter.php";
                $importer = new ilScormAiccImporter();
                $import_dirname = $lmTempDir . '/' . substr($_FILES["scormfile"]["name"], 0, strlen($a_filename) - 4);
                if ($importer->importXmlRepresentation("sahs", null, $import_dirname, "") == true) {
                    $importFromXml = true;
                }
                $mprops = [];
                $mprops = $importer->moduleProperties;
                $subType = $mprops["SubType"][0];
                if ($subType == "scorm") {
                    include_once("./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php");
                    $newObj = new ilObjSCORMLearningModule();
                } else {
                    include_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");
                    $newObj = new ilObjSCORM2004LearningModule();
                    //					$newObj->setEditable($_POST["editable"]=='y');
//					$newObj->setImportSequencing($_POST["import_sequencing"]);
//					$newObj->setSequencingExpertMode($_POST["import_sequencing"]);
                }
            }
            break;
        }

        $newObj->setTitle($name);
        $newObj->setSubType($subType);
        $newObj->setDescription("");
        $newObj->setOfflineStatus(true);
        $newObj->create(true);
        $newObj->createReference();
        $newObj->putInTree($_GET["ref_id"]);
        $newObj->setPermissions($_GET["ref_id"]);

        // create data directory, copy file to directory
        $newObj->createDataDirectory();

        if ($_FILES["scormfile"]["name"]) {
            if ($importFromXml) {
                $scormFile = "content.zip";
                $scormFilePath = $import_dirname . "/" . $scormFile;
                $file_path = $newObj->getDataDirectory() . "/" . $scormFile;
                ilFileUtils::rename($scormFilePath, $file_path);
                ilUtil::unzip($file_path);
                unlink($file_path);
                ilUtil::delDir($lmTempDir, false);
            } else {
                // copy uploaded file to data directory
                $file_path = $newObj->getDataDirectory() . "/" . $_FILES["scormfile"]["name"];
                ilUtil::moveUploadedFile(
                    $_FILES["scormfile"]["tmp_name"],
                    $_FILES["scormfile"]["name"],
                    $file_path
                );
                ilUtil::unzip($file_path);
            }
        } else {
            // copy uploaded file to data directory
            $file_path = $newObj->getDataDirectory() . "/" . $_POST["uploaded_file"];
            ilUploadFiles::_copyUploadFile($_POST["uploaded_file"], $file_path);
            ilUtil::unzip($file_path);
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

    public function upload()
    {
        $this->uploadObject();
    }
    
    

    /**
    * save new learning module to db
    */
    public function saveObject()
    {
        if (trim($_POST["title"]) == "") {
            $this->ilias->raiseError($this->lng->txt("msg_no_title"), $this->ilias->error_obj->MESSAGE);
        }
        
        include_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");
        $newObj = new ilObjSCORM2004LearningModule();
        $newObj->setTitle(ilUtil::stripSlashes($_POST["title"]));
        $newObj->setSubType("scorm2004");
        $newObj->setEditable(true);
        $newObj->setDescription(ilUtil::stripSlashes($_POST["desc"]));
        $newObj->create();
        $newObj->createReference();
        $newObj->putInTree($_GET["ref_id"]);
        $newObj->setPermissions($_GET["ref_id"]);
        $newObj->createDataDirectory();
        $newObj->createScorm2004Tree();
        ilUtil::sendInfo($this->lng->txt($newObj->getType() . "_added"), true);

        // #7375
        $this->ctrl->setParameterByClass("ilObjSCORM2004LearningModuleGUI", "ref_id", $newObj->getRefId());
        $this->ctrl->redirectByClass(array("ilSAHSEditGUI", "ilObjSCORM2004LearningModuleGUI"), "showOrganization");
    }


    /**
    * permission form
    */
    public function info()
    {
        $this->infoObject();
    }

    /**
    * show owner of learning module
    */
    public function owner()
    {
        $this->ownerObject();
    }

    /**
    * output main header (title and locator)
    */
    public function getTemplate()
    {
        global $DIC;
        $lng = $DIC['lng'];

        $this->tpl->getStandardTemplate();
    }

    /**
    * output tabs
    */
    public function setTabs()
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
    */
    public function certificate()
    {
        $guiFactory = new ilCertificateGUIFactory();
        $output_gui = $guiFactory->create($this->object);

        $output_gui->certificateEditor();
    }
    
    /**
    * adds tabs to tab gui object
    *
    * @param	object		$tabs_gui		ilTabsGUI object
    */
    public function getTabs()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilHelp = $DIC['ilHelp'];
        
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
            
        // learning progress and offline mode
        include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
        if (ilLearningProgressAccess::checkAccess($this->object->getRefId()) || $rbacsystem->checkAccess("edit_permission", "", $this->object->getRefId())) {
            //if scorm && offline_mode activated
            if ($this->object->getSubType() == "scorm2004" || $this->object->getSubType() == "scorm") {
                if ($this->object->getOfflineMode() == true) {
                    $this->tabs_gui->addTarget(
                        "offline_mode_manager",
                        $this->ctrl->getLinkTarget($this, "offlineModeManager"),
                        "offlineModeManager",
                        "ilobjscormlearningmodulegui"
                    );
                }
            }
        }
        if (ilLearningProgressAccess::checkAccess($this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'learning_progress',
                $this->ctrl->getLinkTargetByClass(array('illearningprogressgui'), ''),
                '',
                array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui')
            );
        }

        // tracking data
        if ($rbacsystem->checkAccess("read_learning_progress", $this->object->getRefId()) || $rbacsystem->checkAccess("edit_learning_progress", $this->object->getRefId())) {
            if ($this->object->getSubType() == "scorm2004" || $this->object->getSubType() == "scorm") {
                include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
                $privacy = ilPrivacySettings::_getInstance();
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

        // edit meta
        include_once "Services/Object/classes/class.ilObjectMetaDataGUI.php";
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
        if ($rbacsystem->checkAccess("edit_permission", "", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "export",
                $this->ctrl->getLinkTarget($this, "export"),
                array("", "export"),
                get_class($this)
            );
        }

        // perm
        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
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
    */
    public static function _goto($a_target)
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];

        $parts = explode("_", $a_target);

        if ($ilAccess->checkAccess("write", "", $parts[0])) {
            $_GET["cmd"] = "";
            $_GET["baseClass"] = "ilSAHSEditGUI";
            $_GET["ref_id"] = $parts[0];
            $_GET["obj_id"] = $parts[1];
            include("ilias.php");
            exit;
        }
        if ($ilAccess->checkAccess("visible", "", $parts[0]) || $ilAccess->checkAccess("read", "", $parts[0])) {
            $_GET["cmd"] = "infoScreen";
            $_GET["baseClass"] = "ilSAHSPresentationGUI";
            $_GET["ref_id"] = $parts[0];
            include("ilias.php");
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

    public function addLocatorItems()
    {
        global $DIC;
        $ilLocator = $DIC['ilLocator'];
        
        if (is_object($this->object)) {
            $ilLocator->addItem(
                $this->object->getTitle(),
                $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"),
                "",
                $_GET["ref_id"]
            );
        }
    }
    
    /**
     * List files
     *
     * @param
     * @return
     */
    public function editContent()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        
        if (!$this->object->getEditable()) {
            $ilCtrl->redirectByClass("ilfilesystemgui", "listFiles");
        } else {
            $ilCtrl->redirectByClass("ilobjscorm2004learningmodulegui", "editOrganization");
        }
    }

    /**
     * set Tabs for settings
     */
    public function setSettingsSubTabs()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilTabs = $DIC['ilTabs'];
        $ilCtrl = $DIC['ilCtrl'];

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
    
        include_once "Services/Certificate/classes/class.ilCertificate.php";
        if (ilCertificate::isActive()) {
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

    public function export()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilTabs = $DIC['ilTabs'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $ilTabs->activateTab("export");
        include_once("./Services/Export/classes/class.ilExportGUI.php");
        $exp_gui = new ilExportGUI($this);
        $ilCtrl->setCmd("");
        $exp_gui->addFormat("xml");
        $ret = $this->ctrl->forwardCommand($exp_gui);
        return $ret;
    }

    public function exportModule()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $moduleId = ilObject::_lookupObjectId($_GET["ref_id"]);
        require_once "./Modules/ScormAicc/classes/class.ilScormAiccExporter.php";
        $exporter = new ilScormAiccExporter();
        $xml = $exporter->getXmlRepresentation("sahs", "5.1.0", $moduleId);
    }

    public function getType()
    {
        return "sahs";
    }
} // END class.ilObjSAHSLearningModule
