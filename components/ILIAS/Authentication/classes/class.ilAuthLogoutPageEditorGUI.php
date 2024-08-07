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

/**
 * @ilCtrl_isCalledBy ilAuthLogoutPageEditorGUI: ilObjAuthSettingsGUI
 * @ilCtrl_Calls      ilAuthLogoutPageEditorGUI: ilLogoutPageGUI
 */
class ilAuthLogoutPageEditorGUI
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
    private \ILIAS\HTTP\Services $http;
    private \ILIAS\Refinery\Factory $refinery;
    private \ILIAS\UI\Factory $ui_factory;
    protected \ILIAS\UI\Renderer $ui_renderer;

    private int $ref_id;
    private ilAuthLogoutPageEditorSettings $settings;
    private ?ilSetting $logoutSettings = null;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;

    //variables from requests
    private ?string $redirect_source = null;
    private ?int $key = null;
    private array $visible_languages = [];
    private array $languages = [];

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

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->lng = $DIC['lng'];

        $this->lng->loadLanguageModule('auth');
        $this->ref_id = $a_ref_id;

        $this->settings = ilAuthLogoutPageEditorSettings::getInstance();
        $this->content_style_domain = $DIC->contentStyle()
                                          ->domain()
                                          ->styleForRefId($a_ref_id);

        $query_wrapper = $DIC->http()->wrapper()->query();
        $post_wrapper = $DIC->http()->wrapper()->post();
        $is_post_request = $DIC->http()->request()->getMethod() === "POST";
        $refinery = $DIC->refinery();

        if ($query_wrapper->has("redirectSource")) {
            $this->redirect_source = $query_wrapper->retrieve("redirectSource", $refinery->kindlyTo()->string());
        }

        if ($post_wrapper->has("key")) {
            $this->key = $post_wrapper->retrieve("key", $refinery->kindlyTo()->int());
        } elseif ($query_wrapper->has("key")) {
            $this->key = $query_wrapper->retrieve("key", $refinery->kindlyTo()->int());
        }

        if ($is_post_request) {
            if ($post_wrapper->has("visible_languages")) {
                $this->visible_languages = $post_wrapper->retrieve(
                    "visible_languages", $refinery->kindlyTo()->listOf($refinery->kindlyTo()->string())
                );
            }

            if ($post_wrapper->has("languages")) {
                $this->languages = $post_wrapper->retrieve(
                    "languages", $refinery->kindlyTo()->listOf($refinery->kindlyTo()->string())
                );
            }
        }
    }

    public function getSettings(): ilAuthLogoutPageEditorSettings
    {
        return $this->settings;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    /**
     * ilCtrl execute command
     */
    public function executeCommand(): void
    {
        switch ($this->ctrl->getNextClass($this)) {
            case 'illogoutpagegui':
                $this->tabs->clearTargets();
                $this->tabs->setBackTarget(
                    $this->lng->txt('back'),
                    $this->ctrl->getLinkTarget($this, 'show'),
                    '_top'
                );

                if ($this->redirect_source !== 'ilinternallinkgui') {
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
    protected function forwardToPageObject(): void
    {
        $keys = $this->http->wrapper()->query()->retrieve(
            'logoutpage_languages_key',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
                $this->refinery->always([])
            ])
        );

        $this->key = ilLanguage::lookupId(current($keys));

        $this->ctrl->saveParameter($this, 'key');

        $this->lng->loadLanguageModule('content');

        if (!ilLogoutPage::_exists('aout', $this->key)) {
            // doesn't exist -> create new one
            $new_page_object = new ilLogoutPage();
            $new_page_object->setParentId($this->key);
            $new_page_object->setId($this->key);
            $new_page_object->createFromXML();
        }

        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath(0));
        $this->tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());

        $this->ctrl->setReturnByClass('illogoutpagegui', 'edit');
        $page_gui = new ilLogoutPageGUI($this->key);

        $page_gui->setTemplateTargetVar('ADM_CONTENT');

        $page_gui->setStyleId($this->content_style_domain->getEffectiveStyleId());
        $page_gui->setTemplateOutput(false);

        $html = $this->ctrl->forwardCommand($page_gui);

        if ($html !== "") {
            $this->tpl->setContent($html);
        }
    }

    /**
     * Show current activated editor
     */
    protected function show(): void
    {
        switch ($this->getSettings()->getMode()) {
            case ilAuthLogoutPageEditorSettings::MODE_IPE:
            default:
                $this->showIliasEditor();
                break;
        }
    }

    private function handleLogoutPageActions(): void
    {
        $action = $this->http->wrapper()->query()->retrieve(
            'logoutpage_languages_action',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always('')
            ])
        );

        $keys = $this->http->wrapper()->query()->retrieve(
            'logoutpage_languages_key',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
                $this->refinery->always([])
            ])
        );

        switch ($action) {
            case 'deactivate':
                $this->deactivate();
                break;
            case 'activate':
                $this->activate();
                break;
            case 'edit':
                $this->ctrl->setParameter($this, 'logoutpage_languages_key', current($keys));
                $this->ctrl->setParameter($this, 'key', ilLanguage::lookupId(current($keys)));
                $this->ctrl->redirectByClass('ilLogoutPageGUI', 'edit');
                break;
            default:
                $this->ctrl->redirect($this, 'show');
                break;
        }
    }

    private function getLangKeysToUpdate(): array
    {
        $keys = $this->http->wrapper()->query()->retrieve(
            'logoutpage_languages_key',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
                $this->refinery->always([])
            ])
        );

        $lang_keys = $this->lng->getInstalledLanguages();

        if (!((string) current($keys) == 'ALL_OBJECTS')) {
            $lang_keys = array_intersect($keys, $lang_keys);
        }

        return $lang_keys;
    }

    protected function activate(): void
    {
        $lang_keys = $this->getLangKeysToUpdate();
        $settings = ilAuthLogoutPageEditorSettings::getInstance();

        foreach ($lang_keys as $lang_key) {
            $settings->enableIliasEditor($lang_key, true);
        }

        $settings->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'show');
    }

    protected function deactivate(): void
    {
        $lang_keys = $this->getLangKeysToUpdate();
        $settings = ilAuthLogoutPageEditorSettings::getInstance();

        foreach ($lang_keys as $lang_key) {
            $settings->enableIliasEditor($lang_key, false);
        }

        $settings->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'show');
    }

    /**
     * Show ILIAS page editor summary.
     */
    protected function showIliasEditor(): void
    {
        $tbl = new \ILIAS\Authentication\LogoutPage\LogoutPageLanguagesOverviewTable(
            $this->ctrl,
            $this->lng,
            $this->http,
            $this->ui_factory,
            $this->ui_renderer
        );

        $this->tpl->setContent($this->ui_renderer->render($tbl->getComponent()));
    }

    protected function saveLogoutInfo(): void
    {
        if (!$this->rbacsystem->checkAccess("write", $this->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt("permission_denied"), $this->ilErr->MESSAGE);
        }

        $this->initLogoutForm();
        if ($this->form->checkInput()) {
            $this->logoutSettings = new ilSetting("logout_settings");
            foreach ($this->lng->getInstalledLanguages() as $lang_key) {
                $settingKey = "logout_message_" . $lang_key;
                if ($this->form->getInput($settingKey)) {
                    $this->logoutSettings->set($settingKey, $this->form->getInput($settingKey));
                }
            }

            if ($this->form->getInput('default_auth_mode')) {
                $this->setting->set('default_auth_mode', $this->form->getInput('default_auth_mode'));
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("logout_information_settings_saved"), true);
        }

        $this->ctrl->redirect($this, 'show');
    }

    /**
     * Init logout form
     */
    protected function initLogoutForm(): void
    {
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this, 'saveLogoutInfo'));
        $this->form->setTableWidth('80%');
        $this->form->setTitle($this->lng->txt('logout_information'));

        $this->form->addCommandButton('saveLogoutInfo', $this->lng->txt('save'));

        if (!is_object($this->logoutSettings)) {
            $this->logoutSettings = new ilSetting("logout_settings");
        }

        $logout_settings = $this->logoutSettings->getAll();
        $languages = $this->lng->getInstalledLanguages();
        $def_language = $this->lng->getDefaultLanguage();

        foreach ($this->setDefLangFirst($def_language, $languages) as $lang_key) {
            $add = "";
            if ($lang_key === $def_language) {
                $add = " (" . $this->lng->txt("default") . ")";
            }

            $textarea = new ilTextAreaInputGUI(
                $this->lng->txt("meta_l_" . $lang_key) . $add,
                'logout_message_' . $lang_key
            );

            $textarea->setRows(10);
            $msg_logout_lang = "logout_message_" . $lang_key;

            if (isset($logout_settings[$msg_logout_lang])) {
                $textarea->setValue($logout_settings[$msg_logout_lang]);
            }

            $this->form->addItem($textarea);

            unset($logout_settings["logout_message_" . $lang_key]);
        }

        foreach ($logout_settings as $key => $message) {
            $lang_key = substr($key, strrpos($key, "_") + 1, strlen($key) - strrpos($key, "_"));

            $textarea = new ilTextAreaInputGUI(
                $this->lng->txt("meta_l_" . $lang_key),
                'logout_message_' . $lang_key
            );

            $textarea->setRows(10);
            $textarea->setValue($message);

            if (!in_array($lang_key, $languages, true)) {
                $textarea->setAlert($this->lng->txt("not_installed"));
            }

            $this->form->addItem($textarea);
        }
    }

    /**
     * returns an array of all installed languages, default language at the first position
     * @param string $a_def_language Default language of the current installation
     * @param array  $a_languages    Array of all installed languages
     * @return array $languages Array of the installed languages, default language at first position or
     *                               an empty array, if $a_a_def_language is empty
     * @author Michael Jansen
     */
    private function setDefLangFirst(string $a_def_language, array $a_languages): array
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
