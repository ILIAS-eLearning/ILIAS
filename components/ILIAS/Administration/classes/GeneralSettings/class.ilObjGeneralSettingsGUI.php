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

declare(strict_types=1);

use ILIAS\Administration\HeaderTitleGUI;
use ILIAS\Administration\HeaderTitleRepo;
use ILIAS\Administration\ContactInformationGUI;
use ILIAS\UICore\GlobalTemplate;

/**
 * @ilCtrl_isCalledBy ilObjGeneralSettingsGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjGeneralSettingsGUI: ilPermissionGUI
 */
class ilObjGeneralSettingsGUI extends ilObject2GUI
{
    public function getType(): string
    {
        return ilObjGeneralSettings::TYPE;
    }

    private function getHeaderTitleGUI(): HeaderTitleGUI
    {
        return new HeaderTitleGUI(
            $this->ctrl,
            $this->tpl,
            $this->lng,
            $this->http->request(),
            new HeaderTitleRepo(),
            $this->checkPermissionBool('write')
        );
    }

    private function getContactInformationGUI(): ContactInformationGUI
    {
        return new ContactInformationGUI(
            $this->ctrl,
            $this->tpl,
            $this->lng,
            $this->settings,
            $this->checkPermissionBool('write')
        );
    }

    public function executeCommand(): void
    {
        $this->checkPermission('read');

        $this->lng->loadLanguageModule('adm');
        $this->prepareOutput();

        switch ($this->ctrl->getNextClass($this)) {
            case strtolower(ilPermissionGUI::class):
                $this->tabs_gui->activateTab('perm_settings');
                $this->ctrl->forwardCommand(new ilPermissionGUI($this));
                break;

            case strtolower(HeaderTitleGUI::class):
                $this->tabs_gui->activateTab('header_title');
                $this->ctrl->forwardCommand($this->getHeaderTitleGUI());
                break;

            case strtolower(ContactInformationGUI::class):
                $this->tabs_gui->activateTab('contact_data');
                $this->ctrl->forwardCommand($this->getContactInformationGUI());
                break;

            default:
                $this->tabs_gui->activateTab('basic_settings');

                $cmd = $this->ctrl->getCmd("view");
                switch ($cmd) {
                    case 'view':
                        $this->view();
                        break;

                    case 'update':
                        $this->checkPermission('write');
                        $this->update();
                        break;
                }
        }
    }

    public function getAdminTabs(): void
    {
        $this->tabs_gui->addTab(
            'basic_settings',
            $this->lng->txt('basic_settings'),
            $this->ctrl->getLinkTarget($this)
        );

        $this->tabs_gui->addTab(
            'header_title',
            $this->lng->txt('header_title'),
            $this->ctrl->getLinkTargetByClass([HeaderTitleGUI::class]),
        );

        $this->tabs_gui->addTab(
            'contact_data',
            $this->lng->txt('contact_data'),
            $this->ctrl->getLinkTargetByClass([ContactInformationGUI::class]),
        );

        if ($this->checkPermissionBool('edit_permission')) {
            $this->tabs_gui->addTab(
                'perm_settings',
                $this->lng->txt('perm_settings'),
                $this->ctrl->getLinkTargetByClass([self::class, ilPermissionGUI::class], 'perm')
            );
        }
    }

    private function setSubTabs(): void
    {
    }

    public function view(): void
    {
        $this->tpl->setContent($this->buildForm()->getHTML());
    }

    public function update(): void
    {
        $form = $this->buildForm();
        if ($form->checkInput()) {
            $this->settings->set("short_inst_name", $form->getInput("short_inst_name"));

            $public_section = ilPublicSectionSettings::getInstance();
            $public_section->setEnabled((bool) $form->getInput('pub_section'));

            $domains = [];
            foreach ((array) $form->getInput('public_section_domains') as $domain) {
                if (strlen(trim($domain)) !== 0) {
                    $domains[] = $domain;
                }
            }
            $public_section->setDomains($domains);
            $public_section->save();

            $global_profiles = ($form->getInput("pub_section"))
                ? (int) $form->getInput('enable_global_profiles')
                : 0;
            $this->settings->set('enable_global_profiles', (string) $global_profiles);

            $this->settings->set("open_google", $form->getInput("open_google"));
            $this->settings->set("locale", $form->getInput("locale"));

            $this->tpl->setOnScreenMessage(GlobalTemplate::MESSAGE_TYPE_SUCCESS, $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this);
        }

        $form->setValuesByPost();
        $this->tpl->setContent($form->getHtml());
    }

    private function buildForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $this->lng->loadLanguageModule("pd");

        // installation short title
        $ti = new ilTextInputGUI($this->lng->txt("short_inst_name"), "short_inst_name");
        $ti->setMaxLength(200);
        $ti->setSize(40);
        $ti->setValue($this->settings->get("short_inst_name"));
        $ti->setInfo($this->lng->txt("short_inst_name_info"));
        $form->addItem($ti);

        $cb = new ilCheckboxInputGUI($this->lng->txt("pub_section"), "pub_section");
        $cb->setInfo($this->lng->txt("pub_section_info"));
        if (ilPublicSectionSettings::getInstance()->isEnabled()) {
            $cb->setChecked(true);
        }
        $form->addItem($cb);

        $this->lng->loadLanguageModule('administration');
        $domains = new ilTextInputGUI($this->lng->txt('adm_pub_section_domain_filter'), 'public_section_domains');
        $domains->setInfo($this->lng->txt('adm_pub_section_domain_filter_info'));
        $domains->setMulti(true);
        $domains->setValue(current(ilPublicSectionSettings::getInstance()->getDomains()));
        $domains->setMultiValues(ilPublicSectionSettings::getInstance()->getDomains());

        $cb->addSubItem($domains);

        // Enable Global Profiles
        $cb_prop = new ilCheckboxInputGUI($this->lng->txt('pd_enable_user_publish'), 'enable_global_profiles');
        $cb_prop->setInfo($this->lng->txt('pd_enable_user_publish_info'));
        $cb_prop->setChecked((bool) $this->settings->get('enable_global_profiles'));
        $cb->addSubItem($cb_prop);

        // search engine
        $robot_settings = ilRobotSettings::getInstance();
        $cb2 = new ilCheckboxInputGUI($this->lng->txt("search_engine"), "open_google");
        $cb2->setInfo($this->lng->txt("enable_search_engine"));
        $form->addItem($cb2);

        if (!$robot_settings->checkRewrite()) {
            $cb2->setAlert($this->lng->txt("allow_override_alert"));
            $cb2->setChecked(false);
            $cb2->setDisabled(true);
        } elseif ($this->settings->get("open_google")) {
            $cb2->setChecked(true);
        }

        // locale
        $ti = new ilTextInputGUI($this->lng->txt("adm_locale"), "locale");
        $ti->setMaxLength(80);
        $ti->setSize(40);
        $ti->setInfo($this->lng->txt("adm_locale_info"));
        $ti->setValue($this->settings->get("locale"));
        $form->addItem($ti);

        // save and cancel commands
        if ($this->checkPermissionBool('write')) {
            $form->addCommandButton("update", $this->lng->txt("save"));
        }

        $form->setTitle($this->lng->txt("basic_settings"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

}
