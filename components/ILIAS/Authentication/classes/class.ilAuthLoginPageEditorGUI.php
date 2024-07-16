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
 * @ilCtrl_isCalledBy ilAuthLoginPageEditorGUI: ilObjAuthSettingsGUI
 * @ilCtrl_Calls      ilAuthLoginPageEditorGUI: ilLoginPageGUI
 */
class ilAuthLoginPageEditorGUI
{
    private ilCtrl $ctrl;
    private ilLanguage $lng;
    private ilGlobalTemplateInterface $tpl;
    private ilTabsGUI $tabs;
    private \ILIAS\HTTP\Services $http;
    private \ILIAS\Refinery\Factory $refinery;
    private \ILIAS\UI\Factory $ui_factory;
    private \ILIAS\UI\Renderer $ui_renderer;

    private \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;

    //variables from requests
    private ?string $redirect_source = null;
    private ?int $key = null;

    public function __construct(int $a_ref_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->lng = $DIC['lng'];

        $this->lng->loadLanguageModule('auth');

        $this->content_style_domain = $DIC->contentStyle()
                                          ->domain()
                                          ->styleForRefId($a_ref_id);

        $query_wrapper = $DIC->http()->wrapper()->query();
        $post_wrapper = $DIC->http()->wrapper()->post();
        $refinery = $DIC->refinery();

        if ($query_wrapper->has('redirectSource')) {
            $this->redirect_source = $query_wrapper->retrieve('redirectSource', $refinery->kindlyTo()->string());
        }

        if ($post_wrapper->has('key')) {
            $this->key = $post_wrapper->retrieve('key', $refinery->kindlyTo()->int());
        } elseif ($query_wrapper->has('key')) {
            $this->key = $query_wrapper->retrieve('key', $refinery->kindlyTo()->int());
        }
    }

    public function executeCommand(): void
    {
        switch (strtolower($this->ctrl->getNextClass($this) ?? '')) {
            case strtolower(ilLoginPageGUI::class):
                $this->tabs->clearTargets();
                $this->tabs->setBackTarget(
                    $this->lng->txt('back'),
                    $this->ctrl->getLinkTarget($this, 'show')
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

    private function forwardToPageObject(): void
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
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('language_does_not_exist'), true);
            $this->show();
            return;
        }

        $this->ctrl->saveParameter($this, 'key');

        $this->lng->loadLanguageModule('content');

        if (!ilLoginPage::_exists('auth', $this->key)) {
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
        $page_gui->setStyleId($this->content_style_domain->getEffectiveStyleId());
        $page_gui->setTemplateOutput(false);

        $html = $this->ctrl->forwardCommand($page_gui);

        if ($html !== '') {
            $this->tpl->setContent($html);
        }
    }

    private function show(): void
    {
        $this->showIliasEditor();
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

                // no break
            default:
                $this->ctrl->redirect($this, 'show');
        }
    }

    /**
     * @return list<string>
     */
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

    private function activate(): void
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

    private function deactivate(): void
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

    private function showIliasEditor(): void
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
}
