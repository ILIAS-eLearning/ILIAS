<?php

use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\File\Sanitation\DownloadSanitationReportUserInteraction;
use ILIAS\File\Sanitation\SanitationReportJob;

/**
 * Class ilObjFileServicesGUI
 * @author       Lukas Zehnder <lz@studer-raimann.ch>
 *
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
     *
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference)
    {
        global $DIC;

        $this->type = "fils";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        $this->tabs = $DIC['ilTabs'];
        $this->lng  = $DIC->language();
        $this->lng->loadLanguageModule('adn');
        $this->ctrl         = $DIC['ilCtrl'];
        $this->tpl          = $DIC['tpl'];
        $this->tree         = $DIC['tree'];
        $this->settings     = $DIC['ilSetting'];
        $this->rbacsystem   = $DIC['rbacsystem'];
        $this->error_handling = $DIC["ilErr"];
    }


    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {
        $this->lng->loadLanguageModule("fils");

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
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $ret = &$this->ctrl->forwardCommand($perm_gui);
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
    public function setTitleAndDescription()
    {
        parent::setTitleAndDescription();
        $this->tpl->setDescription($this->object->getDescription());
    }


    /**
     * Initializes the settings form.
     */
    private function initSettingsForm()
    {
        require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
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
        $form->addItem($ta);

        // file suffix custom positive list
        $ta = new ilTextAreaInputGUI($this->lng->txt(
            "file_suffix_custom_positive"),
            "suffix_custom_white_list"
        );
        $ta->setInfo($this->lng->txt("file_suffix_custom_positive_info"));
        $ta->setRows(5);
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
        $form->addItem($ta);

        // command buttons
        $form->addCommandButton('saveSettings', $this->lng->txt('save'));
        $form->addCommandButton('view', $this->lng->txt('cancel'));

        return $form;
    }


    /**
     * Edit settings.
     */
    public function editSettings()
    {
        $this->tabs_gui->setTabActive('settings');

        if (!$this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->error_handling->raiseError(
                $this->lng->txt("no_permission"),
                $this->error_handling->WARNING
            );
        }

        // get form
        $form = $this->initSettingsForm();

        // set current values
        $val = array();
        $val["suffix_repl_additional"]      = $this->settings->get("suffix_repl_additional");
        $val["suffix_custom_white_list"]    = $this->settings->get("suffix_custom_white_list");
        $val["suffix_custom_expl_black"]    = $this->settings->get("suffix_custom_expl_black");
        $form->setValuesByArray($val);

        // set content
        $this->tpl->setContent($form->getHTML());
    }


    /**
     * Save settings
     */
    public function saveSettings()
    {
        if (!$this->rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $this->error_handling->raiseError(
                $this->lng->txt("no_permission"),
                $this->error_handling->WARNING
            );
        }

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


    public function addToExternalSettingsForm($a_form_id)
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_SECURITY:
                $fields = array('file_suffix_repl' => $this->settings->get("suffix_repl_additional"));
                return array(
                    array(self::CMD_EDIT_SETTINGS, $fields)
                );
        }
    }
}