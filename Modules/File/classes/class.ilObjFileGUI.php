<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

use ILIAS\UI\Component\Input\Container\Form\Standard as UIComponentForm;

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
    public const CMD_EDIT = "edit";
    public const CMD_VERSIONS = "versions";
    public const CMD_UPLOAD_FILES = "uploadFiles";

    /**
     * @var \ilObjFile
     */
    public $object;

    /**
     * @var ilLanguage
     */
    public $lng;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var \ILIAS\HTTP\Services
     */
    protected $http;

    /**
     * @var ilComponentLogger|ilLogger
     */
    protected $log;

    /**
     * @var ilObjectService
     */
    protected $obj_service;

    /**
     * @var \ILIAS\ResourceStorage\Services
     */
    protected $storage;

    /**
     * @var ilObjFileStakeholder
     */
    protected $stakeholder;

    /**
     * @var ilObjFileUploadHandler
     */
    protected $upload_handler;

    /**
     * ilObjFileGUI Constructor
     *
     * @param int $a_id
     * @param int $a_id_type
     * @param int $a_parent_node_id
     */
    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        global $DIC;

        $this->lng            = $DIC->language();
        $this->ui             = $DIC->ui();
        $this->http           = $DIC->http();
        $this->obj_service    = $DIC->object();
        $this->storage        = $DIC->resourceStorage();
        $this->log            = ilLoggerFactory::getLogger('file');
        $this->stakeholder    = new ilObjFileStakeholder($DIC->user()->getId());
        $this->upload_handler = new ilObjFileUploadHandler();

        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->lng->loadLanguageModule("file");
    }

    public function getType()
    {
        return "file";
    }

    public function executeCommand()
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
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);

                // add entry to navigation history
                $ilNavigationHistory->addItem(
                    $this->node_id,
                    $link,
                    "file"
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
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilexportgui":
                $ilTabs->activateTab("export");
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                $ret = $this->ctrl->forwardCommand($exp_gui);
                break;

            case 'ilobjectcopygui':
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('file');
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
                $new_gui = new ilLearningProgressGUI(
                    ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $_GET['user_id'] ? $_GET['user_id'] : $ilUser->getId()
                );
                $this->ctrl->forwardCommand($new_gui);
                $this->tabs_gui->setTabActive('learning_progress');
                break;
            case strtolower(ilFileVersionsGUI::class):
                $this->tabs_gui->activateTab("id_versions");

                if (!$this->checkPermissionBool("write")) {
                    $this->ilErr->raiseError($this->lng->txt("permission_denied"), $this->ilErr->MESSAGE);
                }
                $this->ctrl->forwardCommand(new ilFileVersionsGUI($this->object));
                break;

            case strtolower(ilObjFileUploadHandler::class):
                $this->ctrl->forwardCommand($this->upload_handler);
                break;

            default:
                // in personal workspace use object2gui
                if ((int)$this->id_type === self::WORKSPACE_NODE_ID) {
                    $this->addHeaderAction();

                    // coming from goto we need default command
                    if (empty($cmd)) {
                        $ilCtrl->setCmd("infoScreen");
                    }
                    $ilTabs->clearTargets();

                    return parent::executeCommand();
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
    protected function render() : void
    {
        $this->infoScreen();
    }

    /**
     * returns the HTML of a UI Component form.
     *
     * @param ilAccordionGUI  $accordion
     * @param UIComponentForm $form
     * @param int             $form_type
     */
    private function addUIFormToAccordion(ilAccordionGUI $accordion, UIComponentForm $form, int $form_type) : void
    {
        // abort if form-type is unknown
        if (!in_array($form_type, [self::CFORM_NEW, self::CFORM_CLONE, self::CFORM_IMPORT], true)) {
            return;
        }

        $inputs = $form->getInputs();
        // use label of first input as title, because UI Component forms don't support form-titles yet
        $title = (!empty($inputs)) ?
            $inputs[array_key_first($inputs)]->getLabel() : ''
        ;

        $tpl = new ilTemplate("tpl.creation_acc_head.html", true, true, "Services/Object");
        $tpl->setVariable("TITLE", $this->lng->txt("option") . " " . $form_type . ": " . $title);

        $accordion->addItem($tpl->get(), $this->ui->renderer()->render($form));
    }

    /**
     * returns the HTML of a legacy form.
     *
     * @param ilAccordionGUI    $accordion
     * @param ilPropertyFormGUI $form
     * @param int               $form_type
     */
    private function addLegacyFormToAccordion(ilAccordionGUI $accordion, ilPropertyFormGUI $form, int $form_type) : void
    {
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

    /**
     * returns the HTML for creation forms (accordion).
     * this method overrides the parent method in order to support UI Component forms.
     *
     * @param array $a_forms
     * @return string
     */
    protected function getCreationFormsHTML(array $a_forms)
    {
        // abort if empty array was passed
        if (empty($a_forms)) return '';

        // return ui component form html if it's the only array entry
        if (1 === count($a_forms) && $a_forms[0] instanceof UIComponentForm) {
            return $this->ui->renderer()->render($a_forms[0]);
        }

        // return legacy form html if it's the only array entry
        if (1 === count($a_forms) && $a_forms[0] instanceof ilPropertyFormGUI) {
            return $a_forms[0]->getHTML();
        }

        $accordion = new ilAccordionGUI();
        $accordion->setBehaviour(ilAccordionGUI::FIRST_OPEN);

        foreach ($a_forms as $type => $form) {
            if ($form instanceof UIComponentForm) {
                $this->addUIFormToAccordion($accordion, $form, $type);
            }

            if ($form instanceof ilPropertyFormGUI) {
                $this->addLegacyFormToAccordion($accordion, $form, $type);
            }
        }

        return "<div class='ilCreationFormSection'>{$accordion->getHTML()}</div>";
    }

    /**
     * @param string $a_new_type
     * @return array
     */
    protected function initCreationForms($a_new_type) : array
    {
        $forms = [];
        $forms[self::CFORM_NEW] = $this->initMultiUploadForm();

        // repository only
        if ((int) $this->id_type !== self::WORKSPACE_NODE_ID) {
            $forms[self::CFORM_IMPORT] = $this->initImportForm('file');
            $forms[self::CFORM_CLONE] = $this->fillCloneTemplate(null, "file");
        }

        return $forms;
    }

    /**
     * @return UIComponentForm
     */
    protected function initMultiUploadForm() : UIComponentForm
    {
        return $this->ui->factory()->input()->container()->form()->standard(
            $this->ctrl->getLinkTargetByClass(self::class, self::CMD_UPLOAD_FILES),
            [
                'files' => $this->ui->factory()->input()->field()
                    ->file(
                        $this->upload_handler,
                        $this->lng->txt('upload')
                    )
                    ->withZipExtractOptions(true)
                    ->withMaxFiles(100)
                    ->withNestedInputs([
                        'filename' => $this->ui->factory()->input()->field()
                            ->text(
                                $this->lng->txt('name')
                            )
                        ,
                        'description' => $this->ui->factory()->input()->field()
                            ->textarea(
                                $this->lng->txt('description')
                            )
                        ,
                    ])
                ,
            ]
        );
    }

    /**
     * Returns the file processor according to the given information.
     * @param bool $extract
     * @param bool $keep_structure
     * @return ilObjFileProcessorInterface
     */
    private function getFileProcessor(bool $extract, bool $keep_structure) : ilObjFileProcessorInterface
    {
        if ($extract) {
            if ($keep_structure) {
                $processor = new ilObjFileUnzipRecursiveProcessor(
                    $this->stakeholder,
                    $this,
                    $this->access_handler,
                    $this->tree,
                    $this->id_type
                );
            } else {
                $processor = new ilObjFileUnzipFlatProcessor(
                    $this->stakeholder,
                    $this,
                    $this->access_handler,
                    $this->tree,
                    $this->id_type,
                );
            }
        } else {
            $processor = new ilObjFileProcessor(
                $this->stakeholder,
                $this,
                $this->access_handler,
                $this->tree
            );
        }

        return $processor;
    }

    /**
     * MUST be protected, since this is Called from ilObject2GUI when used in Personal Workspace
     */
    protected function uploadFiles() : void
    {
        $form = $this->initMultiUploadForm();
        $form = $form->withRequest($this->http->request());
        $data = $form->getData();

        if (empty($data['files'])) {
            $this->tpl->setContent(
                $this->ui->renderer()->render(
                    $this->ui->factory()->messageBox()->failure($this->lng->txt('upload_failure'))
                )
            );

            return;
        }

        foreach ($data['files'] as $file_id => $file_data) {
            $rid = $this->storage->manage()->find($file_id);
            if (null !== $rid) {
                $processor = $this->getFileProcessor($file_data['zip_extract'], $file_data['zip_structure']);
                $processor->process($rid, $this->parent_id, [
                    ilObjFileProcessorInterface::OPTION_FILENAME     => $file_data['filename'],
                    ilObjFileProcessorInterface::OPTION_DESCRIPTION  => $file_data['description'],
                ]);
            }
        }

        $link = ilLink::_getLink($this->requested_ref_id);
        $this->ctrl->redirectToURL($link);
    }

    /**
     * updates object entry in object_data
     * @access    public
     */
    public function update()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        $form = $this->initPropertiesForm();
        if (!$form->checkInput()) {
            $ilTabs->activateTab("settings");
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());

            return false;
        }

        $title = $form->getInput('title');
        // bugfix mantis 26045:
        $filename = empty($data["name"]) ? $this->object->getFileName() : $data["name"];
        if (strlen(trim($title)) == 0) {
            $title = $filename;
        } else {
            $title = $this->object->checkFileExtension($filename, $title);
        }
        $this->object->setTitle($title);
        $this->object->setDescription($form->getInput('description'));
        $this->object->setRating($form->getInput('rating'));

        $this->update = $this->object->update();
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

        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        ilUtil::redirect($this->ctrl->getLinkTarget($this, self::CMD_EDIT, '', false, false));
    }

    /**
     * edit object
     * @access    public
     */
    public function edit()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];
        $ilErr = $DIC['ilErr'];

        if (!$this->checkPermissionBool("write")) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_write"));
        }

        $ilTabs->activateTab("settings");

        $form = $this->initPropertiesForm(self::CMD_EDIT);

        $val = array();
        $val['title'] = $this->object->getTitle();
        $val['description'] = $this->object->getLongDescription();
        $val['rating'] = $this->object->hasRating();
        $form->setValuesByArray($val);
        $ecs = new ilECSFileSettings($this->object);
        $ecs->addSettingsToForm($form, 'file');

        $this->tpl->setContent($form->getHTML());

        return true;
    }

    /**
     * @param
     * @return
     */
    protected function initPropertiesForm($mode = "create")
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

        if ($mode === 'create') {

            $file = new ilFileStandardDropzoneInputGUI($this->lng->txt('obj_file'), 'file');
            $file->setRequired(false);
            $form->addItem($file);

            $group = new ilRadioGroupInputGUI('', 'replace');
            $group->setValue(0);

            $replace = new ilRadioOption($this->lng->txt('replace_file'), 1);
            $replace->setInfo($this->lng->txt('replace_file_info'));
            $group->addOption($replace);

            $keep = new ilRadioOption($this->lng->txt('file_new_version'), 0);
            $keep->setInfo($this->lng->txt('file_new_version_info'));
            $group->addOption($keep);

            $file->addSubItem($group);

        } else {
            $o = new ilNonEditableValueGUI($this->lng->txt('upload_info'));
            $o->setValue($this->lng->txt('upload_info_desc'));
            $form->addItem($o);
        }
        $desc = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
        $desc->setRows(3);
        $form->addItem($desc);

        if ($this->id_type == self::REPOSITORY_NODE_ID) {
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

    public function sendFile()
    {
        global $DIC;

        try {
            if (ANONYMOUS_USER_ID == $DIC->user()->getId() && isset($_GET['transaction'])) {
                $a_hist_entry_id = isset($_GET["hist_id"]) ? $_GET["hist_id"] : null;
                $this->object->sendFile($a_hist_entry_id);
            }

            if ($this->checkPermissionBool("read")) {
                // Record read event and catchup with write events
                ilChangeEvent::_recordReadEvent(
                    $this->object->getType(),
                    $this->object->getRefId(),
                    $this->object->getId(),
                    $DIC->user()->getId()
                );
                ilLPStatusWrapper::_updateStatus($this->object->getId(), $DIC->user()->getId());

                $a_hist_entry_id = isset($_GET["hist_id"]) ? $_GET["hist_id"] : null;
                $this->object->sendFile($a_hist_entry_id);
            } else {
                $this->ilErr->raiseError($this->lng->txt("permission_denied"), $this->ilErr->MESSAGE);
            }
        } catch (\ILIAS\Filesystem\Exception\FileNotFoundException $e) {
            $this->ilErr->raiseError($e->getMessage(), $this->ilErr->MESSAGE);
        }

        return true;
    }

    /**
     * @deprecated
     */
    public function versions()
    {
        $this->ctrl->redirectByClass(ilFileVersionsGUI::class);
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
        $this->infoScreenForward();
    }

    /**
     * show information screen
     */
    public function infoScreenForward()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];
        $ilErr = $DIC['ilErr'];
        $ilToolbar = $DIC['ilToolbar'];

        $ilTabs->activateTab("id_info");

        if (!$this->checkPermissionBool("visible") && !$this->checkPermissionBool("read")) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_read"));
        }

        $info = new ilInfoScreenGUI($this);

        if ($this->checkPermissionBool("read", "sendfile")) {
            $button = ilLinkButton::getInstance();
            $button->setCaption("file_download");
            $button->setPrimary(true);

            // get permanent download link for repository
            if ($this->id_type === self::REPOSITORY_NODE_ID) {
                $button->setUrl(ilObjFileAccess::_getPermanentDownloadLink($this->node_id));
            } else {
                $button->setUrl($this->ctrl->getLinkTarget($this, "sendfile"));
            }

            $ilToolbar->addButtonInstance($button);
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

        $info->addProperty($this->lng->txt("size"),
            ilUtil::formatSize(ilObjFileAccess::_lookupFileSize($this->object->getId()), 'long'));
        $info->addProperty($this->lng->txt("version"), $this->object->getVersion());

        $version = $this->object->getVersions([$this->object->getVersion()]);
        $version = end($version);
        if($version instanceof ilObjFileVersion) {
            $info->addProperty($this->lng->txt("version_uploaded"), (new ilDateTime($version->getDate(), IL_CAL_DATETIME))->get(IL_CAL_DATETIME));
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
    public function setTabs()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];
        $ilHelp = $DIC['ilHelp'];

        $ilHelp->setScreenIdComponent("file");

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

    public static function _goto($a_target, $a_additional = null)
    {
        global $DIC;
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];

        if ($a_additional && substr($a_additional, -3) == "wsp") {
            $_GET["baseClass"] = "ilsharedresourceGUI";
            $_GET["wsp_id"] = $a_target;
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
                ilUtil::sendFailure(sprintf(
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
    public function addLocatorItems()
    {
        global $DIC;
        $ilLocator = $DIC['ilLocator'];

        if (is_object($this->object)) {
            $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $this->node_id);
        }
    }

    /**
     * Initializes the upload form for multiple files.
     * @return object The created property form.
     */
    public function initLegacyMultiUploadForm()
    {
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $dnd_form_gui = new ilPropertyFormGUI();
        $dnd_form_gui->setMultipart(true);
        $dnd_form_gui->setHideLabels();
        $dnd_input = new ilDragDropFileInputGUI($this->lng->txt("files"), "upload_files");
        $dnd_input->setArchiveSuffixes(["zip"]);
        $dnd_input->setCommandButtonNames(self::CMD_UPLOAD_FILES, "cancel");
        $dnd_form_gui->addItem($dnd_input);

        // add commands
        $dnd_form_gui->addCommandButton(self::CMD_UPLOAD_FILES, $this->lng->txt("upload_files"));
        $dnd_form_gui->addCommandButton("cancel", $this->lng->txt("cancel"));

        $dnd_form_gui->setTableWidth("100%");
        $dnd_form_gui->setTarget($this->getTargetFrame("save"));
        $dnd_form_gui->setTitle($this->lng->txt("upload_files_title"));
        $dnd_form_gui->setTitleIcon(ilUtil::getImagePath('icon_file.gif'));

        $this->ctrl->setParameter($this, "new_type", "file");
        $dnd_form_gui->setFormAction($this->ctrl->getFormAction($this, self::CMD_UPLOAD_FILES));

        return $dnd_form_gui;
    }

    protected function initHeaderAction($a_sub_type = null, $a_sub_id = null)
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
