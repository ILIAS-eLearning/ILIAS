<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/classes/class.ilAuthLoginPageEditorSettings.php';

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
    /**
     * @var $ctrl ilCtrl
     */
    protected $ctrl = null;
    protected $lng = null;

    private $ref_id = 0;
    private $settings = null;



    /**
     * Constructor
     * @param int $a_ref_id
     * @global ilCtrl ilCtrl
     */
    public function __construct($a_ref_id)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->lng->loadLanguageModule('auth');
        $this->ref_id = $a_ref_id;

        $this->settings = ilAuthLoginPageEditorSettings::getInstance();
    }

    /**
     * Get Settings
     * @return ilAuthLoginPageEditorSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return int ref_id
     */
    public function getRefId()
    {
        return $this->ref_id;
    }

    /**
     * ilCtrl execute command
     */
    public function executeCommand()
    {
        switch ($this->ctrl->getNextClass($this)) {
            case 'illoginpagegui':
                $GLOBALS['DIC']['ilTabs']->clearTargets();
                $GLOBALS['DIC']['ilTabs']->setBackTarget(
                    $this->lng->txt('back'),
                    $this->ctrl->getLinkTarget($this, 'show'),
                    '_top'
                );

                if ($_GET["redirectSource"] != "ilinternallinkgui") {
                    $this->forwardToPageObject();
                } else {
                    return '';
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
    protected function forwardToPageObject()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];

        $key = (int) $_REQUEST['key'];
        $this->ctrl->saveParameter($this, 'key', $key);

        include_once("./Services/Authentication/classes/class.ilLoginPage.php");
        include_once("./Services/Authentication/classes/class.ilLoginPageGUI.php");
        include_once './Services/Style/Content/classes/class.ilObjStyleSheet.php';

        $lng->loadLanguageModule("content");

        if (!ilLoginPage::_exists('auth', $key)) {
            // doesn't exist -> create new one
            $new_page_object = new ilLoginPage();
            $new_page_object->setParentId($key);
            $new_page_object->setId($key);
            $new_page_object->createFromXML();
        }

        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
        $tpl->setCurrentBlock("SyntaxStyle");
        $tpl->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
        $tpl->parseCurrentBlock();


        $this->ctrl->setReturnByClass('illoginpagegui', "edit");
        $page_gui = new ilLoginPageGUI($key);

        $page_gui->setTemplateTargetVar("ADM_CONTENT");
        $page_gui->setLinkXML($link_xml);
        //$page_gui->enableChangeComments($this->content_object->isActiveHistoryUserComments());
        //$page_gui->setFileDownloadLink($this->ctrl->getLinkTarget($this, "downloadFile"));
        //$page_gui->setFullscreenLink($this->ctrl->getLinkTarget($this, "showMediaFullscreen"));
        //$page_gui->setLinkParams($this->ctrl->getUrlParameterString()); // todo
        //		$page_gui->setSourcecodeDownloadScript($this->ctrl->getLinkTarget($this, ""));
        $page_gui->setPresentationTitle("");
        $page_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(0));
        $page_gui->setTemplateOutput(false);
        //$page_gui->setLocator($contObjLocator);
        $page_gui->setHeader("");

        // style tab
        //$page_gui->setTabHook($this, "addPageTabs");

        if ($this->ctrl->getCmd() == 'editPage') {
            $this->ctrl->setCmd('edit');
        }
        $html = $this->ctrl->forwardCommand($page_gui);

        $tpl->setContent($html);
    }

    /**
     * Show current activated editor
     * @return void
     */
    protected function show()
    {
        $this->addEditorSwitch();

        switch ($this->getSettings()->getMode()) {
            case ilAuthLoginPageEditorSettings::MODE_RTE:
                return $this->showRichtextEditor();

            case ilAuthLoginPageEditorSettings::MODE_IPE:
                return $this->showIliasEditor();
        }
    }

    /**
     * Show editore switch
     * @global ilToolbarGUI $ilToolbar
     */
    protected function addEditorSwitch()
    {
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];

        $ilToolbar->setFormAction($this->ctrl->getFormAction($this));
        switch ($this->getSettings()->getMode()) {
            case ilAuthLoginPageEditorSettings::MODE_RTE:

                $ilToolbar->addButton(
                    $this->lng->txt('login_page_switch_ipe'),
                    $this->ctrl->getLinkTarget($this, 'switchIPE')
                );
                break;

            case ilAuthLoginPageEditorSettings::MODE_IPE:

                $ilToolbar->addButton(
                    $this->lng->txt('login_page_switch_rte'),
                    $this->ctrl->getLinkTarget($this, 'switchRTE')
                );
                break;
        }
        return;
    }

    /**
     * Switch editor mode
     */
    protected function switchIPE()
    {
        $this->getSettings()->setMode(ilAuthLoginPageEditorSettings::MODE_IPE);
        $this->getSettings()->update();

        ilUtil::sendSuccess($this->lng->txt('login_page_editor_switched'), true);
        $this->ctrl->redirect($this, 'show');
    }

    /**
     * Switch editor mode
     */
    protected function switchRTE()
    {
        $this->getSettings()->setMode(ilAuthLoginPageEditorSettings::MODE_RTE);
        $this->getSettings()->update();

        ilUtil::sendSuccess($this->lng->txt('login_page_editor_switched'), true);
        $this->ctrl->redirect($this, 'show');
    }

    /**
     * Activate languages
     */
    protected function activate()
    {
        $settings = ilAuthLoginPageEditorSettings::getInstance();
        foreach ((array) $_POST['visible_languages'] as $lang_key) {
            $settings->enableIliasEditor($lang_key, in_array($lang_key, (array) $_POST['languages']));
        }
        $settings->update();

        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'show');
    }

    /**
     * Show ILIAS page editor summary.
     */
    protected function showIliasEditor()
    {
        global $DIC;

        $tpl = $DIC['tpl'];

        include_once './Services/Authentication/classes/class.ilAuthLoginPageEditorTableGUI.php';
        $tbl = new ilAuthLoginPageEditorTableGUI($this, 'show');
        $tbl->parse();

        $tpl->setContent($tbl->getHTML());
    }

    /**
     * Show richtext editor
     * @global ilRbacSystem $rbacsystem
     * @global ilLanguage $lng
     * @global ilSetting $ilSetting
     * @author Michael Jansen
     */
    protected function showRichtextEditor()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $lng = $DIC['lng'];
        $ilSetting = $DIC['ilSetting'];
        $tpl = $DIC['tpl'];

        if (!$rbacsystem->checkAccess("visible,read", $this->getRefId())) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }
        $lng->loadLanguageModule("meta");

        $tpl->addBlockFile(
            "ADM_CONTENT",
            "adm_content",
            "tpl.auth_login_messages.html",
            "Services/Authentication"
        );
        $tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        $tpl->setVariable("TXT_HEADLINE", $this->lng->txt("login_information"));
        $tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("login_information_desc"));
        $tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
        $this->initLoginForm();
        $tpl->setVariable('LOGIN_INFO', $this->form->getHTML());
    }

    /**
     * saves the login information data
     *
     * @access protected
     * @author Michael Jansen
     */
    protected function saveLoginInfo()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $lng = $DIC['lng'];
        $ilSetting = $DIC['ilSetting'];

        if (!$rbacsystem->checkAccess("write", $this->getRefId())) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        $this->initLoginForm();
        if ($this->form->checkInput()) {
            if (is_array($_POST)) {
                // @todo: Move settings ilAuthLoginPageSettings
                $this->loginSettings = new ilSetting("login_settings");

                foreach ($_POST as $key => $val) {
                    if (substr($key, 0, 14) == "login_message_") {
                        $this->loginSettings->set($key, $val);
                    }
                }
            }

            if ($_POST['default_auth_mode']) {
                $ilSetting->set('default_auth_mode', (int) $_POST['default_auth_mode']);
            }

            ilUtil::sendSuccess($this->lng->txt("login_information_settings_saved"), true);
        }

        $this->ctrl->redirect($this, 'show');
    }

    /**
     * Init login form
     */
    protected function initLoginForm()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $lng = $DIC['lng'];
        $ilSetting = $DIC['ilSetting'];

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this, 'saveLoginInfo'));
        $this->form->setTableWidth('80%');
        $this->form->setTitle($this->lng->txt('login_information'));

        $this->form->addCommandButton('saveLoginInfo', $this->lng->txt('save'));

        include_once('Services/LDAP/classes/class.ilLDAPServer.php');
        include_once('Services/Radius/classes/class.ilRadiusSettings.php');
        $rad_settings = ilRadiusSettings::_getInstance();
        if ($ldap_id = ilLDAPServer::_getFirstActiveServer() or $rad_settings->isActive()) {
            $select = new ilSelectInputGUI($this->lng->txt('default_auth_mode'), 'default_auth_mode');
            $select->setValue($ilSetting->get('default_auth_mode', AUTH_LOCAL));
            $select->setInfo($this->lng->txt('default_auth_mode_info'));
            $options[AUTH_LOCAL] = $this->lng->txt('auth_local');
            if ($ldap_id) {
                $options[AUTH_LDAP] = $this->lng->txt('auth_ldap');
            }
            if ($rad_settings->isActive()) {
                $options [AUTH_RADIUS] = $this->lng->txt('auth_radius');
            }
            $select->setOptions($options);
            $this->form->addItem($select);
        }

        if (!is_object($this->loginSettings)) {
            $this->loginSettings = new ilSetting("login_settings");
        }

        $login_settings = $this->loginSettings->getAll();
        $languages = $lng->getInstalledLanguages();
        $def_language = $lng->getDefaultLanguage();

        foreach ($this->setDefLangFirst($def_language, $languages) as $lang_key) {
            $add = "";
            if ($lang_key == $def_language) {
                $add = " (" . $lng->txt("default") . ")";
            }

            $textarea = new ilTextAreaInputGUI(
                $lng->txt("meta_l_" . $lang_key) . $add,
                'login_message_' . $lang_key
            );
            $textarea->setRows(10);
            $textarea->setValue($login_settings["login_message_" . $lang_key]);
            $textarea->setUseRte(true);
            $textarea->setRteTagSet("extended");
            $this->form->addItem($textarea);

            unset($login_settings["login_message_" . $lang_key]);
        }

        foreach ($login_settings as $key => $message) {
            $lang_key = substr($key, strrpos($key, "_") + 1, strlen($key) - strrpos($key, "_"));

            $textarea = new ilTextAreaInputGUI(
                $lng->txt("meta_l_" . $lang_key) . $add,
                'login_message_' . $lang_key
            );
            $textarea->setRows(10);
            $textarea->setValue($message);
            $textarea->setUseRte(true);
            $textarea->setRteTagSet("extended");
            if (!in_array($lang_key, $languages)) {
                $textarea->setAlert($lng->txt("not_installed"));
            }
            $this->form->addItem($textarea);
        }
    }

    /**
     *
     * returns an array of all installed languages, default language at the first position
     *
     * @param string $a_def_language Default language of the current installation
     * @param array $a_languages Array of all installed languages
     * @return array $languages Array of the installed languages, default language at first position
     * @access public
     * @author Michael Jansen
     *
     */
    protected function setDefLangFirst($a_def_language, $a_languages)
    {
        if (is_array($a_languages) && $a_def_language != "") {
            $languages = array();
            $languages[] = $a_def_language;

            foreach ($a_languages as $val) {
                if (!in_array($val, $languages)) {
                    $languages[] = $val;
                }
            }

            return $languages;
        } else {
            return array();
        }
    }
}
