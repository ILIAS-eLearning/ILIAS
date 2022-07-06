<?php

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

use ILIAS\HTMLLearningModule\StandardGUIRequest;

/**
 * User Interface class for file based learning modules (HTML)
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjFileBasedLMGUI: ilFileSystemGUI, ilObjectMetaDataGUI, ilPermissionGUI, ilLearningProgressGUI, ilInfoScreenGUI
 * @ilCtrl_Calls ilObjFileBasedLMGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjFileBasedLMGUI: ilExportGUI
 */
class ilObjFileBasedLMGUI extends ilObjectGUI
{
    protected StandardGUIRequest $lm_request;
    protected ilPropertyFormGUI $form;
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;
    public bool $output_prepared;

    public function __construct(
        $a_data,
        int $a_id = 0,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->locator = $DIC["ilLocator"];
        $this->tabs = $DIC->tabs();
        $this->tree = $DIC->repositoryTree();
        $this->tpl = $DIC["tpl"];
        $this->access = $DIC->access();
        $this->toolbar = $DIC->toolbar();
        $this->help = $DIC["ilHelp"];
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->ctrl = $ilCtrl;
        $this->ctrl->saveParameter($this, array("ref_id"));

        $this->lm_request = $DIC->htmlLearningModule()
            ->internal()
            ->gui()
            ->standardRequest();

        $this->type = "htlm";
        $lng->loadLanguageModule("content");
        $lng->loadLanguageModule("obj");

        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        $this->output_prepared = $a_prepare_output;
    }
    
    public function executeCommand() : void
    {
        $ilUser = $this->user;
        $ilTabs = $this->tabs;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
    
        if (
            $this->getCreationMode() === true ||
            strtolower($this->lm_request->getBaseClass()) === "iladministrationgui"
        ) {
            $this->prepareOutput();
        } elseif (!in_array($cmd, array("", "framset")) || $next_class != "") {
            $this->getTemplate();
            $this->setLocator();
            $this->setTabs();
        }

        switch ($next_class) {
            case 'ilobjectmetadatagui':
                $this->checkPermission("write");
                $ilTabs->activateTab('id_meta_data');
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
                if ($this->object->getStartFile() !== "") {
                    $fs_gui->labelFile(
                        (string) $this->object->getStartFile(),
                        $this->lng->txt("cont_startfile")
                    );
                }
                $fs_gui->addCommand($this, "setStartFile", $this->lng->txt("cont_set_start_file"));
                
                $this->ctrl->forwardCommand($fs_gui);
                                            
                // try to set start file automatically
                /* this does not work anymore, see #33348
                if (!ilObjFileBasedLMAccess::_determineStartUrl($this->object->getId())) {
                    $do_update = false;

                    $pcommand = $fs_gui->getLastPerformedCommand();
                    $last_cmd = $pcommand["cmd"] ?? "";
                    $valid = array("index.htm", "index.html", "start.htm", "start.html");
                    if ($last_cmd === "create_file") {
                        $file = strtolower(basename($pcommand["name"]));
                        if (in_array($file, $valid)) {
                            $this->object->setStartFile($pcommand["name"]);
                            $do_update = $pcommand["name"];
                        }
                    } elseif ($last_cmd === "unzip_file") {
                        $zip_file = strtolower(basename($pcommand["name"]));
                        $suffix = strrpos($zip_file, ".");
                        if ($suffix) {
                            $zip_file = substr($zip_file, 0, $suffix);
                        }
                        foreach ($pcommand["added"] as $file) {
                            $chk_file = null;
                            if (stripos($file, ".htm") !== false) {
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

                    if ($do_update) {
                        $this->tpl->setOnScreenMessage('info', sprintf($this->lng->txt("cont_start_file_set_to"), $do_update), true);

                        $this->object->update();
                        $this->ctrl->redirectByClass("ilfilesystemgui", "listFiles");
                    }
                }*/
                break;

            case "ilinfoscreengui":
                $this->showInfoScreen();
                break;

            case "illearningprogressgui":
                $ilTabs->activateTab('id_learning_progress');
                $user_id = ($this->lm_request->getUserId() > 0)
                    ? $this->lm_request->getUserId()
                    : $ilUser->getId();
                $new_gui = new ilLearningProgressGUI(
                    ilLearningProgressBaseGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $user_id
                );
                $this->ctrl->forwardCommand($new_gui);
                break;
                
            case 'ilpermissiongui':
                $ilTabs->activateTab('id_permissions');
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilexportgui":
                $ilTabs->activateTab("export");
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                $exp_gui->addFormat("html", "", $this, "exportHTML");
                $ret = $this->ctrl->forwardCommand($exp_gui);
                break;

            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
            
            default:
                $cmd = $this->ctrl->getCmd("listFiles");
                if (
                    $this->getCreationMode() === true ||
                    strtolower($this->lm_request->getBaseClass()) === "iladministrationgui"
                ) {
                    $cmd .= "Object";
                }
                $ret = $this->$cmd();
                break;
        }
        
        $this->addHeaderAction();
    }

    protected function initCreationForms(string $new_type) : array
    {
        return [
            self::CFORM_NEW => $this->initCreateForm($new_type),
            self::CFORM_IMPORT => $this->initImportForm($new_type)
        ];
    }

    final public function cancelCreationObject() : void
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->redirectByClass("ilrepositorygui", "frameset");
    }

    public function properties() : void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        
        $ilTabs->activateTab("id_settings");

        $this->initSettingsForm();
        $this->getSettingsFormValues();
        $tpl->setContent($this->form->getHTML());
    }

    public function initSettingsForm() : void
    {
        $obj_service = $this->getObjectService();
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

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
        $startfile = ilObjFileBasedLMAccess::_determineStartUrl($this->object->getId());

        $ne = new ilNonEditableValueGUI($lng->txt("cont_startfile"), "");
        if ($startfile !== "") {
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

        // additional features
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('obj_features'));
        $this->form->addItem($section);

        ilObjectServiceSettingsGUI::initServiceSettingsForm(
            $this->object->getId(),
            $this->form,
            [
                ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY
            ]
        );
    }

    public function getSettingsFormValues() : void
    {
        $startfile = ilObjFileBasedLMAccess::_determineStartUrl($this->object->getId());

        $values = array();
        $values['cobj_online'] = !$this->object->getOfflineStatus();
        if ($startfile !== "") {
            $startfile = basename($startfile);
        } else {
            $startfile = $this->lng->txt("no_start_file");
        }

        $values["startfile"] = $startfile;
        $values["title"] = $this->object->getTitle();
        $values["desc"] = $this->object->getLongDescription();
        $values["cont_show_info_tab"] = $this->object->isInfoEnabled();

        $this->form->setValuesByArray($values);
    }

    public function toFilesystem() : void
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->redirectByClass("ilfilesystemgui", "listFiles");
    }

    public function saveProperties() : void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $obj_service = $this->getObjectService();
        
        $this->initSettingsForm();
        if ($this->form->checkInput()) {
            $this->object->setTitle($this->form->getInput("title"));
            $this->object->setDescription($this->form->getInput("desc"));
            $this->object->setOfflineStatus(!(bool) $this->form->getInput("cobj_online"));

            $this->object->update();

            // tile image
            $obj_service->commonSettings()->legacyForm($this->form, $this->object)->saveTileImage();

            // services
            ilObjectServiceSettingsGUI::updateServiceSettingsForm(
                $this->object->getId(),
                $this->form,
                array(
                    ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY
                )
            );

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "properties");
        }

        $ilTabs->activateTab("id_settings");
        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHTML());
    }

    public function editObject() : void
    {
        if (!$this->rbac_system->checkAccess("visible,write", $this->object->getRefId())) {
            throw new ilPermissionException($this->lng->txt("permission_denied"));
        }
    }

    public function edit() : void
    {
        $this->prepareOutput();
        $this->editObject();
    }

    public function cancel() : void
    {
        $this->cancelObject();
    }
    
    protected function afterSave(ilObject $new_object) : void
    {
        if (!$new_object->getStartFile()) {
            // try to set start file automatically
            $files = array();
            ilFileUtils::recursive_dirscan($new_object->getDataDirectory(), $files);
            if (isset($files["file"])) {
                $zip_file = null;
                if (stripos($new_object->getTitle(), ".zip") !== false) {
                    $zip_file = strtolower($new_object->getTitle());
                    $suffix = strrpos($zip_file, ".");
                    if ($suffix) {
                        $zip_file = substr($zip_file, 0, $suffix);
                    }
                }
                $valid = array("index.htm", "index.html", "start.htm", "start.html");
                foreach ($files["file"] as $idx => $file) {
                    $chk_file = null;
                    if (stripos($file, ".htm") !== false) {
                        $chk_file = strtolower($file);
                        $suffix = strrpos($chk_file, ".");
                        if ($suffix) {
                            $chk_file = substr($chk_file, 0, $suffix);
                        }
                    }
                    if (in_array($file, $valid) ||
                        ($chk_file && $zip_file && $chk_file == $zip_file)) {
                        $new_object->setStartFile(str_replace($new_object->getDataDirectory() . "/", "", $files["path"][$idx]) . $file);
                        $new_object->update();
                        break;
                    }
                }
            }
        }
        
        // always send a message
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);
        $this->object = $new_object;
        $this->redirectAfterCreation();
    }

    public function update() : void
    {
        $this->updateObject();
    }

    public function setStartFile(string $a_file) : void
    {
        $this->object->setStartFile($a_file);
        $this->object->update();
        $this->ctrl->redirectByClass("ilfilesystemgui", "listFiles");
    }

    public function getTemplate() : void
    {
        $this->tpl->loadStandardTemplate();
    }

    public function showLearningModule() : void
    {
        $ilUser = $this->user;

        // #9483
        if ($ilUser->getId() !== ANONYMOUS_USER_ID) {
            ilLearningProgress::_tracProgress(
                $ilUser->getId(),
                $this->object->getId(),
                $this->object->getRefId(),
                "htlm"
            );

            ilLPStatusWrapper::_updateStatus($this->object->getId(), $ilUser->getId());
        }

        $startfile = ilObjFileBasedLMAccess::_determineStartUrl($this->object->getId());
        ilWACSignedPath::signFolderOfStartFile($startfile);
        if ($startfile !== "") {
            ilUtil::redirect($startfile);
        }
    }

    /**
     * this one is called from the info button in the repository
     */
    public function infoScreen() : void
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->showInfoScreen();
    }

    public function showInfoScreen() : void
    {
        $ilToolbar = $this->toolbar;
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;

        $ilTabs->activateTab('id_info');
        
        $this->lng->loadLanguageModule("meta");

        $info = new ilInfoScreenGUI($this);
        $info->enablePrivateNotes();
        $info->enableLearningProgress();
        
        $info->enableNews();
        if ($ilAccess->checkAccess("write", "", $this->requested_ref_id)) {
            $info->enableNewsEditing();
            
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");
            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", true);
            }
        }

        // add read / back button
        if ($ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
            // #15127
            $button = ilLinkButton::getInstance();
            $button->setCaption("view");
            $button->setPrimary(true);
            $button->setUrl("ilias.php?baseClass=ilHTLMPresentationGUI&ref_id=" . $this->object->getRefId());
            $button->setTarget("ilContObj" . $this->object->getId());
            $ilToolbar->addButtonInstance($button);
        }
        
        // show standard meta data section
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

        // forward the command
        $this->ctrl->forwardCommand($info);
    }

    protected function setTabs() : void
    {
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lm.svg"));
        $this->getTabs();
        $this->tpl->setTitle($this->object->getTitle());
    }

    protected function getTabs() : void
    {
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

        if ($ilAccess->checkAccess('visible', '', $this->ref_id) && $this->object->isInfoEnabled()) {
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
        
        if (ilLearningProgressAccess::checkAccess($this->object->getRefId())) {
            $ilTabs->addTab(
                "id_learning_progress",
                $lng->txt("learning_progress"),
                $this->ctrl->getLinkTargetByClass(array('ilobjfilebasedlmgui','illearningprogressgui'), '')
            );
        }

        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
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

        $startfile = ilObjFileBasedLMAccess::_determineStartUrl($this->object->getId());
        if ($startfile !== "" && $ilAccess->checkAccess('read', '', $this->ref_id)) {
            $ilTabs->addNonTabbedLink(
                "presentation_view",
                $this->lng->txt("glo_presentation_view"),
                "ilias.php?baseClass=ilHTLMPresentationGUI&ref_id=" . $this->object->getRefId(),
                "_blank"
            );
        }
    }
    
    public static function _goto(string $a_target) : void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $lng = $DIC->language();
        $ilAccess = $DIC->access();

        if ($ilAccess->checkAccess("read", "", $a_target) ||
            $ilAccess->checkAccess("visible", "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreen");
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('failure', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }

        throw new ilPermissionException($lng->txt("msg_no_perm_read_lm"));
    }

    protected function addLocatorItems() : void
    {
        $ilLocator = $this->locator;
        
        if (is_object($this->object)) {
            $ilLocator->addItem(
                $this->object->getTitle(),
                $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"),
                "",
                $this->requested_ref_id
            );
        }
    }

    protected function importFileObject(int $parent_id = null, bool $catch_errors = true) : void
    {
        try {
            parent::importFileObject();
        } catch (ilManifestFileNotFoundImportException $e) {
            // since there is no manifest xml we assume that this is an HTML export file
            $this->createFromDirectory($e->getTmpDir());
        }
    }

    protected function afterImport(ilObject $new_object) : void
    {
        $this->ctrl->setParameter($this, "ref_id", $new_object->getRefId());
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);
        $this->ctrl->redirect($this, "properties");
    }

    public function createFromDirectory(string $a_dir) : void
    {
        if ($a_dir === "" || !$this->checkPermissionBool("create", "", "htlm")) {
            throw new ilPermissionException($this->lng->txt("no_create_permission"));
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

    public function exportHTML() : void
    {
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

        $target_dir = $export_dir . "/" . $subdir;

        ilFileUtils::delDir($target_dir);
        ilFileUtils::makeDir($target_dir);

        $source_dir = $this->object->getDataDirectory();

        ilFileUtils::rCopy($source_dir, $target_dir);

        // zip it all
        $date = time();
        $zip_file = $export_dir . "/" . $date . "__" . IL_INST_ID . "__" .
            $this->object->getType() . "_" . $this->object->getId() . ".zip";
        ilFileUtils::zip($target_dir, $zip_file);

        ilFileUtils::delDir($target_dir);
    }

    public function redirectAfterCreation() : void
    {
        $ctrl = $this->ctrl;
        $ctrl->setParameterByClass("ilObjFileBasedLMGUI", "ref_id", $this->object->getRefId());
        $ctrl->redirectByClass(["ilrepositorygui", "ilObjFileBasedLMGUI"], "properties");
    }


    public function learningProgress() : void
    {
        $this->ctrl->redirectByClass("illearningprogressgui", "");
    }

    public function redrawHeaderAction() : void
    {
        $this->redrawHeaderActionObject();
    }
}
