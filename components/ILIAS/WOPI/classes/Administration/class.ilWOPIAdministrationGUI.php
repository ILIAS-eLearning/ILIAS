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

/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 *
 * @ilCtrl_isCalledBy ilWOPIAdministrationGUI: ilObjExternalToolsSettingsGUI
 */
class ilWOPIAdministrationGUI
{
    public const CMD_DEFAULT = "index";
    public const CMD_STORE = "store";
    private ilCtrlInterface $ctrl;
    private ilAccessHandler $access;
    private \ILIAS\HTTP\Services $http;
    private ilLanguage $lng;
    private ilGlobalTemplateInterface $maint_tpl;
    private ilSetting $settings;
    private Crawler $crawler;
    private ?int $ref_id = null;

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
            self::CMD_STORE => $this->store(),
            default => throw new ilException("command not found: " . $cmd),
        };
    }

    private function index(): void
    {
        $form = new ilWOPISettingsForm($this->settings);

        $this->maint_tpl->setContent($form->getHTML());
    }

    private function store(): void
    {
        $form = new ilWOPISettingsForm($this->settings);

        if ($form->proceed($this->http->request())) {
            global $DIC;

            $this->maint_tpl->setOnScreenMessage('success', $this->lng->txt("msg_wopi_settings_modified"), true);

            // Crawl
            $action_repo = new ActionDBRepository($DIC->database());
            $app_repo = new AppDBRepository($DIC->database());

            $discovery_url = $this->settings->get("wopi_discovery_url");
            if ($discovery_url === null) {
                $app_repo->clear($action_repo);
            } else {
                $apps = $this->crawler->crawl(new URI($discovery_url));
                if ($apps !== null) {
                    $app_repo->clear($action_repo);
                    $app_repo->storeCollection($apps, $action_repo);
                }
            }


            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }

        $this->maint_tpl->setContent($form->getHTML());
    }
}
