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

use ILIAS\Style\Content\GUIService;
use ILIAS\components\Authentication\Pages\AuthPageEditorContext;
use ILIAS\components\Authentication\Pages\AuthPageLanguagesOverviewTable;

/**
 * @ilCtrl_isCalledBy ilAuthPageEditorGUI: ilObjAuthSettingsGUI
 * @ilCtrl_Calls      ilAuthPageEditorGUI: ilLoginPageGUI, ilLogoutPageGUI
 */
class ilAuthPageEditorGUI
{
    final public const DEFAULT_COMMAND = 'showPageEditorLanguages';
    final public const LANGUAGE_TABLE_ACTIONS_COMMAND = 'handlePageActions';
    final public const CONTEXT_HTTP_PARAM = 'auth_ipe_context';

    private ilCtrlInterface $ctrl;
    private ilLanguage $lng;
    private ilGlobalTemplateInterface $tpl;
    private ilTabsGUI $tabs;
    private \ILIAS\HTTP\Services $http;
    private \ILIAS\Refinery\Factory $refinery;
    private \ILIAS\UI\Factory $ui_factory;
    private \ILIAS\UI\Renderer $ui_renderer;
    private \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;
    private ?string $redirect_source = null;
    private ?int $requested_language_id = null;
    private GUIService $content_style_gui;
    private int $ref_id;
    private ?string $request_ipe_context;

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

        $this->ref_id = $a_ref_id;

        $content_style = $DIC->contentStyle();
        $this->content_style_domain = $content_style
            ->domain()
            ->styleForRefId($a_ref_id);
        $this->content_style_gui = $content_style->gui();

        $query_wrapper = $DIC->http()->wrapper()->query();
        $post_wrapper = $DIC->http()->wrapper()->post();
        $refinery = $DIC->refinery();

        if ($query_wrapper->has('redirectSource')) {
            $this->redirect_source = $query_wrapper->retrieve('redirectSource', $refinery->kindlyTo()->string());
        }

        if ($post_wrapper->has('key')) {
            $this->requested_language_id = $post_wrapper->retrieve('key', $refinery->kindlyTo()->int());
        } elseif ($query_wrapper->has('key')) {
            $this->requested_language_id = $query_wrapper->retrieve('key', $refinery->kindlyTo()->int());
        }

        $this->request_ipe_context = $query_wrapper->retrieve(
            self::CONTEXT_HTTP_PARAM,
            $refinery->byTrying([
                $refinery->kindlyTo()->string(),
                $refinery->always(null)
            ])
        );
        $this->ctrl->setParameter($this, self::CONTEXT_HTTP_PARAM, $this->request_ipe_context);
    }

    public function executeCommand(): void
    {
        switch (strtolower($this->ctrl->getNextClass($this) ?? '')) {
            case strtolower(ilLoginPageGUI::class):
            case strtolower(ilLogoutPageGUI::class):
                $this->tabs->clearTargets();
                $this->tabs->setBackTarget(
                    $this->lng->txt('back'),
                    $this->ctrl->getLinkTarget($this, self::DEFAULT_COMMAND)
                );

                if (strtolower($this->redirect_source ?? '') !== strtolower(ilInternalLinkGUI::class)) {
                    $this->forwardToPageObject();
                }
                break;

            default:
                if (!$cmd = $this->ctrl->getCmd()) {
                    $cmd = 'showPageEditorLanguages';
                }
                $this->$cmd();
                break;
        }
    }

    private function getRequestedAuthPageEditorContext(): AuthPageEditorContext
    {
        return AuthPageEditorContext::from($this->request_ipe_context);
    }

    private function forwardToPageObject(): void
    {
        if (!$this->requested_language_id) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('language_does_not_exist'), true);
            $this->ctrl->returnToParent($this);
        }

        $this->lng->loadLanguageModule('content');

        $this->tabs->activateSubTab($this->getRequestedAuthPageEditorContext()->tabIdentifier());

        $ipe_gui_class = $this->getRequestedAuthPageEditorContext()->pageUiClass();
        $ipe_class = $this->getRequestedAuthPageEditorContext()->pageClass();
        $ipe_page_type = $this->getRequestedAuthPageEditorContext()->pageType();

        $this->ctrl->setParameter($this, 'key', $this->requested_language_id);

        if (!$ipe_class::_exists($ipe_page_type, $this->requested_language_id)) {
            $new_page_object = new $ipe_class();
            $new_page_object->setParentId($this->requested_language_id);
            $new_page_object->setId($this->requested_language_id);
            $new_page_object->createFromXML();
        }

        $this->ctrl->setReturnByClass($ipe_gui_class, 'edit');
        $page_gui = new ($ipe_gui_class)($this->requested_language_id);

        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath(0));
        $this->tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());
        $this->content_style_gui->addCss($this->tpl, $this->ref_id);

        $page_gui->setTemplateTargetVar('ADM_CONTENT');
        $page_gui->setStyleId($this->content_style_domain->getEffectiveStyleId());
        $page_gui->setTemplateOutput(false);

        $html = $this->ctrl->forwardCommand($page_gui);

        if ($html !== '') {
            $this->tpl->setContent($html);
        }
    }

    private function handlePageActions(): void
    {
        $action = $this->http->wrapper()->query()->retrieve(
            'authpage_languages_action',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always('')
            ])
        );

        $keys = $this->http->wrapper()->query()->retrieve(
            'authpage_languages_key',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
                $this->refinery->always([])
            ])
        );

        switch ($action) {
            case AuthPageLanguagesOverviewTable::DEACTIVATE:
                $this->deactivate();
                break;

            case AuthPageLanguagesOverviewTable::ACTIVATE:
                $this->activate();
                break;

            case AuthPageLanguagesOverviewTable::EDIT:
                $language_id = ilLanguage::lookupId((string) current($keys));
                if ($language_id) {
                    $this->ctrl->setParameter($this, 'key', $language_id);
                    $this->ctrl->redirectByClass(
                        $this->getRequestedAuthPageEditorContext()->pageUiClass(),
                        'edit'
                    );
                }
        }

        $this->ctrl->redirect($this, self::DEFAULT_COMMAND);
    }

    /**
     * @return list<string>
     */
    private function getLangKeysToUpdate(): array
    {
        $keys = $this->http->wrapper()->query()->retrieve(
            'authpage_languages_key',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
                $this->refinery->always([])
            ])
        );

        $lang_keys = $this->lng->getInstalledLanguages();

        if ((string) current($keys) !== 'ALL_OBJECTS') {
            $lang_keys = array_intersect($keys, $lang_keys);
        }

        return $lang_keys;
    }

    private function activate(): void
    {
        $lang_keys = $this->getLangKeysToUpdate();
        $settings = ilAuthPageEditorSettings::getInstance(
            $this->getRequestedAuthPageEditorContext()
        );

        foreach ($lang_keys as $lang_key) {
            $settings->enableIliasEditor($lang_key, true);
        }

        $settings->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, self::DEFAULT_COMMAND);
    }

    private function deactivate(): void
    {
        $lang_keys = $this->getLangKeysToUpdate();
        $settings = ilAuthPageEditorSettings::getInstance(
            $this->getRequestedAuthPageEditorContext()
        );

        foreach ($lang_keys as $lang_key) {
            $settings->enableIliasEditor($lang_key, false);
        }

        $settings->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, self::DEFAULT_COMMAND);
    }

    private function showPageEditorLanguages(): void
    {
        $this->tabs->activateSubTab($this->getRequestedAuthPageEditorContext()->tabIdentifier());
        $tbl = new AuthPageLanguagesOverviewTable(
            $this->ctrl,
            $this->lng,
            $this->http,
            $this->ui_factory,
            $this->ui_renderer,
            $this->getRequestedAuthPageEditorContext()
        );

        $this->tpl->setContent($this->ui_renderer->render($tbl->getComponent()));
    }
}
