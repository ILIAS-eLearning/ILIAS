<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Login page editor settings GUI
 * ILIAS page editor or richtext editor
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesAuthentication
 * @ilCtrl_isCalledBy ilAuthLoginPageEditorGUI: ilObjAuthSettingsGUI
 * @ilCtrl_Calls ilAuthLoginPageEditorGUI: ilLoginPageGUI
 */
class ilAuthLoginPageEditorGUI
{
    private ilCtrl $ctrl;
    private ilLanguage $lng;
    private ilGlobalTemplateInterface $tpl;
    private ilTabsGUI $tabs;
    private ilToolbarGUI $toolbar;
    private ilRbacSystem $rbacsystem;
    private ilSetting $setting;
    private ilErrorHandling $ilErr;
    private ?ilPropertyFormGUI $form;

    private int $ref_id;
    private ilAuthLoginPageEditorSettings $settings;
    private ?ilSetting $loginSettings = null;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;

    public function __construct(int $a_ref_id)
    {
        global $DIC;
        
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->setting = $DIC->settings();
        $this->ilErr = $DIC['ilErr'];

        $this->lng = $DIC['lng'];
        
        $this->lng->loadLanguageModule('auth');
        $this->ref_id = $a_ref_id;

        $this->settings = ilAuthLoginPageEditorSettings::getInstance();
        $this->content_style_domain = $DIC->contentStyle()
            ->domain()
            ->styleForRefId($a_ref_id);
    }

    /**
     * Get Settings
     */
    public function getSettings() : ilAuthLoginPageEditorSettings
    {
        return $this->settings;
    }

    /**
     * @return int ref_id
     */
    public function getRefId() : int
    {
        return $this->ref_id;
    }

    /**
     * ilCtrl execute command
     */
    public function executeCommand() : void
    {
        switch ($this->ctrl->getNextClass($this)) {
            case 'illoginpagegui':
                $this->tabs->clearTargets();
                $this->tabs->setBackTarget(
                    $this->lng->txt('back'),
                    $this->ctrl->getLinkTarget($this, 'show'),
                    '_top'
                );

                if ($_GET["redirectSource"] !== "ilinternallinkgui") {
                    $this->forwardToPageObject();
                }
                break;

            default:
                if (!$cmd = $this->ctrl->getCmd()) {
                    $cmd = 'show';
                }
                $this->$cmd();
                break;
        }
    }


    /**
     * Forward to page editor
     */
    protected function forwardToPageObject() : void
    {
        $key = (int) $_REQUEST['key'];
        $this->ctrl->saveParameter($this, 'key');

        $this->lng->loadLanguageModule("content");

        if (!ilLoginPage::_exists('auth', $key)) {
            // doesn't exist -> create new one
            $new_page_object = new ilLoginPage();
            $new_page_object->setParentId($key);
            $new_page_object->setId($key);
            $new_page_object->createFromXML();
        }

        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath(0));
        $this->tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());


        $this->ctrl->setReturnByClass('illoginpagegui', "edit");
        $page_gui = new ilLoginPageGUI($key);

        $page_gui->setTemplateTargetVar("ADM_CONTENT");
        //TODO check what should go here $link_xml is undefined
        //$page_gui->setLinkXML($link_xml);
        //$page_gui->enableChangeComments($this->content_object->isActiveHistoryUserComments());
        //$page_gui->setFileDownloadLink($this->ctrl->getLinkTarget($this, "downloadFile"));
        //$page_gui->setFullscreenLink($this->ctrl->getLinkTarget($this, "showMediaFullscreen"));
        //$page_gui->setLinkParams($this->ctrl->getUrlParameterString()); // todo
        //		$page_gui->setSourcecodeDownloadScript($this->ctrl->getLinkTarget($this, ""));
        $page_gui->setPresentationTitle("");
        $page_gui->setStyleId($this->content_style_domain->getEffectiveStyleId());
        $page_gui->setTemplateOutput(false);
        //$page_gui->setLocator($contObjLocator);
        $page_gui->setHeader("");

        // style tab
        //$page_gui->setTabHook($this, "addPageTabs");

        if ($this->ctrl->getCmd() === 'editPage') {
            $this->ctrl->setCmd('edit');
        }
        $html = $this->ctrl->forwardCommand($page_gui);

        if ($html !== "") {
            $this->tpl->setContent($html);
        }
    }

    /**
     * Show current activated editor
     */
    protected function show() : void
    {
        $this->addEditorSwitch();

        switch ($this->getSettings()->getMode()) {
            case ilAuthLoginPageEditorSettings::MODE_RTE:
                $this->showRichtextEditor();
                break;
            case ilAuthLoginPageEditorSettings::MODE_IPE:
                $this->showIliasEditor();
                break;
        }
    }

    /**
     * Show editor switch
     */
    protected function addEditorSwitch() : void
    {
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this));
        switch ($this->getSettings()->getMode()) {
            case ilAuthLoginPageEditorSettings::MODE_RTE:

                $this->toolbar->addButton(
                    $this->lng->txt('login_page_switch_ipe'),
                    $this->ctrl->getLinkTarget($this, 'switchIPE')
                );
                break;

            case ilAuthLoginPageEditorSettings::MODE_IPE:

                $this->toolbar->addButton(
                    $this->lng->txt('login_page_switch_rte'),
                    $this->ctrl->getLinkTarget($this, 'switchRTE')
                );
                break;
        }
    }

    /**
     * Switch editor mode to ILIAS Page editor
     */
    protected function switchIPE() : void
    {
        $this->getSettings()->setMode(ilAuthLoginPageEditorSettings::MODE_IPE);
        $this->getSettings()->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('login_page_editor_switched'), true);
        $this->ctrl->redirect($this, 'show');
    }

    /**
     * Switch editor mode to richtext editor
     */
    protected function switchRTE() : void
    {
        $this->getSettings()->setMode(ilAuthLoginPageEditorSettings::MODE_RTE);
        $this->getSettings()->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('login_page_editor_switched'), true);
        $this->ctrl->redirect($this, 'show');
    }

    /**
     * Activate languages
     */
    protected function activate() : void
    {
        $settings = ilAuthLoginPageEditorSettings::getInstance();
        foreach ((array) $_POST['visible_languages'] as $lang_key) {
            $settings->enableIliasEditor($lang_key, in_array($lang_key, (array) $_POST['languages'], true));
        }
        $settings->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'show');
    }

    /**
     * Show ILIAS page editor summary.
     */
    protected function showIliasEditor() : void
    {
        $tbl = new ilAuthLoginPageEditorTableGUI($this, 'show');
        $tbl->parse();

        $this->tpl->setContent($tbl->getHTML());
    }

    /**
     * Show richtext editor
     * @global ilRbacSystem $rbacsystem
     * @global ilLanguage $lng
     * @global ilSetting $ilSetting
     * @author Michael Jansen
     */
    protected function showRichtextEditor() : void
    {
        if (!$this->rbacsystem->checkAccess("visible,read", $this->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt("permission_denied"), $this->ilErr->MESSAGE);
        }
        $this->lng->loadLanguageModule("meta");

        $this->tpl->addBlockFile(
            "ADM_CONTENT",
            "adm_content",
            "tpl.auth_login_messages.html",
            "Services/Authentication"
        );
        $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        $this->tpl->setVariable("TXT_HEADLINE", $this->lng->txt("login_information"));
        $this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("login_information_desc"));
        $this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
        $this->initLoginForm();
        $this->tpl->setVariable('LOGIN_INFO', $this->form->getHTML());
    }

    /**
     * saves the login information data
     *
     * @author Michael Jansen
     */
    protected function saveLoginInfo() : void
    {
        if (!$this->rbacsystem->checkAccess("write", $this->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt("permission_denied"), $this->ilErr->MESSAGE);
        }

        $this->initLoginForm();
        if ($this->form->checkInput()) {
            // @todo: Move settings ilAuthLoginPageSettings
            $this->loginSettings = new ilSetting("login_settings");
            foreach ($this->lng->getInstalledLanguages() as $lang_key) {
                $settingKey = "login_message_" . $lang_key;
                if ($this->form->getInput($settingKey)) {
                    $this->loginSettings->set($settingKey, $this->form->getInput($settingKey));
                }
            }
            if ($this->form->getInput('default_auth_mode')) {
                $this->setting->set('default_auth_mode', $this->form->getInput('default_auth_mode'));
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("login_information_settings_saved"), true);
        }

        $this->ctrl->redirect($this, 'show');
    }

    /**
     * Init login form
     */
    protected function initLoginForm() : void
    {
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this, 'saveLoginInfo'));
        $this->form->setTableWidth('80%');
        $this->form->setTitle($this->lng->txt('login_information'));

        $this->form->addCommandButton('saveLoginInfo', $this->lng->txt('save'));

        $rad_settings = ilRadiusSettings::_getInstance();
        if (($ldap_id = ilLDAPServer::_getFirstActiveServer()) || $rad_settings->isActive()) {
            $select = new ilSelectInputGUI($this->lng->txt('default_auth_mode'), 'default_auth_mode');
            $select->setValue($this->setting->get('default_auth_mode', (string) ilAuthUtils::AUTH_LOCAL));
            $select->setInfo($this->lng->txt('default_auth_mode_info'));
            $options[ilAuthUtils::AUTH_LOCAL] = $this->lng->txt('auth_local');
            if ($ldap_id) {
                $options[ilAuthUtils::AUTH_LDAP] = $this->lng->txt('auth_ldap');
            }
            if ($rad_settings->isActive()) {
                $options [ilAuthUtils::AUTH_RADIUS] = $this->lng->txt('auth_radius');
            }
            $select->setOptions($options);
            $this->form->addItem($select);
        }

        if (!is_object($this->loginSettings)) {
            $this->loginSettings = new ilSetting("login_settings");
        }

        $login_settings = $this->loginSettings->getAll();
        $languages = $this->lng->getInstalledLanguages();
        $def_language = $this->lng->getDefaultLanguage();

        foreach ($this->setDefLangFirst($def_language, $languages) as $lang_key) {
            $add = "";
            if ($lang_key === $def_language) {
                $add = " (" . $this->lng->txt("default") . ")";
            }

            $textarea = new ilTextAreaInputGUI(
                $this->lng->txt("meta_l_" . $lang_key) . $add,
                'login_message_' . $lang_key
            );
            $textarea->setRows(10);
            $msg_login_lang = "login_message_" . $lang_key;
            if (isset($login_settings[$msg_login_lang])) {
                $textarea->setValue($login_settings[$msg_login_lang]);
            }
            $textarea->setUseRte(true);
            $textarea->setRteTagSet("extended");
            $this->form->addItem($textarea);

            unset($login_settings["login_message_" . $lang_key]);
        }

        foreach ($login_settings as $key => $message) {
            $lang_key = substr($key, strrpos($key, "_") + 1, strlen($key) - strrpos($key, "_"));

            $textarea = new ilTextAreaInputGUI(
                $this->lng->txt("meta_l_" . $lang_key),
                'login_message_' . $lang_key
            );
            $textarea->setRows(10);
            $textarea->setValue($message);
            $textarea->setUseRte(true);
            $textarea->setRteTagSet("extended");
            if (!in_array($lang_key, $languages, true)) {
                $textarea->setAlert($this->lng->txt("not_installed"));
            }
            $this->form->addItem($textarea);
        }
    }

    /**
     * returns an array of all installed languages, default language at the first position
     * @param string $a_def_language Default language of the current installation
     * @param array $a_languages Array of all installed languages
     * @return array $languages Array of the installed languages, default language at first position or
     *         an empty array, if $a_a_def_language is empty
     * @author Michael Jansen
     */
    private function setDefLangFirst(string $a_def_language, array $a_languages) : array
    {
        $languages = [];
        if ($a_def_language !== "") {
            $languages[] = $a_def_language;

            foreach ($a_languages as $val) {
                if (!in_array($val, $languages, true)) {
                    $languages[] = $val;
                }
            }
        }

        return $languages;
    }
}
