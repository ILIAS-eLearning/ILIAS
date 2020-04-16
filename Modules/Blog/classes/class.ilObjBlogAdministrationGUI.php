<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");

/**
* Blog Administration Settings.
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id:$
*
* @ilCtrl_Calls ilObjBlogAdministrationGUI: ilPermissionGUI
* @ilCtrl_IsCalledBy ilObjBlogAdministrationGUI: ilAdministrationGUI
*
* @ingroup ModulesForum
*/
class ilObjBlogAdministrationGUI extends ilObjectGUI
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
        $this->type = "blga";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule("blog");
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
        
        if (!$ilSetting->get("disable_wsp_blogs")) {
            ilUtil::sendInfo($lng->txt("blog_admin_toggle_info"));
        } else {
            ilUtil::sendInfo($lng->txt("blog_admin_inactive_info"));
        }
        
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
            $banner = (bool) $form->getInput("banner");
            
            $blga_set = new ilSetting("blga");
            $blga_set->set("banner", $banner);
            $blga_set->set("banner_width", (int) $form->getInput("width"));
            $blga_set->set("banner_height", (int) $form->getInput("height"));
            $blga_set->set("mask", (bool) $form->getInput("mask"));
            
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
        
        include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('blog_settings'));
        
        if ($this->checkPermissionBool("write")) {
            $form->addCommandButton('saveSettings', $this->lng->txt('save'));
            $form->addCommandButton('cancel', $this->lng->txt('cancel'));
        }

        $banner = new ilCheckboxInputGUI($lng->txt("blog_preview_banner"), "banner");
        $banner->setInfo($lng->txt("blog_preview_banner_info"));
        $form->addItem($banner);
        
        $width = new ilNumberInputGUI($lng->txt("blog_preview_banner_width"), "width");
        $width->setRequired(true);
        $width->setSize(4);
        $banner->addSubItem($width);
        
        $height = new ilNumberInputGUI($lng->txt("blog_preview_banner_height"), "height");
        $height->setRequired(true);
        $height->setSize(4);
        $banner->addSubItem($height);
        
        $blga_set = new ilSetting("blga");
        $banner->setChecked($blga_set->get("banner", false));
        if ($blga_set->get("banner")) {
            $width->setValue($blga_set->get("banner_width"));
            $height->setValue($blga_set->get("banner_height"));
        } else {
            $width->setValue(1370);
            $height->setValue(100);
        }

        /*
        $mask = new ilCheckboxInputGUI($lng->txt("blog_allow_html"), "mask");
        $mask->setInfo($lng->txt("blog_allow_html_info"));
        $mask->setChecked($blga_set->get("mask", false));
        $form->addItem($mask);*/

        $gui = ilAdministrationSettingsFormHandler::getSettingsGUIInstance("adve");
        $ne = new ilNonEditableValueGUI($lng->txt("blog_allow_html"), "", true);
        $this->ctrl->setParameter($gui, "ref_id", $gui->object->getRefId());
        $link = $this->ctrl->getLinkTarget($gui);
        $ne->setValue("<a href='$link'> >> " . $this->lng->txt("settings") . "</a>");
        $form->addItem($ne);


        return $form;
    }
}
