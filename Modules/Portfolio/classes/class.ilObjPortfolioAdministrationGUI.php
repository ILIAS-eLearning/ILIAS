<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");

/**
* Portfolio Administration Settings.
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id:$
*
* @ilCtrl_Calls ilObjPortfolioAdministrationGUI: ilPermissionGUI
* @ilCtrl_IsCalledBy ilObjPortfolioAdministrationGUI: ilAdministrationGUI
*
* @ingroup ModulesPortfolio
*/
class ilObjPortfolioAdministrationGUI extends ilObjectGUI
{
    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @var ilPortfolioDeclarationOfAuthorship
     */
    protected $declaration_authorship;

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
        $this->type = "prfa";
        $this->ui = $DIC->ui();
        $this->request = $DIC->http()->request();
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->declaration_authorship = new ilPortfolioDeclarationOfAuthorship();

        $this->lng->loadLanguageModule("prtf");
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
                $this->tabs_gui->activateTab('perm_settings');
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
        $lng = $this->lng;
        $tabs = $this->tabs_gui;


        if ($this->checkPermissionBool("visible,read")) {
            $tabs->addTab(
                "settings",
                $lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "editSettings")
            );
            $tabs->addTab(
                "authorship",
                $lng->txt("prtf_decl_authorship"),
                $this->ctrl->getLinkTarget($this, "editDeclarationOfAuthorship")
            );
        }

        if ($this->checkPermissionBool('edit_permission')) {
            $tabs->addTab(
                "perm_settings",
                $lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
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
        
        $this->tabs_gui->activateTab('settings');
        
        /*
        if ($ilSetting->get('user_portfolios'))
        {
            ilUtil::sendInfo($lng->txt("prtf_admin_toggle_info"));
        }
        else
        {
            ilUtil::sendInfo($lng->txt("prtf_admin_inactive_info"));
        }
        */
        
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
        $ilSetting = $this->settings;
        
        $this->checkPermission("write");
        
        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            $ilSetting->set('user_portfolios', (int) $form->getInput("prtf"));
            
            $banner = (bool) $form->getInput("banner");
            
            $prfa_set = new ilSetting("prfa");
            $prfa_set->set("pd_block", (bool) $form->getInput("pd_block"));
            $prfa_set->set("banner", $banner);
            $prfa_set->set("banner_width", (int) $form->getInput("width"));
            $prfa_set->set("banner_height", (int) $form->getInput("height"));
            $prfa_set->set("mask", (bool) $form->getInput("mask"));
            $prfa_set->set("mycrs", (bool) $form->getInput("mycrs"));
            
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
        $ilSetting = $this->settings;
        $ilAccess = $this->access;
        
        include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('prtf_settings'));
        
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $form->addCommandButton('saveSettings', $this->lng->txt('save'));
            $form->addCommandButton('cancel', $this->lng->txt('cancel'));
        }
        
        // Enable 'Portfolios'
        $lng->loadLanguageModule('pd');
        $lng->loadLanguageModule('user');
        $prtf_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_prtf'), 'prtf');
        $prtf_prop->setValue('1');
        $prtf_prop->setInfo($lng->txt('user_portfolios_desc'));
        $prtf_prop->setChecked(($ilSetting->get('user_portfolios') ? '1' : '0'));
        $form->addItem($prtf_prop);

        $prfa_set = new ilSetting("prfa");

        $pdblock = new ilCheckboxInputGUI($lng->txt("prtf_pd_block"), "pd_block");
        $pdblock->setInfo($lng->txt("prtf_pd_block_info"));
        $pdblock->setChecked($prfa_set->get("pd_block", false));
        $form->addItem($pdblock);

        $banner = new ilCheckboxInputGUI($lng->txt("prtf_preview_banner"), "banner");
        $banner->setInfo($lng->txt("prtf_preview_banner_info"));
        $form->addItem($banner);
        
        $width = new ilNumberInputGUI($lng->txt("prtf_preview_banner_width"), "width");
        $width->setRequired(true);
        $width->setSize(4);
        $banner->addSubItem($width);
        
        $height = new ilNumberInputGUI($lng->txt("prtf_preview_banner_height"), "height");
        $height->setRequired(true);
        $height->setSize(4);
        $banner->addSubItem($height);

        $banner->setChecked($prfa_set->get("banner", false));
        if ($prfa_set->get("banner")) {
            $width->setValue($prfa_set->get("banner_width"));
            $height->setValue($prfa_set->get("banner_height"));
        } else {
            $width->setValue(1370);
            $height->setValue(100);
        }

        /*
        $mask = new ilCheckboxInputGUI($lng->txt("prtf_allow_html"), "mask");
        $mask->setInfo($lng->txt("prtf_allow_html_info"));
        $mask->setChecked($prfa_set->get("mask", false));
        $form->addItem($mask);*/

        $gui = ilAdministrationSettingsFormHandler::getSettingsGUIInstance("adve");
        $ne = new ilNonEditableValueGUI($lng->txt("prtf_allow_html"), "", true);
        $this->ctrl->setParameter($gui, "ref_id", $gui->object->getRefId());
        $link = $this->ctrl->getLinkTarget($gui);
        $ne->setValue("<a href='$link'> >> ".$this->lng->txt("settings")."</a>");
        $form->addItem($ne);
        
        $mycourses = new ilCheckboxInputGUI($lng->txt("prtf_allow_my_courses"), "mycrs");
        $mycourses->setInfo($lng->txt("prtf_allow_my_courses_info"));
        $mycourses->setChecked($prfa_set->get("mycrs", true));
        $form->addItem($mycourses);

        return $form;
    }
    
    public function addToExternalSettingsForm($a_form_id)
    {
        $ilSetting = $this->settings;
        
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_WSP:
                                
                $fields = array('pd_enable_prtf' => array($ilSetting->get('user_portfolios'), ilAdministrationSettingsFormHandler::VALUE_BOOL));
                
                return array(array("editSettings", $fields));
        }
    }

    //
    // Declaration of authorship
    //

    /**
     * Edit declaration of authorship
     */
    protected function editDeclarationOfAuthorship()
    {
        $main_tpl = $this->tpl;
        $renderer = $ui = $this->ui->renderer();
        $form = $this->initAuthorshipForm();

        $this->tabs_gui->activateTab("authorship");

        $main_tpl->setContent($renderer->render($form));
    }

    /**
     * Init authorship form.
     * @return \ILIAS\UI\Component\Input\Container\Form\Standard
     */
    public function initAuthorshipForm()
    {
        $ui = $this->ui;
        $f = $ui->factory();
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $lng->loadLanguageModule("meta");

        foreach ($lng->getInstalledLanguages() as $l) {
            $txt = $lng->txt("meta_l_" . $l);
            if ($lng->getDefaultLanguage() == $l) {
                $txt.= " (" . $lng->txt("default") . ")";
            }
            $fields["decl_" . $l] = $f->input()->field()->textarea($txt)
                ->withRequired(false)
                ->withValue((string) $this->declaration_authorship->getForLanguage($l));
        }

        // section
        $section1 = $f->input()->field()->section($fields, $lng->txt("prtf_decl_authorship"));

        $form_action = $ctrl->getLinkTarget($this, "saveAuthorship");
        return $f->input()->container()->form()->standard($form_action, ["sec" => $section1]);
    }

    /**
     * Save authorship
     */
    public function saveAuthorship()
    {
        $request = $this->request;
        $form = $this->initAuthorshipForm();
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        if ($this->checkPermissionBool("write")) {
            if ($request->getMethod() == "POST") {
                $form = $form->withRequest($request);
                $data = $form->getData();
                if (is_array($data["sec"])) {
                    foreach ($lng->getInstalledLanguages() as $l) {
                        $this->declaration_authorship->setForLanguage($l, $data["sec"]["decl_" . $l]);
                    }

                    ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
                }
            }
        } else {
            ilUtil::sendFailure($lng->txt("msg_no_perm_write"), true);
        }
        $ctrl->redirect($this, "editDeclarationOfAuthorship");
    }
}
