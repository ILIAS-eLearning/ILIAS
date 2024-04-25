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

use ILIAS\Services\WOPI\Discovery\Crawler;
use ILIAS\Data\URI;
use ILIAS\Services\WOPI\Discovery\AppDBRepository;
use ILIAS\Services\WOPI\Discovery\ActionDBRepository;
use ILIAS\Services\WOPI\Discovery\ActionRepository;
use ILIAS\Services\WOPI\Discovery\AppRepository;
use ILIAS\Services\WOPI\Discovery\ActionTarget;
use ILIAS\Services\WOPI\Discovery\Action;

/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 *
 * @ilCtrl_isCalledBy ilWOPIAdministrationGUI: ilObjExternalToolsSettingsGUI
 */
class ilWOPIAdministrationGUI
{
    public const CMD_DEFAULT = "index";
    public const CMD_STORE = "store";
    public const CMD_SHOW = 'show';
    private ilCtrlInterface $ctrl;
    private ilAccessHandler $access;
    private \ILIAS\HTTP\Services $http;
    private ilLanguage $lng;
    private ilGlobalTemplateInterface $maint_tpl;
    private ilSetting $settings;
    private Crawler $crawler;
    private ?int $ref_id = null;
    private ActionRepository $action_repo;
    private AppRepository $app_repo;
    private \ILIAS\UI\Factory $ui_factory;
    private \ILIAS\UI\Renderer $ui_renderer;

    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->http = $DIC->http();
        $this->settings = $DIC->settings();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("wopi");
        $this->maint_tpl = $DIC->ui()->mainTemplate();
        $this->ref_id = $this->http->wrapper()->query()->has("ref_id")
            ? (int) $this->http->wrapper()->query()->retrieve(
                "ref_id",
                $DIC->refinery()->to()->string()
            )
            : null;
        $this->crawler = new Crawler();
        $this->action_repo = new ActionDBRepository($DIC->database());
        $this->app_repo = new AppDBRepository($DIC->database());

        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
    }

    public function executeCommand(): void
    {
        if (!$this->access->checkAccess("read", "", $this->ref_id)) {
            $this->maint_tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(ilObjExternalToolsSettingsGUI::class);
        }

        $cmd = $this->ctrl->getCmd(self::CMD_DEFAULT);
        match ($cmd) {
            self::CMD_DEFAULT => $this->index(),
            self::CMD_SHOW => $this->show(),
            self::CMD_STORE => $this->store(),
            default => throw new ilException("command not found: " . $cmd),
        };
    }

    private function index(): void
    {
        $supported_suffixes = $this->getSupportedSuffixes();
        if (!empty($supported_suffixes)) {
            $this->maint_tpl->setOnScreenMessage(
                'info',
                sprintf(
                    $this->lng->txt("currently_supported"),
                    implode(", ", $supported_suffixes)
                )
            );
        }

        $form = new ilWOPISettingsForm($this->settings);

        $this->maint_tpl->setContent(
            $form->getHTML()
        );
    }

    private function getSupportedSuffixes(): array
    {
        $wopi_activated = (bool) $this->settings->get("wopi_activated", '0');
        if (!$wopi_activated) {
            return [];
        }
        return $this->action_repo->getSupportedSuffixes(ActionTarget::EDIT);
    }

    private function show(): void
    {
        $actions = array_map(
            function (Action $action) {
                return $this->ui_factory->item()->standard($action->getExtension())->withProperties([
                    $this->lng->txt('launcher_url') => (string) $action->getLauncherUrl(),
                    $this->lng->txt('action') => $action->getName()
                ]);
            },
            $this->action_repo->getActionsForTargets(ActionTarget::EDIT, ActionTarget::EMBED_EDIT)
        );

        $this->maint_tpl->setContent(
            $this->ui_renderer->render(
                $this->ui_factory->item()->group(
                    $this->lng->txt('actions'),
                    $actions
                )
            )
        );
    }

    private function store(): void
    {
        $form = new ilWOPISettingsForm($this->settings);

        if ($form->proceed($this->http->request())) {
            global $DIC;

            $this->maint_tpl->setOnScreenMessage('success', $this->lng->txt("msg_wopi_settings_modified"), true);

            // Crawl
            $discovery_url = $this->settings->get("wopi_discovery_url");
            if ($discovery_url === null) {
                $this->app_repo->clear($this->action_repo);
            } else {
                $apps = $this->crawler->crawl(new URI($discovery_url));
                if ($apps !== null) {
                    $this->app_repo->storeCollection($apps, $this->action_repo);
                }
            }

            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }

        $this->maint_tpl->setContent($form->getHTML());
    }
}
