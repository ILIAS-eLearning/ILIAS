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

use ILIAS\DI\UIServices;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\ResourceStorage\Services;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\File\Icon\IconDatabaseRepository;
use ILIAS\Modules\File\Settings\General;
use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;
use ILIAS\Data\DataSize;
use ILIAS\Refinery\String\Group;
use ILIAS\Data\Factory;
use ILIAS\Services\WOPI\Discovery\ActionDBRepository;
use ILIAS\Services\WOPI\Embed\EmbeddedApplication;
use ILIAS\Data\URI;
use ILIAS\Services\WOPI\Discovery\ActionTarget;

/**
 * GUI class for file objects.
 * @author       Sascha Hofmann <shofmann@databay.de>
 * @author       Stefan Born <stefan.born@phzh.ch>
 * @version      $Id$
 * @ilCtrl_Calls ilObjFileGUI: ilObjectMetaDataGUI, ilInfoScreenGUI, ilPermissionGUI, ilObjectCopyGUI
 * @ilCtrl_Calls ilObjFileGUI: ilExportGUI, ilWorkspaceAccessGUI, ilPortfolioPageGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjFileGUI: ilLearningProgressGUI, ilFileVersionsGUI, ilWOPIEmbeddedApplicationGUI
 * @ilCtrl_Calls ilObjFileGUI: ilFileCommonSettingsGUI
 * @ingroup      ModulesFile
 */
class ilObjFileGUI extends ilObject2GUI
{
    use ilObjFileCopyrightInput;
    use ilObjFileInfoProvider;
    use ilObjFileTransformation;

    public const UPLOAD_MAX_FILES = 100;
    public const PARAM_FILES = 'files';
    public const PARAM_TITLE = 'title';
    public const PARAM_DESCRIPTION = 'description';
    public const PARAM_COPYRIGHT_ID = "copyright_id";

    public const PARAM_UPLOAD_ORIGIN = 'origin';
    public const UPLOAD_ORIGIN_STANDARD = 'standard';
    public const UPLOAD_ORIGIN_DROPZONE = 'dropzone';

    public const CMD_EDIT = "edit";
    public const CMD_VERSIONS = "versions";
    public const CMD_UPLOAD_FILES = "uploadFiles";

    public const CMD_SEND_FILE = "sendFile";

    /**
     * @var \ilObjFile|null $object
     */
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
    protected General $general_settings;
    protected ilFileServicesSettings $file_service_settings;
    protected IconDatabaseRepository $icon_repo;
    private UploadLimitResolver $upload_limit;
    protected \ILIAS\UI\Component\Input\Factory $inputs;
    protected \ILIAS\UI\Renderer $renderer;
    protected \Psr\Http\Message\ServerRequestInterface $request;
    protected \ILIAS\Data\Factory $data_factory;
    private ActionDBRepository $action_repo;

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
        $this->general_settings = new General();
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
        $this->obj_service = $DIC->object();
        $this->lng->loadLanguageModule(ilObjFile::OBJECT_TYPE);
        $this->icon_repo = new IconDatabaseRepository();
        $this->upload_limit = $DIC['ui.upload_limit_resolver'];
        $this->inputs = $DIC->ui()->factory()->input();
        $this->renderer = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->data_factory = new Factory();
        $this->action_repo = new ActionDBRepository($DIC->database());
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

        if (!$this->getCreationMode() && ($this->id_type == self::REPOSITORY_NODE_ID
                && $this->checkPermissionBool("read"))) {
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

        $this->prepareOutput();

        $suffix = ilObjFileAccess::getListGUIData($this->obj_id)["suffix"] ?? "";
        $path_file_icon = $this->icon_repo->getIconFilePathBySuffix($suffix);
        $this->tpl->setTitleIcon($path_file_icon);

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
            case strtolower(ilWOPIEmbeddedApplicationGUI::class):
                if (!$this->checkPermissionBool("edit_file")) {
                    $this->error->raiseError($this->lng->txt("permission_denied"), $this->error->MESSAGE);
                    return;
                }
                $action = $this->action_repo->getActionForSuffix(
                    $this->object->getFileExtension(),
                    ActionTarget::EDIT
                );
                if (null === $action) {
                    $this->error->raiseError($this->lng->txt("no_action_avaliable"), $this->error->MESSAGE);
                    return;
                }

                $embeded_application = new EmbeddedApplication(
                    $this->storage->manage()->find($this->object->getResourceId()),
                    $action,
                    $this->stakeholder,
                    new URI(ilLink::_getLink($this->object->getRefId()))
                );


                $this->ctrl->forwardCommand(
                    new ilWOPIEmbeddedApplicationGUI(
                        $embeded_application
                    )
                );
                break;

            case strtolower(ilFileCommonSettingsGUI::class):
                $this->initSettingsTab();
                $this->tabs_gui->activateSubTab("service_settings");
                $this->ctrl->forwardCommand(
                    new ilFileCommonSettingsGUI(
                        $this->object,
                        $this->ctrl,
                        $this->tpl,
                        $this->lng,
                        $this->object_service
                    )
                );
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
                    break; // otherwise subtabs are duplicated
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
        $title = ($inputs === []) ?
            '' : $inputs[array_key_first($inputs)]->getLabel();

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
        $form->setTitle(''); // see https://mantis.ilias.de/view.php?id=37786

        $tpl = new ilTemplate("tpl.creation_acc_head.html", true, true, "Services/Object");
        $tpl->setVariable("TITLE", $this->lng->txt("option") . " " . $form_type . ": " . $form->getTitle());

        $accordion->addItem($tpl->get(), $form->getHTML());
    }

    protected function getCreationFormsHTML(array $a_forms): string
    {
        // abort if empty array was passed
        if ($a_forms === []) {
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
        $forms = [];
        $forms[self::CFORM_NEW] = $this->initUploadForm();

        // repository only
        if ($this->id_type !== self::WORKSPACE_NODE_ID) {
            $forms[self::CFORM_IMPORT] = $this->initImportForm(ilObjFile::OBJECT_TYPE);
        }

        return $forms;
    }

    public function initUploadForm(): Standard
    {
        $this->getLanguage()->loadLanguageModule('file');
        $inputs = [];

        $this->ctrl->setParameterByClass(self::class, 'new_type', $this->getType());
        $this->ctrl->setParameterByClass(
            self::class,
            self::PARAM_UPLOAD_ORIGIN,
            self::UPLOAD_ORIGIN_STANDARD
        );

        // add file input
        $size = new DataSize(
            $this->upload_limit->getBestPossibleUploadLimitInBytes($this->upload_handler),
            DataSize::MB
        );

        $inputs[self::PARAM_FILES] = $this->ui->factory()->input()->field()->file(
            $this->upload_handler,
            $this->lng->txt('upload_files'),
            sprintf(
                $this->lng->txt('upload_files_limit'),
                (string) $size
            ),
            $this->ui->factory()->input()->field()->group([
                self::PARAM_TITLE => $this->ui->factory()->input()->field()->text(
                    $this->lng->txt('title')
                )->withAdditionalTransformation(
                    $this->getEmptyStringToNullTransformation()
                ),
                self::PARAM_DESCRIPTION => $this->ui->factory()->input()->field()->textarea(
                    $this->lng->txt('description')
                )->withAdditionalTransformation(
                    $this->getEmptyStringToNullTransformation()
                ),
            ])
        )->withMaxFiles(
            self::UPLOAD_MAX_FILES
        )->withRequired(true);

        // add input for copyright selection if enabled in the metadata settings
        if (ilMDSettings::_getInstance()->isCopyrightSelectionActive()) {
            $inputs[self::PARAM_COPYRIGHT_ID] = $this->getCopyrightSelectionInput('set_license_for_all_files');
        }

        return $this->ui->factory()->input()->container()->form()->standard(
            $this->ctrl->getFormActionByClass(self::class, self::CMD_UPLOAD_FILES),
            $inputs
        )->withSubmitLabel($this->lng->txt('upload_files'));
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
            $data = $dropzone->getData();
        } else {
            $form = $this->initUploadForm()->withRequest($this->request);
            $data = $form->getData();
        }
        $files = $data[self::PARAM_FILES] ?? $data[0] ?? null;

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
                    $processor->process(
                        $rid,
                        $file_data[self::PARAM_TITLE] ?? null,
                        $file_data[self::PARAM_DESCRIPTION] ?? null,
                        $data[self::PARAM_COPYRIGHT_ID] ?? $data[1] ?? null
                    );
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

        $link = match ($this->id_type) {
            self::WORKSPACE_NODE_ID => $this->ctrl->getLinkTargetByClass(ilObjWorkspaceRootFolderGUI::class),
            default => ilLink::_getLink($this->requested_ref_id),
        };

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
        $data = [];
        $form = $this->initPropertiesForm();
        $form = $form->withRequest($this->request);
        $inputs = $form->getData();

        /**
         * @var $title_and_description ilObjectPropertyTitleAndDescription
         */
        $title_and_description = $inputs['file_info']['title_and_description'];

        $title = $title_and_description->getTitle();
        // bugfix mantis 26045:
        $filename = empty($data["name"]) ? $this->object->getFileName() : $data["name"];
        $title = '' === trim($title) ? $filename : $this->object->checkFileExtension($filename, $title);
        $this->object->handleChangedObjectTitle($title);

        $description = $title_and_description->getLongDescription();
        $this->object->setDescription($description);

        $updated_title_and_description = new ilObjectPropertyTitleAndDescription($title, $description);
        $this->object->getObjectProperties()->storePropertyTitleAndDescription($updated_title_and_description);

        $this->object->setImportantInfo($inputs['file_info']['important_info']);
        $this->object->setRating($inputs['file_info']['rating'] ?? false);
        $this->object->setOnclickMode((int) $inputs['file_info']['on_click_action']);
        $this->object->update();

        $this->object->getObjectProperties()->storePropertyIsOnline($inputs['availability']['online_status']);

        if (($inputs['presentation']['tile_image'] ?? null) !== null) {
            $this->object->getObjectProperties()->storePropertyTileImage($inputs['presentation']['tile_image']);
        }

        // BEGIN ChangeEvent: Record update event.
        if (!empty($data["name"])) {
            global $DIC;
            $ilUser = $DIC['ilUser'];
            ilChangeEvent::_recordWriteEvent($this->object->getId(), $ilUser->getId(), 'update');
            ilChangeEvent::_catchupWriteEvents($this->object->getId(), $ilUser->getId());
        }
        // END ChangeEvent: Record update event.

        // Update ecs export settings
        //        $ecs = new ilECSFileSettings($this->object);
        //        $ecs->handleSettingsUpdate(); TODO: reintroduce usage of ECS file settings once they have been made compatible with the new ui components

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirectByClass(self::class, self::CMD_EDIT);
    }

    public function edit(): void
    {
        global $DIC;
        $ilErr = $DIC['ilErr'];

        if (!$this->checkPermissionBool("write")) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_write"));
        }

        $this->initSettingsTab();

        $form = $this->initPropertiesForm();

        //        $ecs = new ilECSFileSettings($this->object);
        //        $ecs->addSettingsToForm($form, ilObjFile::OBJECT_TYPE); TODO: reintroduce usage of ECS file settings once they have been made compatible with the new ui components

        $this->tpl->setContent($this->renderer->render($form));
    }

    protected function initPropertiesForm(): \ILIAS\UI\Component\Input\Container\Form\Standard
    {
        $title_and_description = $this->object->getObjectProperties()->getPropertyTitleAndDescription()->toForm(
            $this->lng,
            $this->ui->factory()->input()->field(),
            $this->refinery
        );

        $important_info = $this->inputs->field()->markdown(
            new ilUIMarkdownPreviewGUI(),
            $this->lng->txt('important_info'),
        )->withValue(
            $this->object->getImportantInfo() ?? ""
        );

        $enable_rating = null;
        if ($this->id_type === self::REPOSITORY_NODE_ID) {
            $this->lng->loadLanguageModule('rating');

            $enable_rating = $this->inputs->field()->checkbox(
                $this->lng->txt('rating_activate_rating'),
                $this->lng->txt('rating_activate_rating_info')
            )->withValue(
                $this->object->hasRating()
            );
        }

        $on_click_action = $this->inputs->field()->radio(
            $this->lng->txt('on_click_action')
        )->withOption(
            (string) ilObjFile::CLICK_MODE_DOWNLOAD,
            $this->lng->txt('action_download')
        )->withOption(
            (string) ilObjFile::CLICK_MODE_INFOPAGE,
            $this->lng->txt('action_show')
        )->withValue(
            (string) $this->object->getOnClickMode()
        );

        $input_groups = array_filter([
            "title_and_description" => $title_and_description,
            "important_info" => $important_info,
            "rating" => $enable_rating,
            "on_click_action" => $on_click_action
        ], static fn($input) => null !== $input);

        $file_info_section = $this->inputs->field()->section(
            $input_groups,
            $this->lng->txt('file_info')
        );


        $online_status = $this->object->getObjectProperties()->getPropertyIsOnline()->toForm(
            $this->lng,
            $this->ui->factory()->input()->field(),
            $this->refinery
        );
        $availability_section = $this->inputs->field()->section(
            ["online_status" => $online_status],
            $this->lng->txt('rep_activation_availability')
        );

        $presentation_section = null;
        if ($this->id_type === self::REPOSITORY_NODE_ID) {
            $tile_image = $this->object->getObjectProperties()->getPropertyTileImage()->toForm(
                $this->lng,
                $this->ui->factory()->input()->field(),
                $this->refinery
            );
            $presentation_section = $this->inputs->field()->section(
                ["tile_image" => $tile_image],
                $this->lng->txt('settings_presentation_header')
            );
        }

        $inputs = array_filter([
            "file_info" => $file_info_section,
            "availability" => $availability_section,
            "presentation" => $presentation_section
        ], static fn($input) => null !== $input);

        return $this->inputs->container()->form()->standard(
            $this->ctrl->getLinkTargetByClass(self::class, 'update'),
            $inputs
        );
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
                if ($this->object->getLPMode() === ilLPObjSettings::LP_MODE_CONTENT_VISITED) {
                    ilLPStatusWrapper::_updateStatus(
                        $this->object->getId(),
                        $this->user->getId(),
                        null,
                        false,
                        true
                    );
                }

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

    protected function editExternal(): void
    {
        $this->ctrl->redirectByClass(ilWOPIEmbeddedApplicationGUI::class, ilWOPIEmbeddedApplicationGUI::CMD_INDEX);
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
            $GLOBALS['DIC']['ilErr']->raiseError($this->lng->txt("msg_no_perm_read"), 2); // TODO remove magic number and old ilErr call
        }

        // add set completed button, if LP mode is active
        if ($this->object->getLPMode() === ilLPObjSettings::LP_MODE_MANUAL) {
            if (ilLPStatus::_hasUserCompleted($this->object->getId(), $this->user->getId())) {
                $label = $this->lng->txt('file_btn_lp_toggle_state_completed');
            } else {
                $label = $this->lng->txt('file_btn_lp_toggle_state_not_completed');
            }
            $this->toolbar->addComponent(
                $this->ui->factory()->button()->standard(
                    $label,
                    $this->ctrl->getLinkTarget($this, 'toggleLearningProgress')
                )
            );
        }

        // Add WOPI editor Button
        if (
            $this->checkPermissionBool("edit_file")
            && $this->action_repo->hasActionForSuffix(
                $this->object->getFileExtension(),
                ActionTarget::EDIT
            )) {
            $external_editor = $this->ui->factory()
                                        ->button()
                                        ->standard(
                                            $this->lng->txt('open_external_editor'),
                                            $this->ctrl->getLinkTargetByClass(
                                                \ilWOPIEmbeddedApplicationGUI::class,
                                                \ilWOPIEmbeddedApplicationGUI::CMD_INDEX
                                            )
                                        );
            $this->toolbar->addComponent($external_editor);
        }


        $info = $this->buildInfoScreen(false);
        $this->ctrl->forwardCommand($info);
    }

    protected function toggleLearningProgress(): void
    {
        if (!ilLPStatus::_hasUserCompleted($this->object->getId(), $this->user->getId())) {
            ilLPStatus::writeStatus(
                $this->object->getId(),
                $this->user->getId(),
                ilLPStatus::LP_STATUS_COMPLETED_NUM
            );
        } else {
            ilLPStatus::writeStatus(
                $this->object->getId(),
                $this->user->getId(),
                ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM
            );
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        $this->ctrl->redirect($this, 'infoScreen');
    }

    public function buildInfoScreen(bool $kiosk_mode): ilInfoScreenGUI
    {
        $info = new ilInfoScreenGUI($this);

        if(!$kiosk_mode) { // in kiosk mode we don't want to show the following sections
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

            $obj_id = $this->object->getId();
            $record_gui = new ilAdvancedMDRecordGUI(
                ilAdvancedMDRecordGUI::MODE_INFO,
                'file',
                $obj_id,
                '',
                0,
                $this->call_by_reference
            );
            $record_gui->setInfoObject($info);
            $record_gui->parse();
        }
        // show rating is not possible in kiosk mode

        // Important Information
        $important_info = $this->object->getImportantInfo();
        if (!empty($important_info)) {
            $group = new Group(new Factory(), $this->lng);
            $markdown_to_html = $group->markdown()->toHTML();

            $info->addSection($this->lng->txt("important_info"));
            $info->addProperty("", $markdown_to_html->transform($important_info));
        }

        // Download Launcher
        if ($this->checkPermissionBool("read", self::CMD_SEND_FILE)) {
            // get permanent download link for repository
            if ($this->id_type === self::REPOSITORY_NODE_ID) {
                $download_target = ilObjFileAccess::_getPermanentDownloadLink($this->node_id);
            } else {
                $download_target = rtrim(ILIAS_HTTP_PATH, '/') . '/' . $this->ctrl->getLinkTarget(
                    $this,
                    self::CMD_SEND_FILE
                );
            }
            $url = $this->data_factory->uri($download_target);
            $link = $this->data_factory->link($this->lng->txt('file_download'), $url);
            $download_launcher = $this->ui->factory()->launcher()->inline($link);
            // create own section for download launcher if there is no important info section
            if (empty($important_info)) {
                $info->addSection("");
            }
            // add download launcher
            $info->addProperty("", $this->renderer->render($download_launcher));
        }

        // standard meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

        if (!$kiosk_mode) { // in kiosk mode we don't want to show the following sections
            // links to resource
            if ($this->access->checkAccess("write", "", $this->ref_id) ||
                $this->access->checkAccess("edit_permissions", "", $this->ref_id)) {
                $rs = ilObject::_getAllReferences($this->obj_id);
                $refs = [];
                foreach ($rs as $r) {
                    if ($this->tree->isInTree($r)) {
                        $refs[] = $r;
                    }
                }
                if (count($refs) > 1) {
                    $links = $sep = "";
                    foreach ($refs as $r) {
                        $cont_loc = new ilLocatorGUI();
                        $cont_loc->addContextItems($r, true);
                        $links .= $sep . $cont_loc->getHTML();
                        $sep = "<br />";
                    }

                    $info->addProperty(
                        $this->lng->txt("res_links"),
                        '<div class="small">' . $links . '</div>'
                    );
                }
            }
        }

        // File Info
        $info->addSection($this->lng->txt("file_info"));
        if ($kiosk_mode) {
            $file_info_for_users = $this->getFileInfoForUsers();
            foreach ($file_info_for_users as $file_info_entry_key => $file_info_entry_value) {
                if ($file_info_entry_value !== null) {
                    $info->addProperty($file_info_entry_key, $file_info_entry_value);
                }
            }
        } else {
            $file_info = $this->getAllFileInfoForCurrentUser();
            foreach ($file_info as $file_info_block) {
                foreach ($file_info_block as $file_info_entry_key => $file_info_entry_value) {
                    if ($file_info_entry_value !== null) {
                        $info->addProperty($file_info_entry_key, $file_info_entry_value);
                    }
                }
            }
        }

        $info->hideFurtherSections(false);

        return $info;
    }

    // get tabs
    protected function setTabs(): void
    {
        global $DIC;
        $ilHelp = $DIC['ilHelp'];
        $ilHelp->setScreenIdComponent(ilObjFile::OBJECT_TYPE);

        $this->ctrl->setParameter($this, "ref_id", $this->node_id);

        if ($this->checkPermissionBool("write")) {
            $this->tabs_gui->addTab(
                "id_versions",
                $this->lng->txt(self::CMD_VERSIONS),
                $this->ctrl->getLinkTargetByClass(ilFileVersionsGUI::class, ilFileVersionsGUI::CMD_DEFAULT)
            );
        }

        if ($this->checkPermissionBool("visible") || $this->checkPermissionBool("read")) {
            $this->tabs_gui->addTab(
                "id_info",
                $this->lng->txt("info_short"),
                $this->ctrl->getLinkTargetByClass(["ilobjfilegui", "ilinfoscreengui"], "showSummary")
            );
        }

        if ($this->checkPermissionBool("write")) {
            $this->tabs_gui->addTab(
                "settings",
                $this->lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, self::CMD_EDIT)
            );
        }

        if (ilLearningProgressAccess::checkAccess($this->object->getRefId())) {
            $this->tabs_gui->addTab(
                'learning_progress',
                $this->lng->txt('learning_progress'),
                $this->ctrl->getLinkTargetByClass([self::class, 'illearningprogressgui'], '')
            );
        }

        // meta data
        if ($this->checkPermissionBool("write")) {
            $mdgui = new ilObjectMetaDataGUI($this->object);
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $this->tabs_gui->addTab(
                    "id_meta",
                    $this->lng->txt("meta_data"),
                    $mdtab
                );
            }
        }

        // export
        if ($this->checkPermissionBool("write")) {
            $this->tabs_gui->addTab(
                "export",
                $this->lng->txt("export"),
                $this->ctrl->getLinkTargetByClass("ilexportgui", "")
            );
        }

        // will add permission tab if needed
        parent::setTabs();
    }

    protected function initSettingsTab(): void
    {
        $this->tabs_gui->activateTab("settings");
        // add subtab for common settings
        $this->tabs_gui->addSubTab(
            'file_settings',
            $this->lng->txt('settings'),
            $this->ctrl->getLinkTargetByClass(self::class, self::CMD_EDIT)
        );
        if (in_array('file', ilAdvancedMDRecord::_getActivatedObjTypes(), true)) {
            $this->tabs_gui->addSubTab(
                'service_settings',
                $this->lng->txt('service_settings'),
                $this->ctrl->getLinkTargetByClass(ilFileCommonSettingsGUI::class, ilFileCommonSettingsGUI::CMD_EDIT)
            );
        }

        $this->tabs_gui->activateSubTab("file_settings");
    }

    public static function _goto($a_target, $a_additional = null): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];

        if ($a_additional && str_ends_with($a_additional, "wsp")) {
            ilObjectGUI::_gotoSharedWorkspaceNode((int) $a_target);
        }

        // added support for direct download goto links
        if ($a_additional && str_ends_with($a_additional, "download")) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "sendfile");
        }

        // static method, no workspace support yet

        if ($ilAccess->checkAccess("visible", "", $a_target)
            || $ilAccess->checkAccess("read", "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreen");
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
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

    protected function initHeaderAction(?string $a_sub_type = null, ?int $a_sub_id = null): ?\ilObjectListGUI
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

    protected function getCtrl(): \ilCtrl
    {
        return $this->ctrl;
    }

    /**
     * @throws ilFileException
     */
    protected function getFileObj(): ilObjFile
    {
        if (!$this->object instanceof ilObjFile) {
            throw new ilFileException("Error: object is not of type ilObjFile or doesn't exist");
        }

        return $this->object;
    }

    protected function getFileStakeholder(): ilObjFileStakeholder
    {
        return $this->stakeholder;
    }

    protected function getGeneralSettings(): General
    {
        return $this->general_settings;
    }

    protected function getLanguage(): \ilLanguage
    {
        return $this->lng;
    }

    protected function getNodeID(): int
    {
        return $this->node_id;
    }

    protected function getRefinery(): \ILIAS\Refinery\Factory
    {
        return $this->refinery;
    }

    protected function getUIFactory(): ILIAS\UI\Factory
    {
        return $this->ui->factory();
    }

    protected function getUser(): ilObjUser
    {
        return $this->user;
    }
}
