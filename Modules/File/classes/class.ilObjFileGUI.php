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

use ILIAS\DI\Container;
use ILIAS\DI\UIServices;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\ResourceStorage\Services;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\UI\Implementation\Component\Input\Container\Form\Standard;
use ILIAS\UI\Implementation\Component\Dropzone\File\File as Dropzone;

/**
 * GUI class for file objects.
 * @author       Sascha Hofmann <shofmann@databay.de>
 * @author       Stefan Born <stefan.born@phzh.ch>
 * @version      $Id$
 * @ilCtrl_Calls ilObjFileGUI: ilObjectMetaDataGUI, ilInfoScreenGUI, ilPermissionGUI, ilObjectCopyGUI
 * @ilCtrl_Calls ilObjFileGUI: ilExportGUI, ilWorkspaceAccessGUI, ilPortfolioPageGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjFileGUI: ilLearningProgressGUI, ilFileVersionsGUI
 * @ingroup      ModulesFile
 */
class ilObjFileGUI extends ilObject2GUI
{
    public const UPLOAD_MAX_FILES = 100;
    public const PARAM_FILES = Dropzone::FILE_INPUT_KEY;

    public const PARAM_UPLOAD_ORIGIN = 'origin';
    public const UPLOAD_ORIGIN_STANDARD = 'standard';
    public const UPLOAD_ORIGIN_DROPZONE = 'dropzone';

    public const CMD_EDIT = "edit";
    public const CMD_VERSIONS = "versions";
    public const CMD_UPLOAD_FILES = "uploadFiles";

    public ?ilObject $object = null;
    public ilLanguage $lng;
    protected UIServices $ui;
    protected UploadHandler $upload_handler;
    protected ResourceStakeholder $stakeholder;
    protected Services $storage;
    protected ?ilLogger $log = null;
    protected ilObjectService $obj_service;
    protected \ILIAS\Refinery\Factory $refinery;
    protected \ILIAS\HTTP\Wrapper\WrapperFactory $http;
    protected ilFileServicesSettings $file_service_settings;

    /**
     * Constructor
     */
    public function __construct(int $a_id = 0, int $a_id_type = self::REPOSITORY_NODE_ID, int $a_parent_node_id = 0)
    {
        global $DIC;
        $this->http = $DIC->http()->wrapper();
        $this->request = $DIC->http()->request();
        $this->refinery = $DIC->refinery();
        $this->file_service_settings = $DIC->fileServiceSettings();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->log = ilLoggerFactory::getLogger(ilObjFile::OBJECT_TYPE);
        $this->ui = $DIC->ui();
        $this->storage = $DIC->resourceStorage();
        $this->upload_handler = new ilObjFileUploadHandlerGUI();
        $this->stakeholder = new ilObjFileStakeholder();
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
        $this->obj_service = $DIC->object();
        $this->lng->loadLanguageModule(ilObjFile::OBJECT_TYPE);
    }

    public function getType(): string
    {
        return ilObjFile::OBJECT_TYPE;
    }

    public function getParentId(): int
    {
        return $this->parent_id;
    }

    public function executeCommand(): void
    {
        global $DIC;
        $ilNavigationHistory = $DIC['ilNavigationHistory'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilUser = $DIC['ilUser'];
        $ilTabs = $DIC['ilTabs'];
        $ilErr = $DIC['ilErr'];

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if (!$this->getCreationMode()) {
            if ($this->id_type == self::REPOSITORY_NODE_ID
                && $this->checkPermissionBool("read")
            ) {
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->node_id);
                $link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "infoScreen");
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);

                // add entry to navigation history
                $ilNavigationHistory->addItem(
                    $this->node_id,
                    $link,
                    ilObjFile::OBJECT_TYPE
                );
            }
        }

        $this->prepareOutput();

        switch ($next_class) {
            case "ilinfoscreengui":
                $this->infoScreenForward();    // forwards command
                break;

            case 'ilobjectmetadatagui':
                if (!$this->checkPermissionBool("write")) {
                    $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
                }

                $ilTabs->activateTab("id_meta");

                $md_gui = new ilObjectMetaDataGUI($this->object);

                // todo: make this work
                // $md_gui->addMDObserver($this->object,'MDUpdateListener','Technical');

                $this->ctrl->forwardCommand($md_gui);
                break;

            // repository permissions
            case 'ilpermissiongui':
                $ilTabs->activateTab("id_permissions");
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilexportgui":
                $ilTabs->activateTab("export");
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                $this->ctrl->forwardCommand($exp_gui);
                break;

            case 'ilobjectcopygui':
                $cp = new ilObjectCopyGUI($this);
                $cp->setType(ilObjFile::OBJECT_TYPE);
                $this->ctrl->forwardCommand($cp);
                break;

            // personal workspace permissions
            case "ilworkspaceaccessgui":
                $ilTabs->activateTab("id_permissions");
                $wspacc = new ilWorkspaceAccessGUI($this->node_id, $this->getAccessHandler());
                $this->ctrl->forwardCommand($wspacc);
                break;

            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            case "illearningprogressgui":
                $ilTabs->activateTab('learning_progress');
                $user_id = $this->http->query()->has('user_id')
                    ? $this->http->query()->retrieve('user_id', $this->refinery->kindlyTo()->int())
                    : $ilUser->getId();
                $new_gui = new ilLearningProgressGUI(
                    ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $user_id
                );
                $this->ctrl->forwardCommand($new_gui);
                $this->tabs_gui->setTabActive('learning_progress');
                break;
            case strtolower(ilFileVersionsGUI::class):
                $this->tabs_gui->activateTab("id_versions");

                if (!$this->checkPermissionBool("write")) {
                    $this->error->raiseError($this->lng->txt("permission_denied"), $this->error->MESSAGE);
                }
                /** @var ilObjFile $obj */
                $obj = $this->object;
                $this->ctrl->forwardCommand(new ilFileVersionsGUI($obj));
                break;
            case strtolower(ilObjFileUploadHandlerGUI::class):
                $this->ctrl->forwardCommand(new ilObjFileUploadHandlerGUI());
                break;

            default:
                // in personal workspace use object2gui
                if ($this->id_type === self::WORKSPACE_NODE_ID) {
                    $this->addHeaderAction();

                    // coming from goto we need default command
                    if (empty($cmd)) {
                        $ilCtrl->setCmd("infoScreen");
                    }
                    $ilTabs->clearTargets();

                    parent::executeCommand();
                }

                if (empty($cmd) || $cmd === 'render') {
                    $cmd = "infoScreen";
                }

                $this->$cmd();
                break;
        }

        $this->addHeaderAction();
    }

    /**
     * This Method is needed if called from personal resources
     * @see executeCommand() line 162
     */
    protected function render(): void
    {
        $this->infoScreen();
    }

    protected function addUIFormToAccordion(ilAccordionGUI $accordion, Standard $form, int $form_type): void
    {
        // abort if form-type is unknown
        if (!in_array($form_type, [self::CFORM_NEW, self::CFORM_CLONE, self::CFORM_IMPORT], true)) {
            return;
        }

        $inputs = $form->getInputs();
        // use label of first input as title, because UI Component forms don't support form-titles yet
        $title = (!empty($inputs)) ?
            $inputs[array_key_first($inputs)]->getLabel() : '';

        $tpl = new ilTemplate("tpl.creation_acc_head.html", true, true, "Services/Object");
        $tpl->setVariable("TITLE", $this->lng->txt("option") . " " . $form_type . ": " . $title);

        $accordion->addItem($tpl->get(), $this->ui->renderer()->render($form));
    }

    protected function addLegacyFormToAccordion(
        ilAccordionGUI $accordion,
        ilPropertyFormGUI $form,
        int $form_type
    ): void {
        // abort if form-type is unknown
        if (!in_array($form_type, [self::CFORM_NEW, self::CFORM_CLONE, self::CFORM_IMPORT], true)) {
            return;
        }

        // see bug #0016217
        if (method_exists($this, "getCreationFormTitle")) {
            if (!empty(($title = $this->getCreationFormTitle($form_type)))) {
                $form->setTitle($title);
            }
        }

        $tpl = new ilTemplate("tpl.creation_acc_head.html", true, true, "Services/Object");
        $tpl->setVariable("TITLE", $this->lng->txt("option") . " " . $form_type . ": " . $form->getTitle());

        $accordion->addItem($tpl->get(), $form->getHTML());
    }

    protected function getCreationFormsHTML(array $a_forms): string
    {
        // abort if empty array was passed
        if (empty($a_forms)) {
            return '';
        }

        if (1 === count($a_forms)) {
            $creation_form = end($a_forms);
            if ($creation_form instanceof Standard) {
                return $this->ui->renderer()->render($creation_form);
            }

            if ($creation_form instanceof ilPropertyFormGUI) {
                return $creation_form->getHTML();
            }
        }

        $accordion = new ilAccordionGUI();
        $accordion->setBehaviour(ilAccordionGUI::FIRST_OPEN);

        foreach ($a_forms as $type => $form) {
            if ($form instanceof Standard) {
                $this->addUIFormToAccordion($accordion, $form, $type);
            }

            if ($form instanceof ilPropertyFormGUI) {
                $this->addLegacyFormToAccordion($accordion, $form, $type);
            }
        }

        return "<div class='ilCreationFormSection'>{$accordion->getHTML()}</div>";
    }

    /**
     * @return array
     */
    protected function initCreationForms($a_new_type): array
    {
        $forms[self::CFORM_NEW] = $this->initUploadForm();

        // repository only
        if ($this->id_type !== self::WORKSPACE_NODE_ID) {
            $forms[self::CFORM_IMPORT] = $this->initImportForm(ilObjFile::OBJECT_TYPE);
            $forms[self::CFORM_CLONE] = $this->fillCloneTemplate(null, ilObjFile::OBJECT_TYPE);
        }

        return $forms;
    }

    public function initUploadForm(): Standard
    {
        $this->ctrl->setParameterByClass(self::class, 'new_type', $this->getType());
        $this->ctrl->setParameterByClass(
            self::class,
            self::PARAM_UPLOAD_ORIGIN,
            self::UPLOAD_ORIGIN_STANDARD
        );

        return $this->ui->factory()->input()->container()->form()->standard(
            $this->ctrl->getFormActionByClass(self::class, self::CMD_UPLOAD_FILES),
            [
                self::PARAM_FILES => $this->ui->factory()->input()->field()->file(
                    $this->upload_handler,
                    $this->lng->txt('upload_files'),
                    null,
                    $this->ui->factory()->input()->field()->group([
                        ilObjFileProcessorInterface::OPTION_FILENAME => $this->ui->factory()->input()->field()->text($this->lng->txt('title')),
                        ilObjFileProcessorInterface::OPTION_DESCRIPTION => $this->ui->factory()->input()->field()->textarea($this->lng->txt('description')),
                    ])
                )->withMaxFiles(self::UPLOAD_MAX_FILES)
                 ->withMaxFileSize((int) ilFileUtils::getUploadSizeLimitBytes())
                 ->withRequired(true),
            ]
        )->withSubmitCaption($this->lng->txt('upload_files'));
    }

    /**
     * MUST be protected, since this is Called from ilObject2GUI when used in Personal Workspace.
     */
    protected function uploadFiles(): void
    {
        $origin = ($this->http->query()->has(self::PARAM_UPLOAD_ORIGIN)) ?
            $this->http->query()->retrieve(
                self::PARAM_UPLOAD_ORIGIN,
                $this->refinery->kindlyTo()->string()
            ) : self::UPLOAD_ORIGIN_STANDARD;

        if (self::UPLOAD_ORIGIN_DROPZONE === $origin) {
            $dropzone = new ilObjFileUploadDropzone($this->parent_id);
            $dropzone = $dropzone->getDropzone()->withRequest($this->request);
            $files = $dropzone->getData();
        } else {
            $form = $this->initUploadForm()->withRequest($this->request);
            $files = $form->getData()[self::PARAM_FILES] ?? null;
        }

        if (empty($files)) {
            $form = $this->initUploadForm()->withRequest($this->request);
            $this->tpl->setContent($this->ui->renderer()->render($form));
            return;
        }

        $processor = new ilObjFileProcessor(
            $this->stakeholder,
            $this,
            $this->storage,
            $this->file_service_settings
        );

        $errors = false;
        foreach ($files as $file_data) {
            $rid = $this->storage->manage()->find($file_data[$this->upload_handler->getFileIdentifierParameterName()]);
            if (null !== $rid) {
                try {
                    $processor->process($rid, [
                        ilObjFileProcessorInterface::OPTION_FILENAME => $file_data[ilObjFileProcessorInterface::OPTION_FILENAME],
                        ilObjFileProcessorInterface::OPTION_DESCRIPTION => $file_data[ilObjFileProcessorInterface::OPTION_DESCRIPTION],
                    ]);
                } catch (Throwable $t) {
                    $errors = true;
                    if (null !== $this->log) {
                        $this->log->error($t->getMessage() . ": " . $t->getTraceAsString());
                    }
                }
            }
        }

        if ($errors) {
            $this->ui->mainTemplate()->setOnScreenMessage(
                'failure',
                $this->lng->txt('could_not_create_file_objs'),
                true
            );
        }

        if ($processor->getInvalidFileNames() !== []) {
            $this->ui->mainTemplate()->setOnScreenMessage(
                'info',
                sprintf(
                    $this->lng->txt('file_upload_info_file_with_critical_extension'),
                    implode(', ', $processor->getInvalidFileNames())
                ),
                true
            );
        }

        switch ($this->id_type) {
            case self::WORKSPACE_NODE_ID:
                $link = $this->ctrl->getLinkTargetByClass(ilObjWorkspaceRootFolderGUI::class);
                break;
            case self::REPOSITORY_NODE_ID:
            default:
                $link = ilLink::_getLink($this->requested_ref_id);
                break;
        }

        $this->ctrl->redirectToURL($link);
    }

    public function putObjectInTree(ilObject $obj, int $parent_node_id = null): void
    {
        // this is needed to support multi fileuploads in personal and shared resources
        $backup_node_id = $this->node_id;
        parent::putObjectInTree($obj, $parent_node_id);
        $this->node_id = $backup_node_id;
    }

    /**
     * updates object entry in object_data
     */
    public function update(): void
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        $form = $this->initPropertiesForm();
        if (!$form->checkInput()) {
            $ilTabs->activateTab("settings");
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());

            return;
        }

        $title = $form->getInput('title');
        // bugfix mantis 26045:
        $filename = empty($data["name"]) ? $this->object->getFileName() : $data["name"];
        if (strlen(trim($title)) == 0) {
            $title = $filename;
        } else {
            $title = $this->object->checkFileExtension($filename, $title);
        }

        $this->object->handleChangedObjectTitle($title);
        $this->object->setDescription($form->getInput('description'));
        $this->object->setRating($form->getInput('rating'));

        $this->object->update();
        $this->obj_service->commonSettings()->legacyForm($form, $this->object)->saveTileImage();

        // BEGIN ChangeEvent: Record update event.
        if (!empty($data["name"])) {
            global $DIC;
            $ilUser = $DIC['ilUser'];
            ilChangeEvent::_recordWriteEvent($this->object->getId(), $ilUser->getId(), 'update');
            ilChangeEvent::_catchupWriteEvents($this->object->getId(), $ilUser->getId());
        }
        // END ChangeEvent: Record update event.

        // Update ecs export settings
        $ecs = new ilECSFileSettings($this->object);
        $ecs->handleSettingsUpdate();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        ilUtil::redirect($this->ctrl->getLinkTarget($this, self::CMD_EDIT, '', false, false));
    }

    public function edit(): void
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];
        $ilErr = $DIC['ilErr'];

        if (!$this->checkPermissionBool("write")) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_write"));
        }

        $ilTabs->activateTab("settings");

        $form = $this->initPropertiesForm();

        $val = [];
        $val['title'] = $this->object->getTitle();
        $val['description'] = $this->object->getLongDescription();
        $val['rating'] = $this->object->hasRating();
        $form->setValuesByArray($val);
        $ecs = new ilECSFileSettings($this->object);
        $ecs->addSettingsToForm($form, ilObjFile::OBJECT_TYPE);

        $this->tpl->setContent($form->getHTML());
    }

    protected function initPropertiesForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'update'));

        $form->setTitle($this->lng->txt('file_edit'));
        $form->addCommandButton('update', $this->lng->txt('save'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $title->setValue($this->object->getTitle());
        $title->setInfo($this->lng->txt("if_no_title_then_filename"));
        $form->addItem($title);

        $o = new ilNonEditableValueGUI($this->lng->txt('upload_info'));
        $o->setValue($this->lng->txt('upload_info_desc'));
        $form->addItem($o);

        $desc = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
        $desc->setRows(3);
        $form->addItem($desc);

        if ($this->id_type === self::REPOSITORY_NODE_ID) {
            $this->lng->loadLanguageModule('rating');
            $rate = new ilCheckboxInputGUI($this->lng->txt('rating_activate_rating'), 'rating');
            $rate->setInfo($this->lng->txt('rating_activate_rating_info'));
            $form->addItem($rate);
        }

        $presentationHeader = new ilFormSectionHeaderGUI();
        $presentationHeader->setTitle($this->lng->txt('settings_presentation_header'));
        $form->addItem($presentationHeader);
        $this->obj_service->commonSettings()->legacyForm($form, $this->object)->addTileImage();

        return $form;
    }

    public function sendFile(): bool
    {
        $hist_entry_id = $this->http->query()->has('hist_id')
            ? $this->http->query()->retrieve('hist_id', $this->refinery->kindlyTo()->int())
            : null;
        try {
            if (ANONYMOUS_USER_ID === $this->user->getId() && $this->http->query()->has('transaction')) {
                $this->object->sendFile($hist_entry_id);
            }

            if ($this->checkPermissionBool("read")) {
                // Record read event and catchup with write events
                ilChangeEvent::_recordReadEvent(
                    $this->object->getType(),
                    $this->object->getRefId(),
                    $this->object->getId(),
                    $this->user->getId()
                );
                ilLPStatusWrapper::_updateStatus($this->object->getId(), $this->user->getId());

                $this->object->sendFile($hist_entry_id);
            } else {
                $this->error->raiseError($this->lng->txt("permission_denied"), $this->error->MESSAGE);
            }
        } catch (\ILIAS\Filesystem\Exception\FileNotFoundException $e) {
            $this->error->raiseError($e->getMessage(), $this->error->MESSAGE);
        }

        return true;
    }

    /**
     * @deprecated
     */
    public function versions(): void
    {
        $this->ctrl->redirectByClass(ilFileVersionsGUI::class);
    }

    public function unzipCurrentRevision(): void
    {
        $this->ctrl->redirectByClass(ilFileVersionsGUI::class, ilFileVersionsGUI::CMD_UNZIP_CURRENT_REVISION);
    }

    /**
     * this one is called from the info button in the repository
     * not very nice to set cmdClass/Cmd manually, if everything
     * works through ilCtrl in the future this may be changed
     */
    public function infoScreen(): void
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreenForward();
    }

    /**
     * show information screen
     */
    public function infoScreenForward(): void
    {
        $this->tabs_gui->activateTab("id_info");

        if (!$this->checkPermissionBool("visible") && !$this->checkPermissionBool("read")) {
            $GLOBALS['DIC']['ilErr']->raiseError($this->lng->txt("msg_no_perm_read"));
        }

        $info = new ilInfoScreenGUI($this);

        if ($this->checkPermissionBool("read", "sendfile")) {
            $button = ilLinkButton::getInstance();
            $button->setTarget('_blank');
            $button->setCaption("file_download");
            $button->setPrimary(true);

            // get permanent download link for repository
            if ($this->id_type === self::REPOSITORY_NODE_ID) {
                $button->setUrl(ilObjFileAccess::_getPermanentDownloadLink($this->node_id));
            } else {
                $button->setUrl($this->ctrl->getLinkTarget($this, "sendfile"));
            }

            $this->toolbar->addButtonInstance($button);
        }

        $info->enablePrivateNotes();

        if ($this->checkPermissionBool("read")) {
            $info->enableNews();
        }

        // no news editing for files, just notifications
        $info->enableNewsEditing(false);
        if ($this->checkPermissionBool("write")) {
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");

            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", true);
                $info->setBlockProperty("news", "public_notifications_option", true);
            }
        }

        // standard meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

        // File Info
        $info->addSection($this->lng->txt("file_info"));
        $info->addProperty($this->lng->txt("filename"), $this->object->getFileName());
        $info->addProperty($this->lng->txt("type"), $this->object->getFileType());
        $info->addProperty($this->lng->txt("resource_id"), $this->object->getResourceId());
        $info->addProperty($this->lng->txt("storage_id"), $this->object->getStorageID());

        $info->addProperty(
            $this->lng->txt("size"),
            ilUtil::formatSize($this->object->getFileSize(), 'long')
        );
        $info->addProperty($this->lng->txt("version"), $this->object->getVersion());

        $version = $this->object->getVersions([$this->object->getVersion()]);
        $version = end($version);
        if ($version instanceof ilObjFileVersion) {
            $info->addProperty(
                $this->lng->txt("version_uploaded"),
                (new ilDateTime($version->getDate(), IL_CAL_DATETIME))->get(IL_CAL_DATETIME)
            );
        }

        if ($this->object->getPageCount() > 0) {
            $info->addProperty($this->lng->txt("page_count"), $this->object->getPageCount());
        }

        // using getVersions function instead of ilHistory direct
        $uploader = $this->object->getVersions();
        $uploader = array_shift($uploader);
        $uploader = $uploader["user_id"];
        $info->addProperty($this->lng->txt("file_uploaded_by"), ilUserUtil::getNamePresentation($uploader));

        // download link added in repository
        if ($this->id_type == self::REPOSITORY_NODE_ID && $this->checkPermissionBool("read", "sendfile")) {
            $tpl = new ilTemplate("tpl.download_link.html", true, true, "Modules/File");
            $tpl->setVariable("LINK", ilObjFileAccess::_getPermanentDownloadLink($this->node_id));
            $info->addProperty($this->lng->txt("download_link"), $tpl->get());
        }

        if ($this->id_type == self::WORKSPACE_NODE_ID) {
            $info->addProperty($this->lng->txt("perma_link"), $this->getPermanentLinkWidget());
        }
        if (!$this->ctrl->isAsynch()
            && ilPreview::hasPreview($this->object->getId(), $this->object->getType())
            && $this->checkPermissionBool("read")
        ) {
            // get context for access checks later on
            switch ($this->id_type) {
                case self::WORKSPACE_NODE_ID:
                case self::WORKSPACE_OBJECT_ID:
                    $context = ilPreviewGUI::CONTEXT_WORKSPACE;
                    break;

                default:
                    $context = ilPreviewGUI::CONTEXT_REPOSITORY;
                    break;
            }

            $preview = new ilPreviewGUI($this->node_id, $context, $this->object->getId(), $this->access_handler);
            $info->addProperty($this->lng->txt("preview"), $preview->getInlineHTML());
        }

        // forward the command
        // $this->ctrl->setCmd("showSummary");
        // $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->ctrl->forwardCommand($info);
    }

    // get tabs
    protected function setTabs(): void
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];
        $ilHelp = $DIC['ilHelp'];

        $ilHelp->setScreenIdComponent(ilObjFile::OBJECT_TYPE);

        $this->ctrl->setParameter($this, "ref_id", $this->node_id);

        if ($this->checkPermissionBool("write")) {
            $ilTabs->addTab(
                "id_versions",
                $lng->txt(self::CMD_VERSIONS),
                $this->ctrl->getLinkTargetByClass(ilFileVersionsGUI::class, ilFileVersionsGUI::CMD_DEFAULT)
            );
        }

        if ($this->checkPermissionBool("visible") || $this->checkPermissionBool("read")) {
            $ilTabs->addTab(
                "id_info",
                $lng->txt("info_short"),
                $this->ctrl->getLinkTargetByClass(array("ilobjfilegui", "ilinfoscreengui"), "showSummary")
            );
        }

        if ($this->checkPermissionBool("write")) {
            $ilTabs->addTab(
                "settings",
                $lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, self::CMD_EDIT)
            );
        }

        if (ilLearningProgressAccess::checkAccess($this->object->getRefId())) {
            $ilTabs->addTab(
                'learning_progress',
                $lng->txt('learning_progress'),
                $this->ctrl->getLinkTargetByClass(array(__CLASS__, 'illearningprogressgui'), '')
            );
        }

        // meta data
        if ($this->checkPermissionBool("write")) {
            $mdgui = new ilObjectMetaDataGUI($this->object);
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $ilTabs->addTab(
                    "id_meta",
                    $lng->txt("meta_data"),
                    $mdtab
                );
            }
        }

        // export
        if ($this->checkPermissionBool("write")) {
            $ilTabs->addTab(
                "export",
                $lng->txt("export"),
                $this->ctrl->getLinkTargetByClass("ilexportgui", "")
            );
        }

        // will add permission tab if needed
        parent::setTabs();
    }

    public static function _goto($a_target, $a_additional = null): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];

        if ($a_additional && substr($a_additional, -3) == "wsp") {
            /** @noRector  */
            include("ilias.php");
            exit;
        }

        // added support for direct download goto links
        if ($a_additional && substr($a_additional, -8) == "download") {
            ilObjectGUI::_gotoRepositoryNode($a_target, "sendfile");
        }

        // static method, no workspace support yet

        if ($ilAccess->checkAccess("visible", "", $a_target)
            || $ilAccess->checkAccess("read", "", $a_target)
        ) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreen");
        } else {
            if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
                $main_tpl->setOnScreenMessage('failure', sprintf(
                    $lng->txt("msg_no_perm_read_item"),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
                ), true);
                ilObjectGUI::_gotoRepositoryRoot();
            }
        }

        $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
    }

    /**
     *
     */
    protected function addLocatorItems(): void
    {
        global $DIC;
        $ilLocator = $DIC['ilLocator'];

        if (is_object($this->object)) {
            $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $this->node_id);
        }
    }

    protected function initHeaderAction($a_sub_type = null, $a_sub_id = null): ?\ilObjectListGUI
    {
        $lg = parent::initHeaderAction($a_sub_type, $a_sub_id);
        if ($lg instanceof ilObjectListGUI && $this->object->hasRating()) {
            $lg->enableRating(
                true,
                null,
                false,
                [ilCommonActionDispatcherGUI::class, ilRatingGUI::class]
            );
        }

        return $lg;
    }
}
