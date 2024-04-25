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
 * Login page editor settings GUI
 * ILIAS page editor or richtext editor
 *
 * @author            Stefan Meyer <meyer@leifos.com>
 * @ingroup           ServicesAuthentication
 * @ilCtrl_isCalledBy ilAuthLoginPageEditorGUI: ilObjAuthSettingsGUI
 * @ilCtrl_Calls      ilAuthLoginPageEditorGUI: ilLoginPageGUI
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
    private \ILIAS\HTTP\Services $http;
    private \ILIAS\Refinery\Factory $refinery;
    private \ILIAS\UI\Factory $ui_factory;
    protected \ILIAS\UI\Renderer $ui_renderer;

    private int $ref_id;
    private ilAuthLoginPageEditorSettings $settings;
    private ?ilSetting $loginSettings = null;
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

        $this->settings = ilAuthLoginPageEditorSettings::getInstance();
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
                    'visible_languages', $refinery->kindlyTo()->listOf($refinery->kindlyTo()->string())
                );
            }

            if ($post_wrapper->has("languages")) {
                $this->languages = $post_wrapper->retrieve(
                    'languages', $refinery->kindlyTo()->listOf($refinery->kindlyTo()->string())
                );
            }
        }
    }

    public function getSettings(): ilAuthLoginPageEditorSettings
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
            case 'illoginpagegui':
                $this->tabs->clearTargets();
                $this->tabs->setBackTarget(
                    $this->lng->txt('back'),
                    $this->ctrl->getLinkTarget($this, 'show'),
                    '_top'
                );

                if (strtolower((string) $this->redirect_source) !== strtolower(ilInternalLinkGUI::class)) {
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
            'loginpage_languages_key',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
                $this->refinery->always([])
            ])
        );

        $this->key = ilLanguage::lookupId((string) current($keys));

        if ($this->key === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("language_does_not_exist"), true);
            $this->show();
            return;
        }

        $this->ctrl->saveParameter($this, 'key');

        $this->lng->loadLanguageModule('content');

        if (!ilLoginPage::_exists('auth', $this->key)) {
            // doesn't exist -> create new one
            $new_page_object = new ilLoginPage();
            $new_page_object->setParentId($this->key);
            $new_page_object->setId($this->key);
            $new_page_object->createFromXML();
        }

        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath(0));
        $this->tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());

        $this->ctrl->setReturnByClass(ilLoginPageGUI::class, 'edit');
        $page_gui = new ilLoginPageGUI($this->key);

        $page_gui->setTemplateTargetVar('ADM_CONTENT');
        //TODO check what should go here $link_xml is undefined
        //$page_gui->setLinkXML($link_xml);
        //$page_gui->enableChangeComments($this->content_object->isActiveHistoryUserComments());
        //$page_gui->setFileDownloadLink($this->ctrl->getLinkTarget($this, "downloadFile"));
        //$page_gui->setFullscreenLink($this->ctrl->getLinkTarget($this, "showMediaFullscreen"));
        //$page_gui->setLinkParams($this->ctrl->getUrlParameterString()); // todo
        //		$page_gui->setSourcecodeDownloadScript($this->ctrl->getLinkTarget($this, ""));
        $page_gui->setStyleId($this->content_style_domain->getEffectiveStyleId());
        $page_gui->setTemplateOutput(false);
        //$page_gui->setLocator($contObjLocator);

        // style tab
        //$page_gui->setTabHook($this, "addPageTabs");

        if ($this->ctrl->getCmd() === 'editPage') {
            // @todo: removed deprecated ilCtrl methods, this needs inspection by a maintainer.
            // $this->ctrl->setCmd('edit');
        }

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
            case ilAuthLoginPageEditorSettings::MODE_IPE:
            default:
                $this->showIliasEditor();
                break;
        }
    }

    private function handleLoginPageActions(): void
    {
        $action = $this->http->wrapper()->query()->retrieve(
            'loginpage_languages_action',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always('')
            ])
        );

        $keys = $this->http->wrapper()->query()->retrieve(
            'loginpage_languages_key',
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
                $this->ctrl->setParameter($this, 'loginpage_languages_key', current($keys));
                $this->ctrl->redirectByClass(ilLoginPageGUI::class, 'edit');
                break;

            default:
                $this->ctrl->redirect($this, 'show');
                break;
        }
    }

    private function getLangKeysToUpdate(): array
    {
        $keys = $this->http->wrapper()->query()->retrieve(
            'loginpage_languages_key',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
                $this->refinery->always([])
            ])
        );

        $lang_keys = $this->lng->getInstalledLanguages();

        if (current($keys) !== 'ALL_OBJECTS') {
            $lang_keys = array_intersect($keys, $lang_keys);
        }

        return $lang_keys;
    }

    /**
     * Activate languages
     */
    protected function activate(): void
    {
        $lang_keys = $this->getLangKeysToUpdate();
        $settings = ilAuthLoginPageEditorSettings::getInstance();

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
        $settings = ilAuthLoginPageEditorSettings::getInstance();

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
        $tbl = new \ILIAS\Authentication\LoginPage\LoginPageLanguagesOverviewTable(
            $this->ctrl,
            $this->lng,
            $this->http,
            $this->ui_factory,
            $this->ui_renderer
        );

        $this->tpl->setContent($this->ui_renderer->render($tbl->getComponent()));
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
