<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Media Cast Settings.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @ilCtrl_Calls ilObjMediaCastSettingsGUI: ilPermissionGUI
 * @ilCtrl_IsCalledBy ilObjMediaCastSettingsGUI: ilAdministrationGUI
 */
class ilObjMediaCastSettingsGUI extends ilObjectGUI
{

    /**
     * @var ilErrorHandling
     */
    protected $error;

    private static $ERROR_MESSAGE;
    /**
     * Contructor
     *
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;
        $this->error = $DIC["ilErr"];
        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->type = 'mcts';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule('mcst');
        $this->initMediaCastSettings();
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

        if (!$this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
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
        $rbacsystem = $this->rbacsystem;
        $ilAccess = $this->access;

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "mcst_edit_settings",
                $this->ctrl->getLinkTarget($this, "editSettings"),
                array("editSettings", "view")
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
    * Edit mediacast settings.
    */
    public function editSettings()
    {
        $this->tabs_gui->setTabActive('mcst_edit_settings');
        $this->initFormSettings();
        return true;
    }

    /**
    * Save mediacast settings
    */
    public function saveSettings()
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            foreach ($this->settings->getPurposeSuffixes() as $purpose => $filetypes) {
                $purposeSuffixes[$purpose] = explode(",", preg_replace("/[^\w,]/", "", strtolower($_POST[$purpose])));
            }

            $this->settings->setPurposeSuffixes($purposeSuffixes);
            $this->settings->setDefaultAccess($_POST["defaultaccess"]);
            $this->settings->setMimeTypes(explode(",", $_POST["mimetypes"]));

            $this->settings->save();

            ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
        }
        
        $ilCtrl->redirect($this, "view");
    }

    /**
    * Save mediacast settings
    */
    public function cancel()
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->redirect($this, "view");
    }
    
    /**
     * iniitialize settings storage for media cast
     *
     */
    protected function initMediaCastSettings()
    {
        $this->settings = ilMediaCastSettings::_getInstance();
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
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('settings'));
        
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $form->addCommandButton('saveSettings', $this->lng->txt('save'));
            $form->addCommandButton('cancel', $this->lng->txt('cancel'));
        }

        //Default Visibility
        $radio_group = new ilRadioGroupInputGUI($lng->txt("mcst_default_visibility"), "defaultaccess");
        $radio_option = new ilRadioOption($lng->txt("mcst_visibility_users"), "users");
        $radio_group->addOption($radio_option);
        $radio_option = new ilRadioOption($lng->txt("mcst_visibility_public"), "public");
        $radio_group->addOption($radio_option);
        $radio_group->setInfo($lng->txt("mcst_news_item_visibility_info"));
        $radio_group->setRequired(false);
        $radio_group->setValue($this->settings->getDefaultAccess());
        #$ch->addSubItem($radio_group);
        $form->addItem($radio_group);


        foreach ($this->settings->getPurposeSuffixes() as $purpose => $filetypes) {
            if ($purpose != "VideoAlternative") {
                $text = new ilTextInputGUI($lng->txt("mcst_" . strtolower($purpose) . "_settings_title"), $purpose);
                $text->setValue(implode(",", $filetypes));
                $text->setInfo($lng->txt("mcst_" . strtolower($purpose) . "_settings_info"));
                $form->addItem($text);
            }
        }
        
        $text = new ilTextAreaInputGUI($lng->txt("mcst_mimetypes"), "mimetypes");
        $text->setInfo($lng->txt("mcst_mimetypes_info"));
        $text->setCols(120);
        $text->setRows(10);
        if (is_array($this->settings->getMimeTypes())) {
            $text->setValue(implode(",", $this->settings->getMimeTypes()));
        }
        $form->addItem($text);
        
        $this->tpl->setContent($form->getHTML());
    }
}
