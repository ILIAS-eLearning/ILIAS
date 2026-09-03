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
class ilAuthPageEditorGUI implements ilCtrlSecurityInterface
{
    final public const string DEFAULT_COMMAND = 'showPageEditorLanguages';
    final public const string LANGUAGE_TABLE_ACTIONS_COMMAND = 'handlePageActions';
    final public const string CONTEXT_HTTP_PARAM = 'auth_ipe_context';

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
    private ilRbacSystem $rbac_system;
    private ilErrorHandling $ilErr;

    public function __construct(int $a_ref_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();

        $this->http = $DIC->http();
        $this->ilErr = $DIC['ilErr'];
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->rbac_system = $DIC->rbac()->system();

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

    public function getUnsafeGetCommands(): array
    {
        return [
           self::LANGUAGE_TABLE_ACTIONS_COMMAND,
        ];
    }

    public function getSafePostCommands(): array
    {
        return [];
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
                $cmd = $this->ctrl->getCmd();
                if ($cmd === null || $cmd === '' || !method_exists($this, $cmd . 'Command')) {
                    $cmd = self::DEFAULT_COMMAND;
                }
                $verified_command = $cmd . 'Command';

                $this->$verified_command();
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
            $this->tpl->setOnScreenMessage(
                $this->tpl::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('language_does_not_exist'),
                true
            );
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
        /** @var ilLoginPageGUI $page_gui */
        $page_gui = new ($ipe_gui_class)($this->requested_language_id);

        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath(0));
        $this->tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());
        $this->content_style_gui->addCss($this->tpl, $this->ref_id);

        $page_gui->setTemplateTargetVar('ADM_CONTENT');
        $page_gui->setStyleId($this->content_style_domain->getEffectiveStyleId());
        $page_gui->setTemplateOutput(false);

        if (!$this->rbac_system->checkAccess('write', $this->ref_id)) {
            $page_gui->setOutputMode(ilPageObjectGUI::PREVIEW);
            $page_gui->setEnableEditing(false);
        }

        $html = $this->ctrl->forwardCommand($page_gui);

        if ($html !== '') {
            $this->tpl->setContent($html);
        }
    }

    private function handlePageActionsCommand(): void
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
            case AuthPageLanguagesOverviewTable::ACTIVATE:
                if (!$this->rbac_system->checkAccess('write', $this->ref_id)) {
                    $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->WARNING);
                    break;
                }
                $this->$action();
                break;

            case AuthPageLanguagesOverviewTable::EDIT:
            case AuthPageLanguagesOverviewTable::PREVIEW:
                $language_id = ilLanguage::lookupId((string) current($keys));
                if ($language_id) {
                    $this->ctrl->setParameter($this, 'key', $language_id);
                    $this->ctrl->redirectByClass(
                        $this->getRequestedAuthPageEditorContext()->pageUiClass(),
                        $action
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

        $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('settings_saved'), true);
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

        $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, self::DEFAULT_COMMAND);
    }

    private function showPageEditorLanguagesCommand(): void
    {
        $this->tabs->activateSubTab($this->getRequestedAuthPageEditorContext()->tabIdentifier());
        $tbl = new AuthPageLanguagesOverviewTable(
            $this->ctrl,
            $this->lng,
            $this->http,
            $this->ui_factory,
            $this->ui_renderer,
            $this->getRequestedAuthPageEditorContext(),
            $this->rbac_system->checkAccess('write', $this->ref_id)
        );

        $this->tpl->setContent($this->ui_renderer->render($tbl->getComponent()));
    }
}
