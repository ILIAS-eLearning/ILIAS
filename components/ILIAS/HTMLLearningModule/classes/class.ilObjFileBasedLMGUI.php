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
use ILIAS\ResourceStorage\Resource\StorableContainerResource;
use ILIAS\components\ResourceStorage\Container\View\Configuration;
use ILIAS\components\ResourceStorage\Container\View\Mode;
use ILIAS\components\ResourceStorage\Container\View\ActionBuilder;

/**
 * User Interface class for file based learning modules (HTML)
 * @author       Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjFileBasedLMGUI: ilObjectMetaDataGUI, ilPermissionGUI, ilLearningProgressGUI, ilInfoScreenGUI
 * @ilCtrl_Calls ilObjFileBasedLMGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjFileBasedLMGUI: ilExportGUI
 * @ilCtrl_Calls ilObjFileBasedLMGUI: ilContainerResourceGUI
 */
class ilObjFileBasedLMGUI extends ilObjectGUI
{
    private const PARAM_PATH = "path";
    public const CMD_LIST_FILES = "listFiles";
    private \ILIAS\ResourceStorage\Services $irss;
    private \ILIAS\HTTP\Services $http;
    protected \ILIAS\HTMLLearningModule\InternalGUIService $gui;
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

        $this->irss = $DIC->resourceStorage();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->locator = $DIC["ilLocator"];
        $this->tabs = $DIC->tabs();
        $this->tree = $DIC->repositoryTree();
        $this->tpl = $DIC["tpl"];
        $this->access = $DIC->access();
        $this->toolbar = $DIC->toolbar();
        $this->help = $DIC["ilHelp"];
        $this->http = $DIC->http();
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
        $this->gui = $DIC->htmlLearningModule()->internal()->gui();
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if (
            $this->getCreationMode() === true ||
            strtolower($this->lm_request->getBaseClass()) === "iladministrationgui"
        ) {
            $this->prepareOutput();
        } elseif (!in_array($cmd, array("", "framset")) || $next_class != "") {
            $this->tpl->loadStandardTemplate();
            $this->setLocator();
            $this->setTabs();
        }

        switch ($next_class) {
            case strtolower(ilContainerResourceGUI::class):
                $this->tabs->activateTab('id_list_files');
                // Check wite access to determine upload and manage capabilities
                $check_access = $this->access->checkAccess('write', '', $this->object->getRefId());

                // Build the view configuration
                $view_configuration = new Configuration(
                    $this->object->getResource(),
                    new ilHTLMStakeholder(),
                    $this->lng->txt('files'),
                    Mode::DATA_TABLE,
                    250,
                    $check_access,
                    $check_access
                );

                // Add a single action for text-files to set as startfile
                $view_configuration = $view_configuration->withExternalAction(
                    $this->lng->txt('cont_set_start_file'),
                    self::class,
                    'setStartFile',
                    'lm',
                    self::PARAM_PATH,
                    false,
                    ['text/*']
                );

                // build the collection GUI
                $container_gui = new ilContainerResourceGUI(
                    $view_configuration
                );

                // forward the command
                $this->ctrl->forwardCommand($container_gui);
                break;
            case 'ilobjectmetadatagui':
                $this->checkPermission("write");
                $this->tabs->activateTab('id_meta_data');
                $md_gui = new ilObjectMetaDataGUI($this->object);
                $this->ctrl->forwardCommand($md_gui);
                break;

            case "ilinfoscreengui":
                $this->showInfoScreen();
                break;

            case "illearningprogressgui":
                $this->tabs->activateTab('id_learning_progress');
                $user_id = ($this->lm_request->getUserId() > 0)
                    ? $this->lm_request->getUserId()
                    : $this->user->getId();
                $new_gui = new ilLearningProgressGUI(
                    ilLearningProgressBaseGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $user_id
                );
                $this->ctrl->forwardCommand($new_gui);
                break;

            case 'ilpermissiongui':
                $this->tabs->activateTab('id_permissions');
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilexportgui":
                $this->tabs->activateTab("export");
                $exp_gui = new ilExportGUI($this);
                $ret = $this->ctrl->forwardCommand($exp_gui);
                break;

            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $cmd = $this->ctrl->getCmd(self::CMD_LIST_FILES);
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

    final public function cancelCreationObject(): void
    {
        $this->ctrl->redirectByClass("ilrepositorygui", "frameset");
    }

    public function properties(): void
    {
        $this->tabs->activateTab("id_settings");

        $this->initSettingsForm();
        $this->getSettingsFormValues();
        $this->tpl->setContent($this->form->getHTML());
    }

    public function initSettingsForm(): void
    {
        $obj_service = $this->getObjectService();

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
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_online"), "cobj_online");
        $cb->setOptionTitle($this->lng->txt(""));
        $cb->setValue("y");
        $this->form->addItem($cb);

        // startfile
        $startfile = ilObjFileBasedLMAccess::_determineStartUrl($this->object->getId());

        $ne = new ilNonEditableValueGUI($this->lng->txt("cont_startfile"), "");
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

        $this->form->addCommandButton("saveProperties", $this->lng->txt("save"));
        $this->form->addCommandButton("toFilesystem", $this->lng->txt("cont_set_start_file"));

        $this->form->setTitle($this->lng->txt("cont_lm_properties"));
        $this->form->setFormAction($this->ctrl->getFormAction($this, "saveProperties"));

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

    public function getSettingsFormValues(): void
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

    public function toFilesystem(): void
    {
        // If we already have a RID, we can redirect to Container GUI
        // otherwise we display an message which informs the user that the resource is not yet available
        if ($this->object->getRID() != "") {
            $this->ctrl->redirectByClass(ilContainerResourceGUI::class);
        } else {
            $this->ctrl->redirectByClass(static::class, "properties");
        }
    }

    public function saveProperties(): void
    {
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

        $this->tabs->activateTab("id_settings");
        $this->form->setValuesByPost();
        $this->tpl->setContent($this->form->getHTML());
    }

    public function editObject(): void
    {
        if (!$this->rbac_system->checkAccess("visible,write", $this->object->getRefId())) {
            throw new ilPermissionException($this->lng->txt("permission_denied"));
        }
    }

    public function edit(): void
    {
        $this->prepareOutput();
        $this->editObject();
    }

    public function cancel(): void
    {
        $this->cancelObject();
    }

    protected function afterSave(ilObject $new_object): void
    {
        if (!$new_object->getStartFile()) {
            $new_object->maybeDetermineStartFile();
        }

        // always send a message
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);
        $this->object = $new_object;
        $this->redirectAfterCreation();
    }

    public function update(): void
    {
        $this->updateObject();
    }

    public function setStartFile(): void
    {
        // try to determine start file from request
        $start_file = $this->http->wrapper()->query()->has('lm_path')
            ? $start_file = $this->http->wrapper()->query()->retrieve(
                'lm_path',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            )[0] ?? ''
            : '';
        // the ContainerResourceGUI uses e bin2hex/hex2bin serialization of pathes. Due to the internals of
        // UI\Table\Data it's not possible to have a different handling for the parameter in case of external actions...
        try {
            $start_file = hex2bin($start_file);
        } catch (Throwable $e) {
            $start_file = '';
        }

        if ($start_file === '') {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('cont_no_start_file'), true);
        } else {
            $this->object->setStartFile($start_file);
            $this->object->update();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('cont_start_file_set'), true);
        }

        $this->ctrl->redirectByClass(ilContainerResourceGUI::class);
    }

    public function showLearningModule(): void
    {
        // #9483
        if ($this->user->getId() !== ANONYMOUS_USER_ID) {
            ilLearningProgress::_tracProgress(
                $this->user->getId(),
                $this->object->getId(),
                $this->object->getRefId(),
                "htlm"
            );

            ilLPStatusWrapper::_updateStatus($this->object->getId(), $this->user->getId());
        }

        /** @var StorableContainerResource $resource */
        $resource = $this->object->getResource();

        if ($resource !== null) {
            $startfile = $this->object->getStartFile() ?? 'index.html';
            $uri = $this->irss->consume()->containerURI(
                $resource->getIdentification(),
                $startfile,
                8 * 60
            )->getURI();

            ilUtil::redirect((string) $uri);
        } else {
            // This is a legacy learning module which has not yet been migrated to the new resource storage
            $startfile = ilObjFileBasedLMAccess::_determineStartUrl($this->object->getId());

            ilWACSignedPath::signFolderOfStartFile($startfile);
            if ($startfile !== "") {
                ilUtil::redirect($startfile);
            }
        }
    }

    /**
     * this one is called from the info button in the repository
     */
    public function infoScreen(): void
    {
        $this->ctrl->redirectByClass(ilInfoScreenGUI::class, "showSummary");
    }

    public function showInfoScreen(): void
    {
        $this->tabs->activateTab('id_info');

        $this->lng->loadLanguageModule("meta");

        $info = new ilInfoScreenGUI($this);
        $info->enablePrivateNotes();
        $info->enableLearningProgress();

        $info->enableNews();
        if ($this->access->checkAccess("write", "", $this->requested_ref_id)) {
            $info->enableNewsEditing();

            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");
            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", true);
            }
        }

        // add read / back button
        if ($this->access->checkAccess("read", "", $this->requested_ref_id)) {
            // #15127
            $this->gui->link(
                $this->lng->txt("view"),
                "ilias.php?baseClass=ilHTLMPresentationGUI&ref_id=" . $this->object->getRefId(),
                true
            )->primary()->toToolbar();
        }

        // show standard meta data section
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

        // forward the command
        $this->ctrl->forwardCommand($info);
    }

    protected function setTabs(): void
    {
        $this->getTabs();
        $this->setTitleAndDescription();
    }

    protected function getTabs(): void
    {
        $this->access = $this->access;
        $this->tabs = $this->tabs;
        $lng = $this->lng;
        $ilHelp = $this->help;

        $ilHelp->setScreenIdComponent("htlm");

        if ($this->access->checkAccess('write', '', $this->ref_id)) {
            // Depending on whether the module has already been migrated to the IRSS, we add a tab to
            // ilContainerResourceGUI or internally. internally, it is only indicated that the files cannot be edited.
            $this->tabs->addTab(
                "id_list_files",
                $lng->txt("cont_list_files"),
                $this->ctrl->getLinkTarget($this, self::CMD_LIST_FILES)
            );
        }

        if ($this->access->checkAccess('visible', '', $this->ref_id) && $this->object->isInfoEnabled()) {
            $this->tabs->addTab(
                "id_info",
                $lng->txt("info_short"),
                $this->ctrl->getLinkTargetByClass([self::class, ilInfoScreenGUI::class], "showSummary")
            );
        }

        if ($this->access->checkAccess('write', '', $this->ref_id)) {
            $this->tabs->addTab(
                "id_settings",
                $lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "properties")
            );
        }

        if (ilLearningProgressAccess::checkAccess($this->object->getRefId())) {
            $this->tabs->addTab(
                "id_learning_progress",
                $lng->txt("learning_progress"),
                $this->ctrl->getLinkTargetByClass([self::class, ilLearningProgressGUI::class], '')
            );
        }

        if ($this->access->checkAccess('write', '', $this->ref_id)) {
            $mdgui = new ilObjectMetaDataGUI($this->object);
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $this->tabs->addTab(
                    "id_meta_data",
                    $lng->txt("meta_data"),
                    $mdtab
                );
            }
        }

        // export
        if ($this->access->checkAccess("write", "", $this->object->getRefId())) {
            $this->tabs->addTab(
                "export",
                $lng->txt("export"),
                $this->ctrl->getLinkTargetByClass(ilExportGUI::class, "")
            );
        }

        if ($this->access->checkAccess('edit_permission', '', $this->object->getRefId())) {
            $this->tabs->addTab(
                "id_permissions",
                $lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass([self::class, ilPermissionGUI::class], "perm")
            );
        }

        $startfile = ilObjFileBasedLMAccess::_determineStartUrl($this->object->getId());
        if ($startfile !== "" && $this->access->checkAccess('read', '', $this->ref_id)) {
            $this->tabs->addNonTabbedLink(
                "presentation_view",
                $this->lng->txt("glo_presentation_view"),
                "ilias.php?baseClass=ilHTLMPresentationGUI&ref_id=" . $this->object->getRefId(),
                "_blank"
            );
        }
    }

    public static function _goto(string $a_target): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $lng = $DIC->language();
        $access = $DIC->access();

        if ($access->checkAccess("read", "", $a_target) ||
            $access->checkAccess("visible", "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreen");
        } elseif ($access->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage(
                'failure',
                sprintf(
                    $lng->txt("msg_no_perm_read_item"),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
                ),
                true
            );
            ilObjectGUI::_gotoRepositoryRoot();
        }

        throw new ilPermissionException($lng->txt("msg_no_perm_read_lm"));
    }

    protected function addLocatorItems(): void
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

    private function listFiles(): void
    {
        if ($this->object->getResource() !== null) {
            $this->ctrl->redirectByClass(ilContainerResourceGUI::class);
            return;
        }
        $this->tabs->activateTab("id_list_files");

        $message_box = $this->gui->ui()->factory()->messageBox()->info(
            $this->lng->txt("infobox_files_not_migrated")
        );

        $this->tpl->setContent(
            $this->gui->ui()->renderer()->render([$message_box])
        );
    }

    protected function importFileObject(?int $parent_id = null): void
    {
        try {
            parent::importFileObject();
        } catch (ilManifestFileNotFoundImportException $e) {
            // since there is no manifest xml we assume that this is an HTML export file
            $this->createFromDirectory($e->getTmpDir());
        }
    }

    protected function afterImport(ilObject $new_object): void
    {
        $this->ctrl->setParameter($this, "ref_id", $new_object->getRefId());
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);
        $this->ctrl->redirect($this, "properties");
    }

    public function createFromDirectory(string $a_dir): void
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

    public function exportHTML(): void
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

    public function redirectAfterCreation(): void
    {
        $ctrl = $this->ctrl;
        $ctrl->setParameterByClass("ilObjFileBasedLMGUI", "ref_id", $this->object->getRefId());
        $ctrl->redirectByClass(["ilrepositorygui", "ilObjFileBasedLMGUI"], "properties");
    }

    public function learningProgress(): void
    {
        $this->ctrl->redirectByClass("illearningprogressgui", "");
    }

    public function redrawHeaderAction(): void
    {
        $this->redrawHeaderActionObject();
    }
}
