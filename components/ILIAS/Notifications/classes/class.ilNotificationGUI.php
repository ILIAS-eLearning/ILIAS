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

use ILIAS\DI\UIServices;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\GlobalScreen\Services as GlobalScreenServices;
use ILIAS\HTTP\Services as HTTPServices;

readonly class ilNotificationGUI implements ilCtrlBaseClassInterface
{
    private ilCtrlInterface $ctrl;
    private HTTPServices $http;
    private GlobalScreenServices $gs;
    private UIServices $ui;

    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->gs = $DIC->globalScreen();
        $this->ui = $DIC->ui();
    }

    public function executeCommand(): void
    {
        if (strtolower($this->ctrl->getNextClass()) === strtolower(ilPersonalNotificationsSettingsGUI::class)) {
            $this->ctrl->forwardCommand(new ilPersonalNotificationsSettingsGUI());
            return;
        }

        if ($this->ctrl->getCmd() === 'getOSDNotifications') {
            $this->getOSDNotifications();
        }
    }

    public function getOSDNotifications(): void
    {
        ilSession::enableWebAccessWithoutSession(true);

        $toasts = [];
        foreach ($this->gs->collector()->toasts()->getToasts() as $toast) {
            $toasts[] = $toast->getRenderer()->getToastComponentForItem($toast);
        }

        $this->http->saveResponse($this->http->response()->withBody(Streams::ofString($this->ui->renderer()->renderAsync($toasts))));
        $this->http->sendResponse();
        $this->http->close();
    }
}
