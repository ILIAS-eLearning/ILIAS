<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* User Interface class for file based learning modules (HTML)
*
* @author Alex Killing <alex.killing@gmx.de>
*
* $Id$
*
* @ilCtrl_Calls ilObjFileBasedLMGUI: ilFileSystemGUI, ilObjectMetaDataGUI, ilPermissionGUI, ilLearningProgressGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilObjFileBasedLMGUI: ilCommonActionDispatcherGUI
* @ilCtrl_Calls ilObjFileBasedLMGUI: ilExportGUI
* @ingroup ModulesHTMLLearningModule
*/

require_once("./Services/Object/classes/class.ilObjectGUI.php");
require_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLM.php");
require_once("./Services/Table/classes/class.ilTableGUI.php");
require_once("./Services/FileSystem/classes/class.ilFileSystemGUI.php");

class ilObjFileBasedLMGUI extends ilObjectGUI
{
    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    public $output_prepared;

    /**
    * Constructor
    *
    * @access	public
    */
    public function __construct($a_data, $a_id = 0, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->locator = $DIC["ilLocator"];
        $this->tabs = $DIC->tabs();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->tree = $DIC->repositoryTree();
        $this->tpl = $DIC["tpl"];
        $this->access = $DIC->access();
        $this->error = $DIC["ilErr"];
        $this->toolbar = $DIC->toolbar();
        $this->help = $DIC["ilHelp"];
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->ctrl = $ilCtrl;
        $this->ctrl->saveParameter($this, array("ref_id"));

        $this->type = "htlm";
        $lng->loadLanguageModule("content");
        $lng->loadLanguageModule("obj");

        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        //$this->actions = $this->objDefinition->getActions("mep");
        $this->output_prepared = $a_prepare_output;
    }
    
    /**
    * execute command
    */
    public function executeCommand()
    {
        $ilUser = $this->user;
        $ilLocator = $this->locator;
        $ilTabs = $this->tabs;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
    
        if (strtolower($_GET["baseClass"]) == "iladministrationgui" ||
            $this->getCreationMode() == true) {
            $this->prepareOutput();
        } else {
            if (!in_array($cmd, array("", "framset")) || $next_class != "") {
                $this->getTemplate();
                $this->setLocator();
                $this->setTabs();
            }
        }

        switch ($next_class) {
            case 'ilobjectmetadatagui':
                $this->checkPermission("write");
                $ilTabs->activateTab('id_meta_data');
                include_once "Services/Object/classes/class.ilObjectMetaDataGUI.php";
                $md_gui = new ilObjectMetaDataGUI($this->object);
                $this->ctrl->forwardCommand($md_gui);
                break;

            case "ilfilesystemgui":
                $this->checkPermission("write");
                $ilTabs->activateTab('id_list_files');
                $fs_gui = new ilFileSystemGUI($this->object->getDataDirectory());
                $fs_gui->activateLabels(true, $this->lng->txt("cont_purpose"));
                $fs_gui->setUseUploadDirectory(true);
                $fs_gui->setTableId("htlmfs" . $this->object->getId());
                if ($this->object->getStartFile() != "") {
                    $fs_gui->labelFile(
                        $this->object->getStartFile(),
                        $this->lng->txt("cont_startfile")
                    );
                }
                $fs_gui->addCommand($this, "setStartFile", $this->lng->txt("cont_set_start_file"));
                
                $this->ctrl->forwardCommand($fs_gui);
                                            
                // try to set start file automatically
                require_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMAccess.php");
                if (!ilObjFileBasedLMAccess::_determineStartUrl($this->object->getId())) {
                    $do_update = false;
                                        
                    $pcommand = $fs_gui->getLastPerformedCommand();
                    if (is_array($pcommand)) {
                        $valid = array("index.htm", "index.html", "start.htm", "start.html");
                        if ($pcommand["cmd"] == "create_file") {
                            $file = strtolower(basename($pcommand["name"]));
                            if (in_array($file, $valid)) {
                                $this->object->setStartFile($pcommand["name"]);
                                $do_update = $pcommand["name"];
                            }
                        } elseif ($pcommand["cmd"] == "unzip_file") {
                            $zip_file = strtolower(basename($pcommand["name"]));
                            $suffix = strrpos($zip_file, ".");
                            if ($suffix) {
                                $zip_file = substr($zip_file, 0, $suffix);
                            }
                            foreach ($pcommand["added"] as $file) {
                                $chk_file = null;
                                if (stristr($file, ".htm")) {
                                    $chk_file = strtolower(basename($file));
                                    $suffix = strrpos($chk_file, ".");
                                    if ($suffix) {
                                        $chk_file = substr($chk_file, 0, $suffix);
                                    }
                                }
                                if (in_array(basename($file), $valid) ||
                                    ($zip_file && $chk_file && $chk_file == $zip_file)) {
                                    $this->object->setStartFile($file);
                                    $do_update = $file;
                                    break;
                                }
                            }
                        }
                    }
                    
                    if ($do_update) {
                        ilUtil::sendInfo(sprintf($this->lng->txt("cont_start_file_set_to"), $do_update), true);
                        
                        $this->object->update();
                        $this->ctrl->redirectByClass("ilfilesystemgui", "listFiles");
                    }
                }
                break;

            case "ilinfoscreengui":
                $ret = $this->outputInfoScreen();
                break;

            case "illearningprogressgui":
                $ilTabs->activateTab('id_learning_progress');
                include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
                $new_gui = new ilLearningProgressGUI(
                    ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $_GET['user_id'] ? $_GET['user_id'] : $ilUser->getId()
                );
                $this->ctrl->forwardCommand($new_gui);
                break;
                
            case 'ilpermissiongui':
                $ilTabs->activateTab('id_permissions');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilexportgui":
                $ilTabs->activateTab("export");
                include_once("./Services/Export/classes/class.ilExportGUI.php");
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                $exp_gui->addFormat("html", "", $this, "exportHTML");
                $ret = $this->ctrl->forwardCommand($exp_gui);
//				$this->tpl->show();
                break;

            case "ilcommonactiondispatchergui":
                include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
            
            default:
                $cmd = $this->ctrl->getCmd("frameset");
                if (strtolower($_GET["baseClass"]) == "iladministrationgui" ||
                    $this->getCreationMode() == true) {
                    $cmd.= "Object";
                }
                $ret = $this->$cmd();
                break;
        }
        
        $this->addHeaderAction();
    }

    protected function initCreationForms($a_new_type)
    {
        $forms = array(self::CFORM_NEW => $this->initCreateForm($a_new_type),
            self::CFORM_IMPORT => $this->initImportForm($a_new_type));

        return $forms;
    }

    /**
    * cancel action and go back to previous page
    * @access	public
    *
    */
    final public function cancelCreationObject($in_rep = false)
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->redirectByClass("ilrepositorygui", "frameset");
    }

    /**
    * edit properties of object (admin form)
    *
    * @access	public
    */
    public function properties()
    {
        $rbacsystem = $this->rbacsystem;
        $tree = $this->tree;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        
        $ilTabs->activateTab("id_settings");

        $this->initSettingsForm();
        $this->getSettingsFormValues();
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Init settings form.
     */
    public function initSettingsForm()
    {
        $obj_service = $this->getObjectService();
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();

        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $ti->setSize(min(40, ilObject::TITLE_LENGTH));
        $ti->setMaxLength(ilObject::TITLE_LENGTH);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // description
        $ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
        $ta->setCols(40);
        $ta->setRows(2);
        $this->form->addItem($ta);

        // online
        $cb = new ilCheckboxInputGUI($lng->txt("cont_online"), "cobj_online");
        $cb->setOptionTitle($lng->txt(""));
        $cb->setValue("y");
        $this->form->addItem($cb);

        // startfile
        require_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMAccess.php");
        $startfile = ilObjFileBasedLMAccess::_determineStartUrl($this->object->getId());

        $ne = new ilNonEditableValueGUI($lng->txt("cont_startfile"), "");
        if ($startfile != "") {
            $ne->setValue(basename($startfile));
        } else {
            $ne->setValue(basename($this->lng->txt("no_start_file")));
        }
        $this->form->addItem($ne);

        $pres = new ilFormSectionHeaderGUI();
        $pres->setTitle($this->lng->txt('obj_presentation'));
        $this->form->addItem($pres);

        // tile image
        $obj_service->commonSettings()->legacyForm($this->form, $this->object)->addTileImage();

        $this->form->addCommandButton("saveProperties", $lng->txt("save"));
        $this->form->addCommandButton("toFilesystem", $lng->txt("cont_set_start_file"));

        $this->form->setTitle($lng->txt("cont_lm_properties"));
        $this->form->setFormAction($ilCtrl->getFormAction($this, "saveProperties"));
    }

    /**
     * Get current values for settings from
     */
    public function getSettingsFormValues()
    {
        require_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMAccess.php");
        $startfile = ilObjFileBasedLMAccess::_determineStartUrl($this->object->getId());

        $values = array();
        $values['cobj_online'] = !$this->object->getOfflineStatus();
        if ($startfile != "") {
            $startfile = basename($startfile);
        } else {
            $startfile = $this->lng->txt("no_start_file");
        }

        $values["startfile"] = $startfile;
        $values["title"] = $this->object->getTitle();
        $values["desc"] = $this->object->getDescription();
        //$values["lic"] = $this->object->getShowLicense();

        $this->form->setValuesByArray($values);
    }

    /**
     * Set start file
     *
     * @param
     * @return
     */
    public function toFilesystem()
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->redirectByClass("ilfilesystemgui", "listFiles");
    }

    /**
     * Save properties form
     */
    public function saveProperties()
    {
        $tpl = $this->tpl;
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;
        $obj_service = $this->getObjectService();
        
        $this->initSettingsForm("");
        if ($this->form->checkInput()) {
            $this->object->setTitle($this->form->getInput("title"));
            $this->object->setDescription($this->form->getInput("desc"));
            $this->object->setOfflineStatus(!(bool) $_POST['cobj_online']);

            $this->object->update();

            // tile image
            $obj_service->commonSettings()->legacyForm($this->form, $this->object)->saveTileImage();

            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "properties");
        }

        $ilTabs->activateTab("id_settings");
        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHtml());
    }

    /**
    * edit properties of object (admin form)
    *
    * @access	public
    */
    public function editObject()
    {
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;

        if (!$rbacsystem->checkAccess("visible,write", $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }
    }

    /**
    * edit properties of object (module form)
    */
    public function edit()
    {
        $this->prepareOutput();
        $this->editObject();
    }

    /**
    * cancel editing
    */
    public function cancel()
    {
        //$this->setReturnLocation("cancel","fblm_edit.php?cmd=listFiles&ref_id=".$_GET["ref_id"]);
        $this->cancelObject();
    }
    
    /**
    * save object
    * @access	public
    */
    public function afterSave(ilObject $newObj)
    {
        if (!$newObj->getStartFile()) {
            // try to set start file automatically
            $files = array();
            include_once "Services/Utilities/classes/class.ilFileUtils.php";
            ilFileUtils::recursive_dirscan($newObj->getDataDirectory(), $files);
            if (is_array($files["file"])) {
                $zip_file = null;
                if (stristr($newObj->getTitle(), ".zip")) {
                    $zip_file = strtolower($newObj->getTitle());
                    $suffix = strrpos($zip_file, ".");
                    if ($suffix) {
                        $zip_file = substr($zip_file, 0, $suffix);
                    }
                }
                $valid = array("index.htm", "index.html", "start.htm", "start.html");
                foreach ($files["file"] as $idx => $file) {
                    $chk_file = null;
                    if (stristr($file, ".htm")) {
                        $chk_file = strtolower($file);
                        $suffix = strrpos($chk_file, ".");
                        if ($suffix) {
                            $chk_file = substr($chk_file, 0, $suffix);
                        }
                    }
                    if (in_array($file, $valid) ||
                        ($chk_file && $zip_file && $chk_file == $zip_file)) {
                        $newObj->setStartFile(str_replace($newObj->getDataDirectory() . "/", "", $files["path"][$idx]) . $file);
                        $newObj->update();
                        break;
                    }
                }
            }
        }
        
        // always send a message
        ilUtil::sendSuccess($this->lng->txt("object_added"), true);
        $this->object = $newObj;
        $this->redirectAfterCreation();
    }
    

    /**
    * update properties
    */
    public function update()
    {
        //$this->setReturnLocation("update", "fblm_edit.php?cmd=listFiles&ref_id=".$_GET["ref_id"].
        //	"&obj_id=".$_GET["obj_id"]);
        $this->updateObject();
    }


    public function setStartFile($a_file)
    {
        $this->object->setStartFile($a_file);
        $this->object->update();
        $this->ctrl->redirectByClass("ilfilesystemgui", "listFiles");
    }

    /**
    * permission form
    */
    public function perm()
    {
        $this->setFormAction("permSave", "fblm_edit.php?cmd=permSave&ref_id=" . $_GET["ref_id"] .
            "&obj_id=" . $_GET["obj_id"]);
        $this->setFormAction("addRole", "fblm_edit.php?ref_id=" . $_GET["ref_id"] .
            "&obj_id=" . $_GET["obj_id"] . "&cmd=addRole");
        $this->permObject();
    }
    

    /**
    * Frameset -> Output list of files
    */
    public function frameset()
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setCmdClass("ilfilesystemgui");
        $ilCtrl->setCmd("listFiles");
        return $this->executeCommand();
    }

    /**
    * output main header (title and locator)
    */
    public function getTemplate()
    {
        $lng = $this->lng;

        $this->tpl->getStandardTemplate();
    }

    public function showLearningModule()
    {
        $ilUser = $this->user;

        // #9483
        if ($ilUser->getId() != ANONYMOUS_USER_ID) {
            include_once "Services/Tracking/classes/class.ilLearningProgress.php";
            ilLearningProgress::_tracProgress(
                $ilUser->getId(),
                $this->object->getId(),
                $this->object->getRefId(),
                "htlm"
            );
        }

        require_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMAccess.php");
        require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');

        $startfile = ilObjFileBasedLMAccess::_determineStartUrl($this->object->getId());
        ilWACSignedPath::signFolderOfStartFile($startfile);
        if ($startfile != "") {
            ilUtil::redirect($startfile);
        }
    }

    // InfoScreen methods
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

    /**
    * info screen call from inside learning module
    */
    public function showInfoScreen()
    {
        $this->outputInfoScreen(true);
    }

    /**
    * info screen
    */
    public function outputInfoScreen($a_standard_locator = true)
    {
        $ilToolbar = $this->toolbar;
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;

        $ilTabs->activateTab('id_info');
        
        $this->lng->loadLanguageModule("meta");
        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");

        $info = new ilInfoScreenGUI($this);
        $info->enablePrivateNotes();
        $info->enableLearningProgress();
        
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
            // #15127
            include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
            $button = ilLinkButton::getInstance();
            $button->setCaption("view");
            $button->setPrimary(true);
            $button->setUrl("ilias.php?baseClass=ilHTLMPresentationGUI&ref_id=" . $this->object->getRefID());
            $button->setTarget("ilContObj" . $this->object->getId());
            $ilToolbar->addButtonInstance($button);
        }
        
        // show standard meta data section
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

        // forward the command
        $this->ctrl->forwardCommand($info);
    }



    /**
    * output tabs
    */
    public function setTabs()
    {
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lm.svg"));
        
        $this->getTabs();
        $this->tpl->setTitle($this->object->getTitle());
    }

    /**
    * adds tabs to tab gui object
    */
    public function getTabs()
    {
        $ilUser = $this->user;
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilHelp = $this->help;

        $ilHelp->setScreenIdComponent("htlm");
        
        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
            $ilTabs->addTab(
                "id_list_files",
                $lng->txt("cont_list_files"),
                $this->ctrl->getLinkTargetByClass("ilfilesystemgui", "listFiles")
            );
        }

        if ($ilAccess->checkAccess('visible', '', $this->ref_id)) {
            $ilTabs->addTab(
                "id_info",
                $lng->txt("info_short"),
                $this->ctrl->getLinkTargetByClass(array("ilobjfilebasedlmgui", "ilinfoscreengui"), "showSummary")
            );
        }

        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
            $ilTabs->addTab(
                "id_settings",
                $lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "properties")
            );
        }
        
        include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
        if (ilLearningProgressAccess::checkAccess($this->object->getRefId())) {
            $ilTabs->addTab(
                "id_learning_progress",
                $lng->txt("learning_progress"),
                $this->ctrl->getLinkTargetByClass(array('ilobjfilebasedlmgui','illearningprogressgui'), '')
            );
        }

        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
            include_once "Services/Object/classes/class.ilObjectMetaDataGUI.php";
            $mdgui = new ilObjectMetaDataGUI($this->object);
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $ilTabs->addTab(
                    "id_meta_data",
                    $lng->txt("meta_data"),
                    $mdtab
                );
            }
        }


        // export
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $ilTabs->addTab(
                "export",
                $lng->txt("export"),
                $this->ctrl->getLinkTargetByClass("ilexportgui", "")
            );
        }

        if ($ilAccess->checkAccess('edit_permission', '', $this->object->getRefId())) {
            $ilTabs->addTab(
                "id_permissions",
                $lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm")
            );
        }

        require_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMAccess.php");
        $startfile = ilObjFileBasedLMAccess::_determineStartUrl($this->object->getId());

        if ($startfile != "") {
            $ilTabs->addNonTabbedLink(
                "presentation_view",
                $this->lng->txt("glo_presentation_view"),
                "ilias.php?baseClass=ilHTLMPresentationGUI&ref_id=" . $this->object->getRefID(),
                "_blank"
            );
        }
    }
    
    /**
    * redirect script
    *
    * @param	string		$a_target
    */
    public static function _goto($a_target)
    {
        global $DIC;

        $rbacsystem = $DIC->rbac()->system();
        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();
        $ilAccess = $DIC->access();

        if ($ilAccess->checkAccess("read", "", $a_target) ||
            $ilAccess->checkAccess("visible", "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreen");
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            ilUtil::sendFailure(sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }

        $ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
    }

    public function addLocatorItems()
    {
        $ilLocator = $this->locator;
        
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
     * Import file
     *
     * @param
     * @return
     */
    public function importFileObject($parent_id = null, $a_catch_errors = true)
    {
        try {
            return parent::importFileObject();
        } catch (ilManifestFileNotFoundImportException $e) {
            // since there is no manifest xml we assume that this is an HTML export file
            $this->createFromDirectory($e->getTmpDir());
        }
    }
    
    /**
     * Create new object from a html zip file
     *
     * @param
     * @return
     */
    public function createFromDirectory($a_dir)
    {
        $ilErr = $this->error;
        
        if (!$this->checkPermissionBool("create", "", "htlm") || $a_dir == "") {
            $ilErr->raiseError($this->lng->txt("no_create_permission"));
        }
        
        // create instance
        $newObj = new ilObjFileBasedLM();
        $filename = ilUtil::stripSlashes($_FILES["importfile"]["name"]);
        $newObj->setTitle($filename);
        $newObj->setDescription("");
        $newObj->create();
        $newObj->populateByDirectoy($a_dir, $filename);
        $this->putObjectInTree($newObj);

        $this->afterSave($newObj);
    }
    
    
    
    
    ////
    //// Export to HTML
    ////


    /**
     * create html package
     */
    public function exportHTML()
    {
        $inst_id = IL_INST_ID;

        include_once("./Services/Export/classes/class.ilExport.php");
        
        ilExport::_createExportDirectory(
            $this->object->getId(),
            "html",
            $this->object->getType()
        );
        $export_dir = ilExport::_getExportDirectory(
            $this->object->getId(),
            "html",
            $this->object->getType()
        );
        
        $subdir = $this->object->getType() . "_" . $this->object->getId();
        $filename = $this->subdir . ".zip";

        $target_dir = $export_dir . "/" . $subdir;

        ilUtil::delDir($target_dir);
        ilUtil::makeDir($target_dir);

        $source_dir = $this->object->getDataDirectory();

        ilUtil::rCopy($source_dir, $target_dir);

        // zip it all
        $date = time();
        $zip_file = $export_dir . "/" . $date . "__" . IL_INST_ID . "__" .
            $this->object->getType() . "_" . $this->object->getId() . ".zip";
        ilUtil::zip($target_dir, $zip_file);

        ilUtil::delDir($target_dir);
    }

    /**
     * @inheritdoc
     */
    public function redirectAfterCreation()
    {
        $ctrl = $this->ctrl;
        $ctrl->setParameterByClass("ilObjFileBasedLMGUI", "ref_id", $this->object->getRefId());
        $ctrl->redirectByClass(["ilrepositorygui", "ilObjFileBasedLMGUI"], "properties");
    }
}
