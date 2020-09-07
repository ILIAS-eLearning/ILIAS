<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");


/**
* Media Objects/Pools Settings.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjMediaObjectsSettingsGUI: ilPermissionGUI
* @ilCtrl_IsCalledBy ilObjMediaObjectsSettingsGUI: ilAdministrationGUI
*
* @ingroup ServicesMediaObject
*/
class ilObjMediaObjectsSettingsGUI extends ilObjectGUI
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
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->type = 'mobs';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule('mob');
        $this->lng->loadLanguageModule('mep');
        $this->lng->loadLanguageModule('content');
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

        if (!$ilAccess->checkAccess('read', '', $this->object->getRefId())) {
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
                    $cmd = "editSettings";
                }

                $this->$cmd();
                break;
        }
        return true;
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

        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $ilTabs->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "editSettings"),
                array("editSettings", "view")
            );
        }

        if ($ilAccess->checkAccess('edit_permission', "", $this->object->getRefId())) {
            $ilTabs->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }

    /**
    * Edit settings
    */
    public function editSettings($a_omit_init = false)
    {
        $tpl = $this->tpl;
        
        if (!$a_omit_init) {
            $this->initMediaObjectsSettingsForm();
            $this->getSettingsValues();
        }
        $tpl->setContent($this->form->getHTML());
    }
        
    /**
     * Save settings
     */
    public function saveSettings()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        $this->checkPermission("write");
        
        $this->initMediaObjectsSettingsForm();
        if ($this->form->checkInput()) {
            // perform save
            $mset = new ilSetting("mobs");
            $mset->set("mep_activate_pages", $_POST["activate_pages"]);
            $mset->set("file_manager_always", $_POST["file_manager_always"]);
            $mset->set("restricted_file_types", $_POST["restricted_file_types"]);
            $mset->set("black_list_file_types", $_POST["black_list_file_types"]);
            $mset->set("upload_dir", $_POST["mob_upload_dir"]);
            
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editSettings");
        }
        
        $this->form->setValuesByPost();
        $this->editSettings(true);
    }
    
    /**
     * Init media objects settings form.
     */
    public function initMediaObjectsSettingsForm()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        
    
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
    
        // activate page in media pool
        $cb = new ilCheckboxInputGUI($lng->txt("mobs_activate_pages"), "activate_pages");
        $cb->setInfo($lng->txt("mobs_activate_pages_info"));
        $this->form->addItem($cb);
    
        // activate page in media pool
        $cb = new ilCheckboxInputGUI($lng->txt("mobs_always_show_file_manager"), "file_manager_always");
        $cb->setInfo($lng->txt("mobs_always_show_file_manager_info"));
        $this->form->addItem($cb);
        
        // allowed file types
        $ta = new ilTextAreaInputGUI($this->lng->txt("mobs_restrict_file_types"), "restricted_file_types");
        //$ta->setCols();
        //$ta->setRows();
        $ta->setInfo($this->lng->txt("mobs_restrict_file_types_info"));
        $this->form->addItem($ta);

        // black lis file types
        $ta = new ilTextAreaInputGUI($this->lng->txt("mobs_black_list_file_types"), "black_list_file_types");
        $ta->setInfo($this->lng->txt("mobs_black_list_file_types_info"));
        $this->form->addItem($ta);

        // Upload dir for learning resources
        $tx_prop = new ilTextInputGUI(
            $lng->txt("mob_upload_dir"),
            "mob_upload_dir"
        );
        $tx_prop->setInfo($lng->txt("mob_upload_dir_info"));
        $this->form->addItem($tx_prop);

        if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $this->form->addCommandButton("saveSettings", $lng->txt("save"));
        }

        $this->form->setTitle($lng->txt("settings"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Get current values for form from
     */
    public function getSettingsValues()
    {
        $values = array();
    
        $mset = new ilSetting("mobs");
        $values["activate_pages"] = $mset->get("mep_activate_pages");
        $values["file_manager_always"] = $mset->get("file_manager_always");
        $values["restricted_file_types"] = $mset->get("restricted_file_types");
        $values["black_list_file_types"] = $mset->get("black_list_file_types");
        $values["mob_upload_dir"] = $mset->get("upload_dir");

        $this->form->setValuesByArray($values);
    }
}
