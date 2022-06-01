<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Portfolio Administration Settings.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilObjPortfolioAdministrationGUI: ilPermissionGUI, ilPortfolioRoleAssignmentGUI
 * @ilCtrl_IsCalledBy ilObjPortfolioAdministrationGUI: ilAdministrationGUI
 */
class ilObjPortfolioAdministrationGUI extends ilObjectGUI
{
    protected \ILIAS\DI\UIServices $ui;
    protected ilPortfolioDeclarationOfAuthorship$declaration_authorship;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->rbac_system = $DIC->rbac()->system();
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

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->activateTab('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilportfolioroleassignmentgui':
                $this->tabs_gui->activateTab('role_assignment');
                $gui = new ilPortfolioRoleAssignmentGUI();
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                if (!$cmd || $cmd === 'view') {
                    $cmd = "editSettings";
                }

                $this->$cmd();
                break;
        }
    }

    public function getAdminTabs() : void
    {
        $lng = $this->lng;
        $tabs = $this->tabs_gui;


        if ($this->hasReadPermission()) {
            $tabs->addTab(
                "settings",
                $lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "editSettings")
            );
            $tabs->addTab(
                "role_assignment",
                $lng->txt("prtf_role_assignment"),
                $this->ctrl->getLinkTargetByClass("ilPortfolioRoleAssignmentGUI", "")
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

    public function editSettings($a_form = null) : void
    {
        $this->tabs_gui->activateTab('settings');
        if (!$a_form) {
            $a_form = $this->initFormSettings();
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    public function saveSettings() : void
    {
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        
        if ($this->hasWritePermission()) {
            $form = $this->initFormSettings();
            if ($form->checkInput()) {
                $ilSetting->set('user_portfolios', (int) $form->getInput("prtf"));

                $banner = (bool) $form->getInput("banner");

                $prfa_set = new ilSetting("prfa");
                $prfa_set->set("banner", $banner);
                $prfa_set->set("banner_width", (int) $form->getInput("width"));
                $prfa_set->set("banner_height", (int) $form->getInput("height"));
                $prfa_set->set("mask", (bool) $form->getInput("mask"));
                $prfa_set->set("mycrs", (bool) $form->getInput("mycrs"));

                $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
                $ilCtrl->redirect($this, "editSettings");
            }
            $form->setValuesByPost();
            $this->editSettings($form);
        }
    }

    public function cancel() : void
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->redirect($this, "view");
    }

    protected function hasWritePermission() : bool
    {
        return $this->rbac_system->checkAccess("write", $this->object->getRefId());
    }

    protected function hasReadPermission() : bool
    {
        return $this->rbac_system->checkAccess("read", $this->object->getRefId());
    }

    protected function initFormSettings() : ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilSetting = $this->settings;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('prtf_settings'));
        
        if ($this->hasWritePermission()) {
            $form->addCommandButton('saveSettings', $this->lng->txt('save'));
            $form->addCommandButton('cancel', $this->lng->txt('cancel'));
        }
        
        // Enable 'Portfolios'
        $lng->loadLanguageModule('pd');
        $lng->loadLanguageModule('user');
        $prtf_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_prtf'), 'prtf');
        $prtf_prop->setValue('1');
        $prtf_prop->setInfo($lng->txt('user_portfolios_desc'));
        $prtf_prop->setChecked((bool) $ilSetting->get('user_portfolios'));
        $form->addItem($prtf_prop);

        $prfa_set = new ilSetting("prfa");

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
        $this->ctrl->setParameter($gui, "ref_id", $gui->getObject()->getRefId());
        $link = $this->ctrl->getLinkTarget($gui);
        $ne->setValue("<a href='$link'> >> " . $this->lng->txt("settings") . "</a>");
        $form->addItem($ne);
        
        $mycourses = new ilCheckboxInputGUI($lng->txt("prtf_allow_my_courses"), "mycrs");
        $mycourses->setInfo($lng->txt("prtf_allow_my_courses_info"));
        $mycourses->setChecked($prfa_set->get("mycrs", true));
        $form->addItem($mycourses);

        return $form;
    }
    
    public function addToExternalSettingsForm(int $a_form_id) : array
    {
        $ilSetting = $this->settings;

        if ($a_form_id === ilAdministrationSettingsFormHandler::FORM_WSP) {
            $fields = array('pd_enable_prtf' => array($ilSetting->get('user_portfolios'),
                                                      ilAdministrationSettingsFormHandler::VALUE_BOOL
            )
            );

            return array(array("editSettings", $fields));
        }
        return [];
    }

    //
    // Declaration of authorship
    //

    protected function editDeclarationOfAuthorship() : void
    {
        $main_tpl = $this->tpl;
        $renderer = $ui = $this->ui->renderer();
        $form = $this->initAuthorshipForm();

        $this->tabs_gui->activateTab("authorship");

        $main_tpl->setContent($renderer->render($form));
    }

    public function initAuthorshipForm() : \ILIAS\UI\Component\Input\Container\Form\Standard
    {
        $ui = $this->ui;
        $f = $ui->factory();
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $lng->loadLanguageModule("meta");
        $fields = [];

        foreach ($lng->getInstalledLanguages() as $l) {
            $txt = $lng->txt("meta_l_" . $l);
            if ($lng->getDefaultLanguage() == $l) {
                $txt .= " (" . $lng->txt("default") . ")";
            }
            $fields["decl_" . $l] = $f->input()->field()->textarea($txt)
                ->withRequired(false)
                ->withValue($this->declaration_authorship->getForLanguage($l));
        }

        // section
        $section1 = $f->input()->field()->section($fields, $lng->txt("prtf_decl_authorship"), "");

        $form_action = $ctrl->getLinkTarget($this, "saveAuthorship");
        return $f->input()->container()->form()->standard($form_action, ["sec" => $section1]);
    }

    public function saveAuthorship() : void
    {
        $request = $this->request;
        $form = $this->initAuthorshipForm();
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        if ($this->hasWritePermission()) {
            if ($request->getMethod() === "POST") {
                $form = $form->withRequest($request);
                $data = $form->getData();
                if (is_array($data["sec"])) {
                    foreach ($lng->getInstalledLanguages() as $l) {
                        $this->declaration_authorship->setForLanguage($l, $data["sec"]["decl_" . $l]);
                    }

                    $this->tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
                }
            }
        } else {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("msg_no_perm_write"), true);
        }
        $ctrl->redirect($this, "editDeclarationOfAuthorship");
    }
}
