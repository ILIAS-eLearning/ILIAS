<?php

/**
 * Class ilObjFileServicesGUI
 * @author              Lukas Zehnder <lz@studer-raimann.ch>
 * @ilCtrl_IsCalledBy   ilObjFileServicesGUI: ilAdministrationGUI
 * @ilCtrl_Calls        ilObjFileServicesGUI: ilPermissionGUI
 */
class ilObjFileServicesGUI extends ilObjectGUI
{
    const CMD_EDIT_SETTINGS = 'editSettings';

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;
    /**
     * @var ilTabsGUI
     */
    protected $tabs;
    /**
     * @var ilLanguage
     */
    public $lng;
    /**
     * @var ilErrorHandling
     */
    public $error_handling;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * ilSetting
     */
    protected $settings;
    /**
     * @var ilTemplate
     */
    public $tpl;

    /**
     * Constructor
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference)
    {
        global $DIC;

        $this->type = "fils";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        $this->tabs = $DIC['ilTabs'];
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('adn');
        $this->ctrl = $DIC['ilCtrl'];
        $this->tpl = $DIC['tpl'];
        $this->tree = $DIC['tree'];
        $this->settings = $DIC['ilSetting'];
        $this->rbacsystem = $DIC['rbacsystem'];
        $this->error_handling = $DIC["ilErr"];
    }

    /**
     * @param string $str
     */
    protected function checkPermissionOrFail(string $str) : void
    {
        if (!$this->hasUserPermissionTo($str)) {
            $this->error_handling->raiseError(
                $this->lng->txt('no_permission'),
                $this->error_handling->error_obj->MESSAGE
            );
        }
    }

    protected function hasUserPermissionTo($str) : bool
    {
        return $this->access->checkAccess($str, '', $this->object->getRefId());
    }

    /**
     * Execute command
     * @access public
     */
    public function executeCommand()
    {
        $this->lng->loadLanguageModule("fils");

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();
        $this->checkPermissionOrFail('read');

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            default:
                if (!$cmd || $cmd === 'view') {
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
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'settings',
                $this->ctrl->getLinkTarget($this, self::CMD_EDIT_SETTINGS),
                array(self::CMD_EDIT_SETTINGS, "view")
            );
        }
        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }

    /**
     * called by prepare output
     */
    public function setTitleAndDescription() : void
    {
        parent::setTitleAndDescription();
        $this->tpl->setDescription($this->object->getDescription());
    }

    /**
     * Initializes the settings form.
     */
    private function initSettingsForm() : ilPropertyFormGUI
    {
        $permission_to_write = $this->hasUserPermissionTo('write');

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("settings"));

        // default positive list
        $ne = new ilNonEditableValueGUI($this->lng->txt("file_suffix_default_positive"), "");
        $ne->setValue(implode(", ", ilFileUtils::getDefaultValidExtensionWhiteList()));
        $ne->setInfo($this->lng->txt("file_suffix_default_positive_info"));
        $form->addItem($ne);

        // file suffix custom negative list
        $ta = new ilTextAreaInputGUI($this->lng->txt(
            "file_suffix_custom_negative"),
            "suffix_repl_additional"
        );
        $ta->setInfo($this->lng->txt("file_suffix_custom_negative_info"));
        $ta->setRows(5);
        $ta->setDisabled(!$permission_to_write);
        $form->addItem($ta);

        // file suffix custom positive list
        $ta = new ilTextAreaInputGUI($this->lng->txt(
            "file_suffix_custom_positive"),
            "suffix_custom_white_list"
        );
        $ta->setInfo($this->lng->txt("file_suffix_custom_positive_info"));
        $ta->setRows(5);
        $ta->setDisabled(!$permission_to_write);
        $form->addItem($ta);

        // resulting overall positive list
        $ne = new ilNonEditableValueGUI($this->lng->txt("file_suffix_overall_positive"), "");
        $ne->setValue(implode(", ", ilFileUtils::getValidExtensions()));
        $ne->setInfo($this->lng->txt("file_suffix_overall_positive_info"));
        $form->addItem($ne);

        // explicit negative list
        $ta = new ilTextAreaInputGUI(
            $this->lng->txt("file_suffix_custom_expl_negative"),
            "suffix_custom_expl_black"
        );
        $ta->setInfo($this->lng->txt("file_suffix_custom_expl_negative_info"));
        $ta->setRows(5);
        $ta->setDisabled(!$permission_to_write);
        $form->addItem($ta);

        // command buttons
        if ($permission_to_write) {
            $form->addCommandButton('saveSettings', $this->lng->txt('save'));
            $form->addCommandButton('view', $this->lng->txt('cancel'));
        }

        return $form;
    }

    /**
     * Edit settings.
     */
    protected function editSettings() : void
    {
        $this->tabs_gui->setTabActive('settings');

        $this->checkPermissionOrFail("visible,read");

        // get form
        $form = $this->initSettingsForm();

        // set current values
        $val = array();
        $val["suffix_repl_additional"] = $this->settings->get("suffix_repl_additional");
        $val["suffix_custom_white_list"] = $this->settings->get("suffix_custom_white_list");
        $val["suffix_custom_expl_black"] = $this->settings->get("suffix_custom_expl_black");
        $form->setValuesByArray($val);

        // set content
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Save settings
     */
    protected function saveSettings():void
    {
        $this->checkPermissionOrFail("write");

        // get form
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $this->settings->set("suffix_repl_additional", $_POST["suffix_repl_additional"]);
            $this->settings->set("suffix_custom_white_list", $_POST["suffix_custom_white_list"]);
            $this->settings->set("suffix_custom_expl_black", $_POST["suffix_custom_expl_black"]);

            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, self::CMD_EDIT_SETTINGS);
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }
}
