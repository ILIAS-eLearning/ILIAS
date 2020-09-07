<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObject2GUI.php");

/**
 * Help settings gui class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjHelpSettingsGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjHelpSettingsGUI: ilAdministrationGUI
 *
 * @ingroup ServicesHelp
 */
class ilObjHelpSettingsGUI extends ilObject2GUI
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
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilDB
     */
    protected $db;


    /**
     * Constructor
     */
    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        global $DIC;
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->rbacsystem = $DIC->rbac()->system();
        $this->error = $DIC["ilErr"];
        $this->access = $DIC->access();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->settings = $DIC->settings();
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->tpl = $DIC["tpl"];
        $this->db = $DIC->database();
    }

    /**
     * Get type
     */
    public function getType()
    {
        return "hlps";
    }

    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {
        $ilErr = $this->error;
        $ilAccess = $this->access;
        $lng = $this->lng;
        $lng->loadLanguageModule("help");

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
    * Edit news settings.
    */
    public function editSettings()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilSetting = $this->settings;
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;
        
        $ilTabs->activateTab("settings");
        
        if (OH_REF_ID > 0) {
            ilUtil::sendInfo("This installation is used for online help authoring. Help modules cannot be imported.");
            return;
        }
        
        if ($this->checkPermissionBool("write")) {
            // help file
            include_once("./Services/Form/classes/class.ilFileInputGUI.php");
            $fi = new ilFileInputGUI($lng->txt("help_help_file"), "help_file");
            $fi->setSuffixes(array("zip"));
            $ilToolbar->addInputItem($fi, true);
            $ilToolbar->addFormButton($lng->txt("upload"), "uploadHelpFile");
            $ilToolbar->addSeparator();
            
            // help mode
            include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
            $options = array(
                "" => $lng->txt("help_tooltips_and_help"),
                "1" => $lng->txt("help_help_only"),
                "2" => $lng->txt("help_tooltips_only")
                );
            $si = new ilSelectInputGUI($this->lng->txt("help_mode"), "help_mode");
            $si->setOptions($options);
            $si->setValue($ilSetting->get("help_mode"));
            $ilToolbar->addInputItem($si);
            
            $ilToolbar->addFormButton($lng->txt("help_set_mode"), "setMode");
        }
        $ilToolbar->setFormAction($ilCtrl->getFormAction($this), true);
        
        include_once("./Services/Help/classes/class.ilHelpModuleTableGUI.php");
        $tab = new ilHelpModuleTableGUI($this, "editSettings", $this->checkPermissionBool("write"));
        
        $this->tpl->setContent($tab->getHTML());
    }

    /**
     * administration tabs show only permissions and trash folder
     */
    public function getAdminTabs()
    {
        if ($this->checkPermissionBool("visible,read")) {
            $this->tabs_gui->addTab(
                "settings",
                $this->lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "editSettings")
            );
        }
        
        if ($this->checkPermissionBool("edit_permission")) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $this->lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm")
            );
        }
    }

    
    
    
    /**
     * Upload help file
     *
     * @param
     * @return
     */
    public function uploadHelpFile()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        if ($this->checkPermissionBool("write")) {
            $this->object->uploadHelpModule($_FILES["help_file"]);
            ilUtil::sendSuccess($lng->txt("help_module_uploaded"), true);
        }
        
        $ilCtrl->redirect($this, "editSettings");
    }
    
    /**
     * Confirm help modules deletion
     */
    public function confirmHelpModulesDeletion()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        $this->checkPermission("write");
            
        if (!is_array($_POST["id"]) || count($_POST["id"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "editSettings");
        } else {
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("help_sure_delete_help_modules"));
            $cgui->setCancel($lng->txt("cancel"), "editSettings");
            $cgui->setConfirm($lng->txt("delete"), "deleteHelpModules");
            
            foreach ($_POST["id"] as $i) {
                $cgui->addItem("id[]", $i, $this->object->lookupModuleTitle($i));
            }
            
            $tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete help modules
     *
     * @param
     * @return
     */
    public function deleteHelpModules()
    {
        $ilDB = $this->db;
        $ilCtrl = $this->ctrl;

        $this->checkPermission("write");
        
        if (is_array($_POST["id"])) {
            foreach ($_POST["id"] as $i) {
                $this->object->deleteModule((int) $i);
            }
        }
        
        $ilCtrl->redirect($this, "editSettings");
    }
    
    /**
     * Activate module
     *
     * @param
     * @return
     */
    public function activateModule()
    {
        $ilSetting = $this->settings;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->checkPermission("write");
        
        $ilSetting->set("help_module", (int) $_GET["hm_id"]);
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "editSettings");
    }
    
    /**
     * Deactivate module
     *
     * @param
     * @return
     */
    public function deactivateModule()
    {
        $ilSetting = $this->settings;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->checkPermission("write");
        
        if ($ilSetting->get("help_module") == (int) $_GET["hm_id"]) {
            $ilSetting->set("help_module", "");
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "editSettings");
    }
    
    /**
     * Set mode
     *
     * @param
     * @return
     */
    public function setMode()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;

        $this->checkPermission("write");
        
        if ($this->checkPermissionBool("write")) {
            $ilSetting->set("help_mode", ilUtil::stripSlashes($_POST["help_mode"]));
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }
        
        $ilCtrl->redirect($this, "editSettings");
    }
}
