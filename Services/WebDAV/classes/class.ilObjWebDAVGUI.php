<?php

use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\File\Sanitation\DownloadSanitationReportUserInteraction;
use ILIAS\File\Sanitation\SanitationReportJob;

/**
 * Class ilObjWebDAVGUI
 * @author       Lukas Zehnder <lz@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy   ilObjWebDAVGUI: ilAdministrationGUI
 * @ilCtrl_Calls        ilObjWebDAVGUI: ilPermissionGUI
 * @package             webdav
 */
class ilObjWebDAVGUI extends ilObjectGUI
{
    const CMD_EDIT_SETTINGS = 'editSettings';
    const CMD_SAVE_SETTINGS = 'saveSettings';

    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilDBInterface
     */
    protected $db;
    /**
     * @var ilErrorHandling
     */
    public $error_handling;
    /**
     * @var \ILIAS\Filesystem\Filesystems
     */
    protected $filesystem;
    /**
     * @var \ILIAS\DI\HTTPServices
     */
    protected $http;
    /**
     * @var ilLanguage
     */
    public $lng;
    /**
     * @var ilLogger
     */
    protected $logger;
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;
    /**
     * ilSetting
     */
    protected $settings;
    /**
     * @var ilTabsGUI
     */
    protected $tabs;
    /**
     * @var ilTemplate
     */
    public $tpl;
    /**
     * @var \ILIAS\UI\Factory
     */
    protected $ui_factory;
    /**
     * @var \ILIAS\UI\Renderer
     */
    protected $ui_renderer;
    /**
     * @var \ILIAS\FileUpload\FileUpload
     */
    protected $upload;


    /**
     * Constructor
     *
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference)
    {
        global $DIC;

        $this->type = "wbdv";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        $this->ctrl = $DIC['ilCtrl'];
        $this->db = $DIC->database();
        $this->error_handling = $DIC["ilErr"];
        $this->filesystem = $DIC->filesystem();
        $this->http = $DIC->http();
        $this->lng = $DIC->language();
        $this->logger = $DIC->logger()->root();
        $this->rbacsystem = $DIC['rbacsystem'];
        $this->settings = $DIC['ilSetting'];
        $this->tabs = $DIC['ilTabs'];
        $this->tpl = $DIC['tpl'];
        $this->tree = $DIC['tree'];
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->upload = $DIC->upload();
    }


    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error_handling->raiseError(
                $this->lng->txt('no_permission'),
                $this->error_handling->error_obj->MESSAGE
            );
        }

        switch ($next_class) {
            case strtolower(ilWebDAVMountInstructionsUploadGUI::class):
                $document_gui = new ilWebDAVMountInstructionsUploadGUI(
                    $this->object,
                    $this->tpl,
                    $this->user,
                    $this->ctrl,
                    $this->lng,
                    $this->rbacsystem,
                    $this->error_handling,
                    $this->logger,
                    $this->toolbar,
                    $this->http,
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->filesystem,
                    $this->upload,
                    new ilWebDAVMountInstructionsRepositoryImpl($this->db)
                );
                $this->tabs_gui->activateTab('webdav_upload_instructions');
                $this->ctrl->forwardCommand($document_gui);
                break;

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = self::CMD_EDIT_SETTINGS;
                }
                $this->$cmd();
                break;
        }

        return true;
    }


    /**
     * Get tabs
     */
    public function getAdminTabs()
    {
        if ($this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                'webdav_general_settings',
                $this->lng->txt("webdav_general_settings"),
                $this->ctrl->getLinkTarget($this, self::CMD_EDIT_SETTINGS)
            );
            $this->tabs_gui->addTab(
                'webdav_upload_instructions',
                $this->lng->txt("webdav_upload_instructions"),
                $this->ctrl->getLinkTargetByClass(ilWebDAVMountInstructionsUploadGUI::class)
            );
        }
    }


    /**
     * called by prepare output
     */
    public function setTitleAndDescription()
    {
        parent::setTitleAndDescription();
        $this->tpl->setDescription($this->object->getDescription());
    }


    protected function initSettingsForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("settings"));

        // Enable webdav
        $cb_prop = new ilCheckboxInputGUI($this->lng->txt("enable_webdav"), "enable_webdav");
        $cb_prop->setValue('1');
        $cb_prop->setChecked($this->object->isWebdavEnabled());
        $form->addItem($cb_prop);

        // Enable versioning
        $cb_prop = new ilCheckboxInputGUI($this->lng->txt("webdav_enable_versioning"), "enable_versioning_webdav");
        $cb_prop->setValue('1');
        $cb_prop->setInfo($this->lng->txt("webdav_versioning_info"));
        $cb_prop->setChecked($this->object->isWebdavVersioningEnabled());
        $form->addItem($cb_prop);

        // command buttons
        $form->addCommandButton(self::CMD_SAVE_SETTINGS, $this->lng->txt('save'));

        return $form;
    }

    /**
     * Edit settings.
     */
    public function editSettings()
    {
        $this->tabs_gui->activateTab('webdav_general_settings');

        if (!$this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->error_handling->raiseError(
                $this->lng->txt("no_permission"),
                $this->error_handling->WARNING
            );
        }

        $form = $this->initSettingsForm();

        $this->tpl->setContent($form->getHTML());
    }


    /**
     * Save settings
     */
    public function saveSettings()
    {
        if (!$this->rbacsystem->checkAccess("write", $this->object->getRefId())) {
            ilUtil::sendFailure($this->lng->txt('no_permission'), true);
            $this->ctrl->redirect($this, self::CMD_EDIT_SETTINGS);
        }

        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $this->object->setWebdavEnabled($_POST['enable_webdav'] == '1');
            $this->object->setWebdavVersioningEnabled($_POST['enable_versioning_webdav'] == '1');
            $this->object->update();
            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, self::CMD_EDIT_SETTINGS);
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }
}
