<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectGUI.php");

/**
 * Exercise Administration Settings
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @ilCtrl_Calls ilObjExerciseAdministrationGUI: ilPermissionGUI
 *
 * @ingroup ModulesExercise
 */
class ilObjExerciseAdministrationGUI extends ilObjectGUI
{
    /**
     * Contructor
     *
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->type = "excs";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule("exercise");
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

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
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
        if ($this->checkPermissionBool("visible,read")) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "editSettings"),
                array("editSettings", "view")
            );
        }

        if ($this->checkPermissionBool('edit_permission')) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }

    
    /**
    * Edit settings.
    */
    public function editSettings($a_form = null)
    {
        $lng = $this->lng;
        $ilSetting = $this->settings;
        
        $this->tabs_gui->setTabActive('settings');
        
        if (!$a_form) {
            $a_form = $this->initFormSettings();
        }
        $this->tpl->setContent($a_form->getHTML());
        return true;
    }

    /**
    * Save settings
    */
    public function saveSettings()
    {
        $ilCtrl = $this->ctrl;
        
        $this->checkPermission("write");
        
        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            $exc_set = new ilSetting("excs");
            $exc_set->set("add_to_pd", (bool) $form->getInput("pd"));
            
            ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "editSettings");
        }
        
        $form->setValuesByPost();
        $this->editSettings($form);
    }

    /**
    * Save settings
    */
    public function cancel()
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->redirect($this, "view");
    }
        
    /**
     * Init settings property form
     *
     * @access protected
     */
    protected function initFormSettings()
    {
        $lng = $this->lng;
        $ilAccess = $this->access;
        
        include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('exc_admin_settings'));
        
        if ($this->checkPermissionBool("write")) {
            $form->addCommandButton('saveSettings', $this->lng->txt('save'));
            $form->addCommandButton('cancel', $this->lng->txt('cancel'));
        }

        $exc_set = new ilSetting("excs");
        
        $pd = new ilCheckboxInputGUI($lng->txt("to_desktop"), "pd");
        $pd->setInfo($lng->txt("exc_to_desktop_info"));
        $pd->setChecked($exc_set->get("add_to_pd", true));
        $form->addItem($pd);

        return $form;
    }
}
