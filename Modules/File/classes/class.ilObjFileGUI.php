<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class for file objects.
 *
 * @author       Sascha Hofmann <shofmann@databay.de>
 * @author       Stefan Born <stefan.born@phzh.ch>
 * @version      $Id$
 *
 * @ilCtrl_Calls ilObjFileGUI: ilObjectMetaDataGUI, ilInfoScreenGUI, ilPermissionGUI, ilObjectCopyGUI
 * @ilCtrl_Calls ilObjFileGUI: ilExportGUI, ilWorkspaceAccessGUI, ilPortfolioPageGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjFileGUI: ilLearningProgressGUI, ilFileVersionsGUI
 *
 * @ingroup      ModulesFile
 */
class ilObjFileGUI extends ilObject2GUI
{

    const CMD_EDIT = "edit";
    const CMD_VERSIONS = "versions";
    /**
     * @var \ilObjFile
     */
    public $object;
    protected $log = null;


    /**
     * Constructor
     *
     * @param int $a_id
     * @param int $a_id_type
     * @param int $a_parent_node_id
     */
    function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        $this->log = ilLoggerFactory::getLogger('file');
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
    }


    function getType()
    {
        return "file";
    }


    function executeCommand()
    {
        global $DIC;
        $ilNavigationHistory = $DIC['ilNavigationHistory'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilUser = $DIC['ilUser'];
        $ilTabs = $DIC['ilTabs'];
        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if ($this->id_type == self::WORKSPACE_NODE_ID) {
            ilFileInputGUI::setPersonalWorkspaceQuotaCheck(true);
        }

        if (!$this->getCreationMode()) {
            if ($this->id_type == self::REPOSITORY_NODE_ID
                && $this->checkPermissionBool("read")
            ) {
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->node_id);
                $link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "infoScreen");
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);

                // add entry to navigation history
                $ilNavigationHistory->addItem($this->node_id,
                    $link, "file");
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
            case "ilworkspaceaccessgui";
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

            default:
                // in personal workspace use object2gui
                if ($this->id_type == self::WORKSPACE_NODE_ID) {
                    $this->addHeaderAction();

                    // coming from goto we need default command
                    if (empty($cmd)) {
                        $ilCtrl->setCmd("infoScreen");
                    }
                    $ilTabs->clearTargets();

                    return parent::executeCommand();
                }

                if (empty($cmd)) {
                    $cmd = "infoScreen";
                }

                $this->$cmd();
                break;
        }

        $this->addHeaderAction();
    }


    /**
     * @param string $a_new_type
     */
    protected function initCreationForms($a_new_type)
    {
        $forms = array();

        if ($this->id_type == self::WORKSPACE_NODE_ID) {
            if (!ilDiskQuotaHandler::isUploadPossible()) {
                $this->lng->loadLanguageModule("file");
                ilUtil::sendFailure($this->lng->txt("personal_workspace_quota_exceeded_warning"), true);
                $this->ctrl->redirect($this, "cancel");
            }
        }

        // use drag-and-drop upload if configured
        if (ilFileUploadSettings::isDragAndDropUploadEnabled()) {
            $forms[] = $this->initMultiUploadForm();
        } else {
            $forms[] = $this->initSingleUploadForm();
            $forms[] = $this->initZipUploadForm();
        }

        // repository only
        if ($this->id_type != self::WORKSPACE_NODE_ID) {
            $forms[self::CFORM_IMPORT] = $this->initImportForm('file');
            $forms[self::CFORM_CLONE] = $this->fillCloneTemplate(null, "file");
        }

        return $forms;
    }


    /**
     * FORM: Init single upload form.
     */
    public function initSingleUploadForm()
    {
        global $DIC;
        $lng = $DIC['lng'];

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $single_form_gui = new ilPropertyFormGUI();
        $single_form_gui->setMultipart(true);

        // File Title
        $in_title = new ilTextInputGUI($lng->txt("title"), "title");
        $in_title->setInfo($this->lng->txt("if_no_title_then_filename"));
        $in_title->setSize(min(40, ilObject::TITLE_LENGTH));
        $in_title->setMaxLength(ilObject::TITLE_LENGTH);
        $single_form_gui->addItem($in_title);

        // File Description
        $in_descr = new ilTextAreaInputGUI($lng->txt("description"), "description");
        $single_form_gui->addItem($in_descr);

        // File
        $in_file = new ilFileInputGUI($lng->txt("file"), "upload_file");
        $in_file->setRequired(true);
        $single_form_gui->addItem($in_file);

        $single_form_gui->addCommandButton("save", $this->lng->txt($this->type . "_add"));
        $single_form_gui->addCommandButton("saveAndMeta", $this->lng->txt("file_add_and_metadata"));
        $single_form_gui->addCommandButton("cancel", $lng->txt("cancel"));

        $single_form_gui->setTableWidth("600px");
        $single_form_gui->setTarget($this->getTargetFrame("save"));
        $single_form_gui->setTitle($this->lng->txt($this->type . "_new"));
        $single_form_gui->setTitleIcon(ilUtil::getImagePath('icon_file.svg'), $this->lng->txt('obj_file'));

        $this->ctrl->setParameter($this, "new_type", "file");

        $single_form_gui->setFormAction($this->ctrl->getFormAction($this, "save"));

        return $single_form_gui;
    }


    /**
     * save object
     *
     * @access    public
     */
    function save()
    {
        global $DIC;
        $ilUser = $DIC->user();

        if (!$this->checkPermissionBool("create", "", "file")) {
            $this->ilErr->raiseError($this->lng->txt("permission_denied"), $this->ilErr->MESSAGE);
        }

        $single_form_gui = $this->initSingleUploadForm();

        if ($single_form_gui->checkInput()) {
            $title = $single_form_gui->getInput("title");
            $description = $single_form_gui->getInput("description");
            $upload_file = $single_form_gui->getInput("upload_file");

            if (trim($title) == "") {
                $title = $upload_file["name"];
            } else {
                // BEGIN WebDAV: Ensure that object title ends with the filename extension
                $fileExtension = ilObjFileAccess::_getFileExtension($upload_file["name"]);
                $titleExtension = ilObjFileAccess::_getFileExtension($title);
                if ($titleExtension != $fileExtension && strlen($fileExtension) > 0) {
                    $title .= '.' . $fileExtension;
                }
                // END WebDAV: Ensure that object title ends with the filename extension
            }

            // create and insert file in grp_tree

            $fileObj = new ilObjFile();
            $fileObj->setTitle($title);
            $fileObj->setDescription($description);
            $fileObj->setFileName($upload_file["name"]);

            $fileObj->setFileType(ilMimeTypeUtil::getMimeType(
                "", $upload_file["name"], $upload_file["type"]));
            $fileObj->setFileSize($upload_file["size"]);
            $this->object_id = $fileObj->create();

            $this->putObjectInTree($fileObj, $this->parent_id);

            // upload file to filesystem
            $fileObj->createDirectory();
            if ($result = $fileObj->getUploadFile($upload_file["tmp_name"], $upload_file["name"])) {
                $fileObj->setFileName($result->getName());
            }

            $this->handleAutoRating($fileObj);

            // BEGIN ChangeEvent: Record write event.
            ilChangeEvent::_recordWriteEvent($fileObj->getId(), $ilUser->getId(), 'create');
            // END ChangeEvent: Record write event.

            ilUtil::sendSuccess($this->lng->txt("file_added"), true);

            if ($this->ctrl->getCmd() == "saveAndMeta") {
                $this->ctrl->setParameter($this, "new_type", "");
                $target = $this->ctrl->getLinkTargetByClass(array("ilobjectmetadatagui", "ilmdeditorgui"), "listSection", "", false, false);
                ilUtil::redirect($target);
            } else {
                $this->ctrl->returnToParent($this);
            }
        } else {
            $single_form_gui->setValuesByPost();
            $this->tpl->setContent($single_form_gui->getHTML());
        }
    }


    /**
     * save object
     *
     * @access    public
     */
    function saveAndMeta()
    {
        $this->save();
    }


    /**
     * FORM: Init zip upload form.
     */
    public function initZipUploadForm($a_mode = "create")
    {
        global $DIC;
        $lng = $DIC['lng'];

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $zip_form_gui = new ilPropertyFormGUI();
        $zip_form_gui->setMultipart(true);

        // File
        $in_file = new ilFileInputGUI($lng->txt("file"), "zip_file");
        $in_file->setRequired(true);
        $in_file->setSuffixes(array("zip"));
        $zip_form_gui->addItem($in_file);

        // Take over structure
        $in_str = new ilCheckboxInputGUI($this->lng->txt("take_over_structure"), "adopt_structure");
        $in_str->setInfo($this->lng->txt("take_over_structure_info"));
        $zip_form_gui->addItem($in_str);

        $zip_form_gui->addCommandButton("saveUnzip", $this->lng->txt($this->type . "_add"));
        $zip_form_gui->addCommandButton("cancel", $lng->txt("cancel"));

        $zip_form_gui->setTableWidth("600px");
        $zip_form_gui->setTarget($this->getTargetFrame("save"));
        $zip_form_gui->setTitle($this->lng->txt("header_zip"));
        $zip_form_gui->setTitleIcon(ilUtil::getImagePath('icon_file.svg'), $this->lng->txt('obj_file'));

        $this->ctrl->setParameter($this, "new_type", "file");

        $zip_form_gui->setFormAction($this->ctrl->getFormAction($this, "saveUnzip"));

        return $zip_form_gui;
    }


    /**
     * saveUnzip object
     *
     * @access    public
     */
    function saveUnzip()
    {
        $zip_form_gui = $this->initZipUploadForm();

        if ($this->checkPermissionBool("create", "", "file")) {
            if ($zip_form_gui->checkInput()) {
                $zip_file = $zip_form_gui->getInput("zip_file");
                $adopt_structure = $zip_form_gui->getInput("adopt_structure");

                include_once("Services/Utilities/classes/class.ilFileUtils.php");

                // Create unzip-directory
                $newDir = ilUtil::ilTempnam();
                ilUtil::makeDir($newDir);

                // Check if permission is granted for creation of object, if necessary
                if ($this->id_type != self::WORKSPACE_NODE_ID) {

                    $type = ilObject::_lookupType((int) $this->parent_id, true);
                } else {
                    $type = ilObject::_lookupType($this->tree->lookupObjectId($this->parent_id), false);
                }

                $tree = $access_handler = null;
                switch ($type) {
                    // workspace structure
                    case 'wfld':
                    case 'wsrt':
                        $permission = $this->checkPermissionBool("create", "", "wfld");
                        $containerType = "WorkspaceFolder";
                        $tree = $this->tree;
                        $access_handler = $this->getAccessHandler();
                        break;

                    // use categories as structure
                    case 'cat':
                    case 'root':
                        $permission = $this->checkPermissionBool("create", "", "cat");
                        $containerType = "Category";
                        break;

                    // use folders as structure (in courses)
                    default:
                        $permission = $this->checkPermissionBool("create", "", "fold");
                        $containerType = "Folder";
                        break;
                }
                // 	processZipFile (
                //		Dir to unzip,
                //		Path to uploaded file,
                //		should a structure be created (+ permission check)?
                //		ref_id of parent
                //		object that contains files (folder or category)
                //		should sendInfo be persistent?)
                try {
                    $processDone = ilFileUtils::processZipFile($newDir,
                        $zip_file["tmp_name"],
                        ($adopt_structure && $permission),
                        $this->parent_id,
                        $containerType,
                        $tree,
                        $access_handler);
                    ilUtil::sendSuccess($this->lng->txt("file_added"), true);
                } catch (ilFileUtilsException $e) {
                    ilUtil::sendFailure($e->getMessage(), true);
                }

                ilUtil::delDir($newDir);
                $this->ctrl->returnToParent($this);
            } else {
                $zip_form_gui->setValuesByPost();
                $this->tpl->setContent($zip_form_gui->getHTML());
            }
        } else {
            $this->ilErr->raiseError($this->lng->txt("permission_denied"), $this->ilErr->MESSAGE);
        }
    }


    /**
     * updates object entry in object_data
     *
     * @access    public
     */
    public function update()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        $form = $this->initPropertiesForm(self::CMD_EDIT);
        if (!$form->checkInput()) {
            $ilTabs->activateTab("settings");
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());

            return false;
        }

        $title = $form->getInput('title');
        $this->object->setTitle($title);
        $this->object->setDescription($form->getInput('description'));
        $this->object->setRating($form->getInput('rating'));

        $this->update = $this->object->update();

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
     *
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

        // Edit ecs export settings
        include_once 'Modules/File/classes/class.ilECSFileSettings.php';
        $ecs = new ilECSFileSettings($this->object);
        $ecs->addSettingsToForm($form, 'file');

        $this->tpl->setContent($form->getHTML());

        return true;
    }


    /**
     *
     * @param
     *
     * @return
     */
    protected function initPropertiesForm($mode = "create")
    {
        $this->lng->loadLanguageModule('file');

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
            $upload_possible = true;
            if ($this->id_type == self::WORKSPACE_NODE_ID) {
                $upload_possible = ilDiskQuotaHandler::isUploadPossible();
            }

            if ($upload_possible) {
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
            } elseif ($mode == 'create') {
                $file = new ilNonEditableValueGUI($this->lng->txt('obj_file'));
                $file->setValue($this->lng->txt("personal_workspace_quota_exceeded_warning"));
                $form->addItem($file);
            }
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
                // BEGIN ChangeEvent: Record read event.
                require_once('Services/Tracking/classes/class.ilChangeEvent.php');

                // Record read event and catchup with write events
                ilChangeEvent::_recordReadEvent(
                    $this->object->getType(), $this->object->getRefId(), $this->object->getId(), $DIC->user()->getId()
                );
                // END ChangeEvent: Record read event.

                require_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
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
    function infoScreen()
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreenForward();
    }


    /**
     * show information screen
     */
    function infoScreenForward()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];
        $ilErr = $DIC['ilErr'];
        $ilToolbar = $DIC['ilToolbar'];

        $ilTabs->activateTab("id_info");

        if (!$this->checkPermissionBool("visible") && !$this->checkPermissionBool("read")) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_read"));
        }

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);

        if ($this->checkPermissionBool("read", "sendfile")) {
            // #9876
            $this->lng->loadLanguageModule("file");

            // #14378
            include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
            $button = ilLinkButton::getInstance();
            $button->setCaption("file_download");
            $button->setPrimary(true);

            // get permanent download link for repository
            if ($this->id_type == self::REPOSITORY_NODE_ID) {
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
        $info->addProperty($this->lng->txt("type"), $this->object->guessFileType());

        $info->addProperty($this->lng->txt("size"), ilUtil::formatSize(ilObjFile::_lookupFileSize($this->object->getId()), 'long'));
        $info->addProperty($this->lng->txt("version"), $this->object->getVersion());

        if ($this->object->getPageCount() > 0) {
            $info->addProperty($this->lng->txt("page_count"), $this->object->getPageCount());
        }

        // using getVersions function instead of ilHistory direct
        $uploader = $this->object->getVersions();
        $uploader = array_shift($uploader);
        $uploader = $uploader["user_id"];

        $this->lng->loadLanguageModule("file");
        include_once "Services/User/classes/class.ilUserUtil.php";
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

        // display previews
        include_once("./Services/Preview/classes/class.ilPreview.php");
        if (!$this->ctrl->isAsynch()
            && ilPreview::hasPreview($this->object->getId(), $this->object->getType())
            && $this->checkPermissionBool("read")
        ) {
            include_once("./Services/Preview/classes/class.ilPreviewGUI.php");

            // get context for access checks later on
            $context;
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
    function setTabs()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];
        $ilHelp = $DIC['ilHelp'];

        $ilHelp->setScreenIdComponent("file");

        $this->ctrl->setParameter($this, "ref_id", $this->node_id);

        if ($this->checkPermissionBool("write")) {
            $ilTabs->addTab("id_versions",
                $lng->txt(self::CMD_VERSIONS),
                $this->ctrl->getLinkTargetByClass(ilFileVersionsGUI::class, ilFileVersionsGUI::CMD_DEFAULT));
        }

        if ($this->checkPermissionBool("visible") || $this->checkPermissionBool("read")) {
            $ilTabs->addTab("id_info",
                $lng->txt("info_short"),
                $this->ctrl->getLinkTargetByClass(array("ilobjfilegui", "ilinfoscreengui"), "showSummary"));
        }

        if ($this->checkPermissionBool("write")) {
            $ilTabs->addTab("settings",
                $lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, self::CMD_EDIT));
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
                $ilTabs->addTab("id_meta",
                    $lng->txt("meta_data"),
                    $mdtab);
            }
        }

        // export
        if ($this->checkPermissionBool("write")) {
            $ilTabs->addTab("export",
                $lng->txt("export"),
                $this->ctrl->getLinkTargetByClass("ilexportgui", ""));
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
                ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
                ilObjectGUI::_gotoRepositoryRoot();
            }
        }

        $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
    }


    /**
     *
     */
    function addLocatorItems()
    {
        global $DIC;
        $ilLocator = $DIC['ilLocator'];

        if (is_object($this->object)) {
            $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $this->node_id);
        }
    }


    /**
     * Initializes the upload form for multiple files.
     *
     * @return object The created property form.
     */
    public function initMultiUploadForm()
    {
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $dnd_form_gui = new ilPropertyFormGUI();
        $dnd_form_gui->setMultipart(true);
        $dnd_form_gui->setHideLabels();

        // file input
        include_once("Services/Form/classes/class.ilDragDropFileInputGUI.php");
        $dnd_input = new ilDragDropFileInputGUI($this->lng->txt("files"), "upload_files");
        $dnd_input->setArchiveSuffixes(array("zip"));
        $dnd_input->setCommandButtonNames("uploadFiles", "cancel");
        $dnd_form_gui->addItem($dnd_input);

        // add commands
        $dnd_form_gui->addCommandButton("uploadFiles", $this->lng->txt("upload_files"));
        $dnd_form_gui->addCommandButton("cancel", $this->lng->txt("cancel"));

        $dnd_form_gui->setTableWidth("100%");
        $dnd_form_gui->setTarget($this->getTargetFrame("save"));
        $dnd_form_gui->setTitle($this->lng->txt("upload_files_title"));
        $dnd_form_gui->setTitleIcon(ilUtil::getImagePath('icon_file.gif'), $this->lng->txt('obj_file'));

        $this->ctrl->setParameter($this, "new_type", "file");
        $dnd_form_gui->setFormAction($this->ctrl->getFormAction($this, "uploadFiles"));

        return $dnd_form_gui;
    }


    /**
     * Called after a file was uploaded.
     */
    public function uploadFiles()
    {
        global $DIC;

        include_once("./Services/JSON/classes/class.ilJsonUtil.php");

        $response = new stdClass();
        $response->error = null;
        $response->debug = null;

        $files = $_FILES;

        // #14249 - race conditions because of concurrent uploads
        $after_creation_callback = (int) $_REQUEST["crtcb"];
        if ($after_creation_callback) {
            $this->after_creation_callback_objects = array();
            unset($_REQUEST["crtcb"]);
        }

        // load form
        $dnd_form_gui = $this->initMultiUploadForm();
        if ($dnd_form_gui->checkInput()) {
            try {
                if (!$this->checkPermissionBool("create", "", "file")) {
                    $response->error = $this->lng->txt("permission_denied");
                } else {
                    // handle the file
                    $inp = $dnd_form_gui->getInput("upload_files");
                    $this->log->debug("ilObjFileGUI::uploadFiles " . print_r($_POST, true));
                    $this->log->debug("ilObjFileGUI::uploadFiles " . print_r($_FILES, true));
                    $fileresult = $this->handleFileUpload($inp);
                    if ($fileresult) {
                        $response = (object) array_merge((array) $response, (array) $fileresult);
                    }
                }
            } catch (Exception $ex) {
                $response->error = $ex->getMessage();
            }
        } else {
            $dnd_input = $dnd_form_gui->getItemByPostVar("upload_files");
            $response->error = $dnd_input->getAlert();
        }

        if ($after_creation_callback
            && sizeof($this->after_creation_callback_objects)
        ) {
            foreach ($this->after_creation_callback_objects as $new_file_obj) {
                ilObject2GUI::handleAfterSaveCallback($new_file_obj, $after_creation_callback);
            }
            unset($this->after_creation_callback_objects);
        }

        // send response object (don't use 'application/json' as IE wants to download it!)
        header('Vary: Accept');
        header('Content-type: text/plain');

        foreach ($DIC->upload()->getResults() as $result) {
            if (!ilFileUtils::hasValidExtension($result->getName())) {
                $this->lng->loadLanguageModule('file');
                ilUtil::sendInfo($this->lng->txt('file_upload_info_file_with_critical_unknown_extension_later_renamed_when_downloading'), true);
            }
        }
        echo json_encode($response);
        // no further processing!
        exit;
    }


    /**
     * Handles the upload of a single file and adds it to the parent object.
     *
     * @param array $file_upload An array containing the file upload parameters.
     *
     * @return object The response object.
     */
    protected function handleFileUpload($file_upload)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        if ($DIC->upload()->hasBeenProcessed() !== true) {
            if (PATH_TO_GHOSTSCRIPT !== "") {
                $DIC->upload()->register(new ilCountPDFPagesPreProcessors());
            }
        }

        $DIC->upload()->process();
        /**
         * @var $item \ILIAS\FileUpload\DTO\UploadResult
         */
        $item = reset($DIC->upload()->getResults());

        // file upload params

        $file_upload['name'] = $item->getName();

        $filename = ilUtil::stripSlashes($item->getName());
        $type = ilUtil::stripSlashes($item->getMimeType());
        $size = ilUtil::stripSlashes($item->getSize());
        $temp_name = $item->getPath(); // currently used

        // additional params
        $title = ilUtil::stripSlashes($file_upload["title"]);
        $description = ilUtil::stripSlashes($file_upload["description"]);
        $extract = ilUtil::stripSlashes($file_upload["extract"]);
        $keep_structure = ilUtil::stripSlashes($file_upload["keep_structure"]);

        // create answer object
        $response = new stdClass();
        $response->fileName = $filename;
        $response->fileSize = intval($size);
        $response->fileType = $type;
        $response->fileUnzipped = $extract;
        $response->error = null;

        // extract archive?
        if ($extract) {
            $zip_file = $filename;
            $adopt_structure = $keep_structure;

            include_once("Services/Utilities/classes/class.ilFileUtils.php");

            // Create unzip-directory
            $newDir = ilUtil::ilTempnam();
            ilUtil::makeDir($newDir);

            // Check if permission is granted for creation of object, if necessary
            if ($this->id_type != self::WORKSPACE_NODE_ID) {
                $type = ilObject::_lookupType((int) $this->parent_id, true);
            } else {
                $type = ilObject::_lookupType($this->tree->lookupObjectId($this->parent_id), false);
            }

            $tree = $access_handler = null;
            switch ($type) {
                // workspace structure
                case 'wfld':
                case 'wsrt':
                    $permission = $this->checkPermissionBool("create", "", "wfld");
                    $containerType = "WorkspaceFolder";
                    $tree = $this->tree;
                    $access_handler = $this->getAccessHandler();
                    break;

                // use categories as structure
                case 'cat':
                case 'root':
                    $permission = $this->checkPermissionBool("create", "", "cat");
                    $containerType = "Category";
                    break;

                // use folders as structure (in courses)
                default:
                    $permission = $this->checkPermissionBool("create", "", "fold");
                    $containerType = "Folder";
                    break;
            }

            try {
                // 	processZipFile (
                //		Dir to unzip,
                //		Path to uploaded file,
                //		should a structure be created (+ permission check)?
                //		ref_id of parent
                //		object that contains files (folder or category)
                //		should sendInfo be persistent?)
                ilFileUtils::processZipFile(
                    $newDir,
                    $temp_name,
                    ($adopt_structure && $permission),
                    $this->parent_id,
                    $containerType,
                    $tree,
                    $access_handler
                );
            } catch (ilFileUtilsException $e) {
                $response->error = $e->getMessage();
            } catch (Exception $ex) {
                $response->error = $ex->getMessage();
            }

            ilUtil::delDir($newDir);

            // #15404
            if ($this->id_type != self::WORKSPACE_NODE_ID) {
                foreach (ilFileUtils::getNewObjects() as $parent_ref_id => $objects) {
                    if ($parent_ref_id != $this->parent_id) {
                        continue;
                    }

                    foreach ($objects as $object) {
                        $this->after_creation_callback_objects[] = $object;
                    }
                }
            }
        } else {
            if (trim($title) == "") {
                $title = $filename;
            }

            // create and insert file in grp_tree
            $fileObj = new ilObjFile();
            $fileObj->setTitle($title);
            $fileObj->setDescription($description);
            $fileObj->setFileName($filename);
            $fileObj->setFileType($type);
            $fileObj->setFileSize($size);
            $this->object_id = $fileObj->create();
            $this->putObjectInTree($fileObj, $this->parent_id);

            // see uploadFiles()
            if (is_array($this->after_creation_callback_objects)) {
                $this->after_creation_callback_objects[] = $fileObj;
            }

            // upload file to filesystem
            $fileObj->createDirectory();
            $fileObj->raiseUploadError(true);

            $result = $fileObj->getUploadFile($temp_name, $filename);

            if ($result) {
                //if no title for the file was set use the filename as title
                if (empty($fileObj->getTitle())) {
                    $fileObj->setTitle($filename);
                }
                $fileObj->setFileName($filename);
            }
            $fileObj->update();
            $this->handleAutoRating($fileObj);

            ilChangeEvent::_recordWriteEvent($fileObj->getId(), $ilUser->getId(), 'create');
        }

        return $response;
    }


    /**
     * Deletes the file versions that were confirmed by the user.
     */
    function confirmDeleteVersions()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        // has the user the rights to delete versions?
        if (!$this->checkPermissionBool("write")) {
            $this->ilErr->raiseError($this->lng->txt("permission_denied"), $this->ilErr->MESSAGE);
        }

        // delete versions after confirmation
        if (count($_POST["hist_id"]) > 0) {
            $this->object->deleteVersions($_POST["hist_id"]);
            ilUtil::sendSuccess($this->lng->txt("file_versions_deleted"), true);
        }

        $this->ctrl->setParameter($this, "hist_id", "");
        $this->ctrl->redirect($this, self::CMD_VERSIONS);
    }


    /**
     * Deletes this file object.
     */
    function confirmDeleteFile()
    {
        // has the user the rights to delete the file?
        if (!$this->checkPermissionBool("write")) {
            $this->ilErr->raiseError($this->lng->txt("permission_denied"), $this->ilErr->MESSAGE);
        }

        // delete this file object
        include_once("./Services/Repository/classes/class.ilRepUtilGUI.php");
        $ru = new ilRepUtilGUI($this);
        $ru->deleteObjects($this->parent_id, array($this->ref_id));

        // redirect to parent object
        $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->parent_id);
        $this->ctrl->redirectByClass("ilrepositorygui");
    }


    protected function initHeaderAction($a_sub_type = null, $a_sub_id = null)
    {
        $lg = parent::initHeaderAction($a_sub_type, $a_sub_id);
        if (is_object($lg)) {
            if ($this->object->hasRating()) {
                $lg->enableRating(true, null, false,
                    array("ilcommonactiondispatchergui", "ilratinggui"));
            }
        }

        return $lg;
    }
}