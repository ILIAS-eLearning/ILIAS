<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");


/**
* Accessibility Settings.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjAccessibilitySettingsGUI: ilPermissionGUI
* @ilCtrl_IsCalledBy ilObjAccessibilitySettingsGUI: ilAdministrationGUI
*
* @ingroup ServicesAccessibility
*/
class ilObjAccessibilitySettingsGUI extends ilObjectGUI
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * Contructor
     *
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->error = $DIC["ilErr"];
        $this->access = $DIC->access();
        $this->tabs = $DIC->tabs();
        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->type = 'accs';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule('acc');
    }

    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;
        $ilAccess = $this->access;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt('no_permission'), $ilErr->WARNING);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = "editAccessibilitySettings";
                }

                $this->$cmd();
                break;
        }
        return true;
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function getSettingsForm()
    {
        require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('settings'));

        require_once 'Services/Administration/classes/class.ilAdministrationSettingsFormHandler.php';
        ilAdministrationSettingsFormHandler::addFieldsToForm(
            ilAdministrationSettingsFormHandler::FORM_ACCESSIBILITY,
            $form,
            $this
        );
        
        return $form;
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    protected function editAccessibilitySettings(ilPropertyFormGUI $form = null)
    {
        $this->tabs_gui->setTabActive('acc_settings');
        if (!$form) {
            $form = $this->getSettingsForm();
        }
        
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Get tabs
     *
     * @access public
     *
     */
    public function getAdminTabs()
    {
        $rbacsystem = $this->rbacsystem;
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;

        if ($rbacsystem->checkAccess("read", $this->object->getRefId())) {
            $ilTabs->addTab('acc_settings', $this->lng->txt('settings'), $this->ctrl->getLinkTarget($this, 'editAccessibilitySettings'));
        }

        if ($rbacsystem->checkAccess("read", $this->object->getRefId())) {
            $ilTabs->addTarget(
                "acc_access_keys",
                $this->ctrl->getLinkTarget($this, "editAccessKeys"),
                array("editAccessKeys", "view")
            );
        }

        if ($rbacsystem->checkAccess("edit_permission", $this->object->getRefId())) {
            $ilTabs->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }

    /**
    * Edit access keys
    */
    public function editAccessKeys()
    {
        $tpl = $this->tpl;

        $this->tabs_gui->setTabActive('acc_access_keys');
        
        include_once("./Services/Accessibility/classes/class.ilAccessKeyTableGUI.php");
        $table = new ilAccessKeyTableGUI($this, "editAccessKeys");
        
        $tpl->setContent($table->getHTML());
    }
    
    /**
    * Save access keys
    */
    public function saveAccessKeys()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilAccess = $this->access;
        
        if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) {
            include_once("./Services/Accessibility/classes/class.ilAccessKey.php");
            ilAccessKey::writeKeys(ilUtil::stripSlashesArray($_POST["acckey"]));
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "editAccessKeys");
    }
}
